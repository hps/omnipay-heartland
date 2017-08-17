<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class ReversalRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new ReverseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSecretApiKey('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ');               
        $this->request->setTransactionReference('1023522834')
            ->setAmount('10.00');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('ReversalSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023522835', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
    }
    
    public function testSendError()
    {
        $this->setMockHttpResponse('ReversalFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());     
        $this->assertSame('Transaction rejected because amount to be returned exceeds the original settlement amount or the return amount is zero.', $response->getMessage());       
    }
    
    public function testSendSuccessByToken()
    {
        $this->setMockHttpResponse('ReversalSuccess.txt');
        $this->request->setTransactionReference(null);
        $this->request->setToken('supt_rHttxY33auMw3s3fOoGnnojl');
        
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023522835', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
    }
}
