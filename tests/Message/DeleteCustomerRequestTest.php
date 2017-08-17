<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class DeleteCustomerRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new DeleteCustomerRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
        $this->request->setCustomerReference('1234');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('DeleteCustomerSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    public function testSendSuccessIdempotent()
    {
        $this->setMockHttpResponse('DeleteCustomerSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        $this->setMockHttpResponse('DeleteCustomerSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    public function testSendFailureUnknownCustomer()
    {
        $this->setMockHttpResponse('DeleteCustomerFailureUnknownCustomer.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
    }
}
