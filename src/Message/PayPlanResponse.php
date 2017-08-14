<?php

namespace Omnipay\Heartland\Message;

use DOMDocument;

/**
 * Heartland Portico Response
 */
class PayPlanResponse extends AbstractResponse
{
    protected $responseData = null;

    protected function parseResponse()
    {
        $resp = json_decode($this->response->response);
        switch ($this->response->status) {
            case '204':
            case '200':
                $this->responseData = $resp;
                $this->setStatusOK(true);

                if ($this->response->status == '200' && empty($resp)) {
                    $this->setStatusOk(false);
                    $this->response->status = '404';
                    $this->response->response = 'Not Found';
                }

                break;
            case '400':
                $this->heartlandResponseMessage = $resp->error->message;
                break;
            default:
                $this->heartlandResponseMessage = 'Unexpected response';
                break;
        }
    }

    public function getData()
    {
        return (array) $this->responseData;
    }
}
