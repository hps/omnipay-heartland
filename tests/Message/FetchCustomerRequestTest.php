<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class FetchCustomerRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new FetchCustomerRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
        $this->request->setCustomerKey('1234');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('FetchCustomerSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    public function testSendFailureNotFound()
    {
        $this->setMockHttpResponse('FetchCustomerFailureNotFound.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
    }
}
