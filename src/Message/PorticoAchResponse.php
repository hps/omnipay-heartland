<?php

namespace Omnipay\Heartland\Message;

/**
 * Heartland Portico ACH Response
 */
class PorticoAchResponse extends PorticoResponse
{
    protected $checkErrors = null;

    public function getCheckErrors()
    {
        return $this->checkErrors;
    }

    protected function parseResponse()
    {
        switch ($this->response->status) {
            case '200':
                $responseObject = $this->XML2Array($this->response->response);
                $ver = "Ver1.0";
                $this->responseData = $responseObject->$ver;
                $this->setStatusOK(true);
                $this->processChargeGatewayResponse();

                if ($this->getReasonCode() == 0) {
                    $item = $this->responseData->Transaction->{$this->request->getTransactionType()};
                    $this->issuerResponseCode = isset($item->RspCode) ? (string)$item->RspCode : null;
                    $this->issuerResponseMessage = isset($item->RspMessage) ? (string)$item->RspMessage : null;
                    $this->heartlandResponseMessage = $this->issuerResponseMessage;

                    if ($this->issuerResponseCode == '1') {
                        $this->setStatusOK(false);
                        $this->checkErrors = array(
                            array(
                                'type' => (string)$item->CheckRspInfo->Type,
                                'code' => (string)$item->CheckRspInfo->Code,
                                'message' => (string)$item->CheckRspInfo->Message,
                            ),
                        );
                    }
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
}
