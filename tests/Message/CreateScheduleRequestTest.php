<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class CreateScheduleRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreateScheduleRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'customerKey' => 65617,
                'paymentMethodKey' => 'd0e7eba5-7cdd-47af-9992-9f732f56f5d7',
                'scheduleIdentifier' => '08112017-Omnipay-jkfghdaskhgiu',
                'scheduleStatus' => 'Active',
                'subtotalAmount' => array(
                  'value' => 100,
                ),
                'startDate' => '08122017',
                'frequency' => 'Monthly',
                'processingDateInfo' => 'First',
                'duration' => 'Ongoing',
                'reprocessingCount' => 1,
                'emailReceipt' => 'Approvals',
                'emailAdvanceNotice' => 'No',
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CreateScheduleSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    public function testSendFailureBadRequest()
    {
        $this->setMockHttpResponse('CreateScheduleFailureBadRequest.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }
}
