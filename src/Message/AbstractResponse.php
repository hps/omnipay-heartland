<?php

namespace Omnipay\Heartland\Message;

/**
 * Heartland Response
 */
abstract class AbstractResponse extends \Omnipay\Common\Message\AbstractResponse
{
    /**
    * @var \stdClass
    */
    protected $request = null;

    /**
    * @var \stdClass
    */
    protected $response = null;

    /**
      * @var bool
      */
    protected $statusOK = false;

    /**
     * @var string
     */
    protected $heartlandTransactionType = "";

    /**
     * @var string
     */
    protected $heartlandResponseMessage = "Success";

    public function __construct($request, $response, $txnType)
    {
        $this->request = $request;
        $this->response = $response;
        $this->heartlandTransactionType = $txnType;
        $this->parseResponse();
    }

    /**
      * @return bool
      */
    public function isSuccessful()
    {
        return $this->statusOK;
    }

    public function isDecline()
    {
        return !$this->isSuccessful();
    }

    protected function setStatusOK($value)
    {
        $this->statusOK = $value;
        return $this;
    }

    public function getReasonCode()
    {
        return (string) $this->heartlandResponseReasonCode;
    }

    public function getCode()
    {
        return (string) $this->response->status;
    }

    public function getMessage()
    {
        return (string) $this->heartlandResponseMessage;
    }

    abstract protected function parseResponse();
}
