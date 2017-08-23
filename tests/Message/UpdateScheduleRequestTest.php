<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class UpdateScheduleRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new UpdateScheduleRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'scheduleReference' => '65697',
                'scheduleStatus' => 'Inactive',
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('UpdateScheduleSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }
    
    public function testScheduleStarted()
    {
        $this->setMockHttpResponse('UpdateScheduleSuccess.txt');
        $this->request->setScheduleStarted('true');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }
    
    public function testScheduleNotStarted()
    {
        $this->setMockHttpResponse('UpdateScheduleSuccess.txt');
        $this->request->setScheduleStarted('false');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }
}
