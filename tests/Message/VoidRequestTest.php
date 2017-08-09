<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class VoidRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSecretApiKey('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ');               
        $this->request->setTransactionReference('1023522834')
            ->setAmount('10.00');
    }
   
    public function testSendSuccess()
    {
        $this->setMockHttpResponse('VoidSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023548675', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('VoidFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());     
        $this->assertSame('Transaction rejected because the referenced original transaction is invalid. Subject \'1023548675\'.  Original transaction not found.', $response->getMessage());       
    }
}
