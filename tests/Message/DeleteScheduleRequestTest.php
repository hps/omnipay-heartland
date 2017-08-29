<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class DeleteScheduleRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new DeleteScheduleRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
        $this->request->setScheduleReference('1234');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('DeleteScheduleSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    public function testSendSuccessIdempotent()
    {
        $this->setMockHttpResponse('DeleteScheduleSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        $this->setMockHttpResponse('DeleteScheduleSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    public function testSendFailureUnknownSchedule()
    {
        $this->setMockHttpResponse('DeleteScheduleFailureUnknownSchedule.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('400', $response->getCode());
    }
}
