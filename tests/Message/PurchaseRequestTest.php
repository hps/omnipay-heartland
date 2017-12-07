<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '10.00',
                'currency' => 'USD',
                'cardReference' => 'supt_5h1EiJ324I5U6hIvCEsc0rGI'
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ');

    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023524629', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023529555', $response->getTransactionReference());
        $this->assertSame('Invalid card data', $response->getMessage());
    }

    public function testSendSuccessWithCard()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');
        $this->request->setCardReference(null);
        $this->request->setCard($this->getValidCard());
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023524629', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
    }

    public function testGatewayEmptyResponse()
    {
        /*
        $this->setMockHttpResponse('PurchaseEmptyResponse.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEmpty($response->getData());

        */
    }

    public function testErrorNoOpenBatch()
    {
        $this->setMockHttpResponse('PurchaseNoOpenBatch.txt');
        $response = $this->request->send();

        $this->assertSame($response->getReasonCode(), '8');
        $this->assertSame('Transaction rejected because the referenced original transaction is invalid', $response->getMessage());
    }

    public function testErrorInvalidCPCData()
    {
        $this->setMockHttpResponse('PurchaseInvalidCPCData.txt');
        $response = $this->request->send();

        $this->assertSame($response->getReasonCode(), '9');
        $this->assertSame('Invalid CPC data', $response->getMessage());
    }

    public function testErrorInvalidNumber()
    {
        $this->setMockHttpResponse('PurchaseInvalidNumber.txt');
        $response = $this->request->send();

        $this->assertSame($response->getReasonCode(), '11');
        $this->assertSame('The card number is not valid', $response->getMessage());
    }


    public function testGatewayErrorReversal()
    {
        $this->setMockHttpResponse('PurchaseGatewayError.txt');
        $response = $this->request->send();

        $this->assertSame($response->getReasonCode(), '31');
        $this->assertSame($response->getMessage(), 'Gateway timed out');
        $this->assertNotNull($response->reversalDataObject->getData());
    }


    public function testGatewayTimeoutError()
    {
        $this->setMockHttpResponse('PurchaseGatewayTimeout.txt');
        $response = $this->request->send();

        $this->assertSame($response->getReasonCode(), '21');
        $this->assertSame($response->getMessage(), 'The card issuer timed-out.');
        $this->assertNotNull($response->reversalDataObject->getData());
    }

}
