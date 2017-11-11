<?php

namespace Omnipay\Heartland\Message;

use DOMDocument;

/**
 * Heartland Portico Response
 */
class PorticoResponse extends AbstractResponse
{
    protected $heartlandResponseReasonCode = 0;
    protected $heartlandTransactionId = "";
    protected $responseData = null;
    protected $purchaseCardResponse = null;
    public $reversalRequired = false;
    public $reversalDataObject = null;

    /**
     * Get the transfer reference from the response of CreateTransferRequest,
     * UpdateTransferRequest, and FetchTransferRequest.
     *
     * @return array|null
     */
    public function getTransactionReference()
    {
        return (string) $this->heartlandTransactionId;
    }

    protected function parseResponse()
    {
        switch ($this->response->status) {
            case '200':
                $responseObject = $this->XML2Array($this->response->response);
                $ver = "Ver1.0";
                $this->responseData = $responseObject->$ver;
                $this->processChargeGatewayResponse();

                if ($this->getReasonCode() == 0) {
                    $this->processChargeIssuerResponse();
                }
                break;
            case '500':
                $faultString = $this->XMLFault2String($this->response->response);
                $this->setStatusOK(false);
                $this->heartlandResponseMessage = $faultString;
                break;
            default:
                $this->heartlandResponseMessage = 'Unexpected response';
                break;
        }

        $this->request->handleResponse($this);
    }

    public function getData()
    {
        //convert the xml object as an array
        $serverResponseArray = $this->xmlObj2array($this->responseData); 
        $porticoResponse = $this->mergeResponse($serverResponseArray);
        
        //handle name value pair
        $nameValueDetails = array();
        //convert the xml object as an array 
        if(!empty($porticoResponse)){
            foreach ($porticoResponse as $index => $node) {
                if(is_numeric($index) && is_object($node) && !empty($node->Name) && !empty($node->Value)){
                    $porticoResponse[trim($node->Name)] = trim($node->Value);
                    unset($porticoResponse[$index]);
                }                 
            }
        }
        return $porticoResponse;
    }

    public function getPurchaseCardIndicator()
    {
        return isset($this->responseData->Transaction)
            && isset($this->responseData->Transaction->{$this->request->getTransactionType()})
            && isset($this->responseData->Transaction->{$this->request->getTransactionType()}->CPCInd)
            ? $this->responseData->Transaction->{$this->request->getTransactionType()}->CPCInd
            : null;
    }

    public function getPurchaseCardResponse()
    {
        return $this->purchaseCardResponse;
    }

    public function setPurchaseCardResponse($value)
    {
        $this->purchaseCardResponse = $value;
        return $this;
    }

    private function processChargeGatewayResponse()
    {
        $this->heartlandTransactionId = isset($this->responseData->Header->GatewayTxnId)
            ? $this->responseData->Header->GatewayTxnId
            : null;
        $gatewayRspCode = isset($this->responseData->Header->GatewayRspCode)
            ? $this->responseData->Header->GatewayRspCode
            : null;
        $this->heartlandResponseMessage = isset($this->responseData->Header->GatewayRspMsg)
            ? $this->responseData->Header->GatewayRspMsg
            : null;

        $this->setStatusOK($gatewayRspCode == 0);

        if ($gatewayRspCode == '0') {
            return;
        }

        if ($gatewayRspCode == '30') {
            $this->reversalRequired = true;
        }
        $gatewayException = HpsGatewayResponseValidation::checkResponse(
            $this->responseData,
            $this->heartlandTransactionType
        );
        $this->heartlandResponseMessage = $gatewayException->message;
        $this->heartlandResponseReasonCode = $gatewayException->code;
    }

    /**
     * @param $expectedType
     *
     * @throws \HpsCreditException
     * @throws null
     */
    private function processChargeIssuerResponse()
    {
        $expectedType = $this->heartlandTransactionType;
        $transactionId = isset($this->responseData->Header->GatewayTxnId)
            ? $this->responseData->Header->GatewayTxnId
            : null;
        $item = $this->responseData->Transaction->$expectedType;

        if ($item != null) {
            $responseCode = (isset($item->RspCode) ? $item->RspCode : null);
            $responseText = (isset($item->RspText) ? $item->RspText : null);

            if ($responseCode != null) {
                // check if we need to do a reversal
                if ($responseCode == '91') {
                    $this->reversalRequired = true;
                }
                //concat earlier messages
                $gatewayException = HpsIssuerResponseValidation::checkResponse(
                    $transactionId,
                    $responseCode,
                    $responseText
                );

                if ($gatewayException != null) {
                    $this->heartlandResponseMessage = $gatewayException->message;
                    $this->heartlandResponseReasonCode = $gatewayException->code;
                }
            }
        }
    }

    /**
     * @param $xml
     *
     * @return mixed
     */
    private function XML2Array($xml)
    {
        if (!empty($xml)) {
            $envelope = simplexml_load_string($xml, "SimpleXMLElement", 0, 'http://schemas.xmlsoap.org/soap/envelope/');
            foreach ($envelope->Body as $response) {
                foreach ($response->children('http://Hps.Exchange.PosGateway') as $item) {
                    return $item;
                }
            }
        }
        return null;
    }

    /**
     * @param $xml
     *
     * @return string
     */
    private function XMLFault2String($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        return $dom->getElementsByTagName('faultstring')->item(0)->nodeValue;
    }

    /**
     *
     * Convert xml object into array recursively
     *
     * @param $xmlObject
     * @param $out
     *
     * @return array
     */
    private function xmlObj2array($xmlObject, $out = array())
    {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? $this->xmlObj2array($node) : $node;
        }
        return $out;
    }

    /**
     *
     * merge array recursively
     *
     * @param $array
     *
     * @return array
     */
    private function mergeResponse($array)
    {
        $return = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, $this->mergeResponse($value));
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
