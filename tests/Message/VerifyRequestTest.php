<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class VerifyRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new VerifyRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(array(
            'card' => $this->getValidCard(),
        ));
        $this->request->setSecretApiKey('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('VerifySuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1024868463', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
    }

    public function testSendErrorInvalidCard()
    {
        $this->setMockHttpResponse('VerifyFailureInvalidCard.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('The card number is not valid', $response->getMessage());
    }
}
