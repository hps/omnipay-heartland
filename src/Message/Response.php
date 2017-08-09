<?php

namespace Omnipay\Heartland\Message;

use DOMDocument;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

/**
 * Heartland Response
 */
class Response extends AbstractResponse
{

    /**
     * 
     *
     * @var \stdClass  
     */
    private $response = null;
    private $statusOK = false;
    private $heartlandResponseMessage = "";
    private $heartlandResponseReasonCode = "";
    private $heartlandTransactionId = "";
    private $heartlandTransactionType = "";
    private $responseData = null;
    public $reversalRequired = false;

    public function __construct($request, $response, $txnType) 
    {
        $this->request = $request;
        $this->response = $response;
        $this->heartlandTransactionType = $txnType;
        $this->goThroughResponse();
    }

    public function isSuccessful() 
    {
        return $this->statusOK;
    }

    public function getMessage() 
    {
        return (string) $this->heartlandResponseMessage;
    }

    public function getReasonCode() 
    {
        return (string) $this->heartlandResponseReasonCode;
    }
    
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
   

    private function goThroughResponse() 
    {         
        switch ($this->response->status) {
            case '200':
                $responseObject = $this->_XML2Array($this->response->response);
                $ver = "Ver1.0";
                $this->responseData = $responseObject->$ver;
                $this->_processChargeGatewayResponse();
                $this->_processChargeIssuerResponse();
                break;
            case '500':
                $faultString = $this->_XMLFault2String($this->response->response);
                $this->statusOK = false;
                $this->heartlandResponseMessage = $faultString;
                $this->heartlandResponseReasonCode = $this->response->Header->GatewayRspCode;
                break;
            default:
                $this->heartlandResponseMessage = 'Unexpected response';                
                break;
        }        
    }
    
    public function getData() {        
        //convert the xml object as an array
        $serverResponseArray = $this->xmlObj2array($this->responseData);        
        return $this->mergeResponse($serverResponseArray);
    }
    
    public function getCode()
    {
        return (string) $this->response->status;
    }

    private function _processChargeGatewayResponse() 
    {
        $this->heartlandTransactionId = (isset($this->responseData->Header->GatewayTxnId) ? $this->responseData->Header->GatewayTxnId : null);
        $this->heartlandResponseReasonCode = (isset($this->responseData->Header->GatewayRspCode) ? $this->responseData->Header->GatewayRspCode : null);
        $this->heartlandResponseMessage = (isset($this->responseData->Header->GatewayRspMsg) ? $this->responseData->Header->GatewayRspMsg : null);
        
        $this->statusOK = ($this->heartlandResponseReasonCode == 0) ? true : false;
        
        if ($this->heartlandResponseReasonCode == '0') {
            return;
        }

        if ($this->heartlandResponseReasonCode == '30') {
            $this->reversalRequired = true;
            try {
                $reverseRequest = new ReverseRequest($this->request->httpClient, $this->request->httpRequest);
                $reverseRequest->initialize($this->request->getParameters());
                $reverseResponse = $reverseRequest->send();
            } catch (Exception $e) {
                $this->heartlandResponseMessage = 'Error occurred while reversing a charge due to HPS gateway timeout: ' . HpsExceptionCodes::GATEWAY_TIMEOUT_REVERSAL_ERROR;
                return;
            }
        }
        $this->heartlandResponseMessage = HpsGatewayResponseValidation::checkResponse($this->responseData, $this->heartlandTransactionType);
    }

    /**
     * @param $expectedType
     *
     * @throws \HpsCreditException
     * @throws null
     */
    private function _processChargeIssuerResponse() 
    {
        $expectedType = $this->heartlandTransactionType;
        $transactionId = (isset($this->responseData->Header->GatewayTxnId) ? $this->responseData->Header->GatewayTxnId : null);
        $item = $this->responseData->Transaction->$expectedType;

        if ($item != null) {
            $responseCode = (isset($item->RspCode) ? $item->RspCode : null);
            $responseText = (isset($item->RspText) ? $item->RspText : null);

            if ($responseCode != null) {
                // check if we need to do a reversal
                if ($responseCode == '91') {
                    $this->reversalRequired = true;                    
                }
                $this->heartlandResponseMessage = HpsIssuerResponseValidation::checkResponse($transactionId, $responseCode, $responseText);
            }
        }
    }

    /**
     * @param $xml
     *
     * @return mixed
     */
    private function _XML2Array($xml) 
    {
        $envelope = simplexml_load_string($xml, "SimpleXMLElement", 0, 'http://schemas.xmlsoap.org/soap/envelope/');
        foreach ($envelope->Body as $response) {
            foreach ($response->children('http://Hps.Exchange.PosGateway') as $item) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param $xml
     *
     * @return string
     */
    private function _XMLFault2String($xml) 
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
    private function xmlObj2array($xmlObject, $out = array()) {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = ( is_object($node) ) ? $this->xmlObj2array($node) : $node;
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
    private function mergeResponse($array) {
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
