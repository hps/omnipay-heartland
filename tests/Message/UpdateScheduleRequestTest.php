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
                'ScheduleKey' => '65697',
                'ScheduleStatus' => 'Inactive',
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
}
