<?php

namespace Omnipay\Heartland\Message;

use DOMDocument;

/**
 * Heartland Portico Response
 */
class PorticoResponse extends AbstractResponse
{
    protected $heartlandResponseReasonCode = 0;
    protected $transactionId = "";
    protected $responseData = null;
    protected $purchaseCardResponse = null;
    public $reversalRequired = false;
    public $reversalDataObject = null;

    protected $issuerResponseCode = null;
    protected $issuerResponseMessage = null;
    protected $gatewayResponseCode = null;
    protected $gatewayResponseMessage = null;

    /**
     * Get the transfer reference from the response of CreateTransferRequest,
     * UpdateTransferRequest, and FetchTransferRequest.
     *
     * @return array|null
     */
    public function getTransactionReference()
    {
        return (string) $this->transactionId;
    }

    public function getCode()
    {
        if ($this->issuerResponseCode) {
            return $this->issuerResponseCode;
        }

        if ($this->gatewayResponseCode) {
            return $this->gatewayResponseCode;
        }

        return (string) $this->response->status;
    }

    protected function parseResponse()
    {
        switch ($this->response->status) {
            case '200':
                $responseObject = $this->XML2Array($this->response->response);
                $ver = "Ver1.0";
                $this->responseData = $responseObject->$ver;
                $this->setStatusOk(true);
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
        return $this->mergeResponse($serverResponseArray);
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
        $this->transactionId = isset($this->responseData->Header->GatewayTxnId)
            ? (string)$this->responseData->Header->GatewayTxnId
            : null;
        $this->gatewayResponseCode = isset($this->responseData->Header->GatewayRspCode)
            ? (string)$this->responseData->Header->GatewayRspCode
            : null;
        $this->gatewayResponseMessage = isset($this->responseData->Header->GatewayRspMsg)
            ? (string)$this->responseData->Header->GatewayRspMsg
            : null;

        $this->setStatusOK($this->gatewayResponseCode == '0');

        if ($this->gatewayResponseCode == '0') {
            return;
        }

        $this->setStatusOk(false);

        if ($this->gatewayResponseCode == '30') {
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
        $item = $this->responseData->Transaction->$expectedType;

        if ($item != null) {
            $this->issuerResponseCode = (isset($item->RspCode) ? (string)$item->RspCode : null);
            $this->issuerResponseMessage = (isset($item->RspText) ? (string)$item->RspText : null);

            if ($this->issuerResponseCode != null) {
                // check if we need to do a reversal
                if ($this->issuerResponseCode == '91') {
                    $this->reversalRequired = true;
                }
                //concat earlier messages
                $gatewayException = HpsIssuerResponseValidation::checkResponse(
                    $this->transactionId,
                    $this->issuerResponseCode,
                    $this->issuerResponseMessage
                );

                if ($gatewayException != null) {
                    $this->setStatusOk(false);
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
