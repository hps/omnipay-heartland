<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class RefundRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSecretApiKey('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ');               
        $this->request->setTransactionReference('1023522834')
            ->setAmount('10.00');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('RefundSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023522835', (string) $response->getTransactionReference());
        $this->assertSame('Success', (string) $response->getMessage());
    }
    
    public function testSendError()
    {
        $this->setMockHttpResponse('RefundFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());     
        $this->assertSame('Transaction rejected because amount to be returned exceeds the original settlement amount or the return amount is zero.', (string) $response->getMessage());       
    }
}
