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
                'customerReference' => 65617,
                'paymentMethodReference' => 'd0e7eba5-7cdd-47af-9992-9f732f56f5d7',
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

    public function testGetValues()
    {
        $data = array(
            'scheduleIdentifier' => "123456ABC",
            'customerKey' => 71292,
            'scheduleName' => "",
            'scheduleStatus' => "Active",
            'paymentMethodKey' => "ed595835-376c-4332-8fce-29f12084646e",
            'subtotalAmount' => array(
                'currency' => "USD",
                'value' => "1200"
            ),
            'taxAmount' => array(
                'currency' => "USD",
                'value' => "200"
            ),
            'startDate' => "02232015",
            'processingDateInfo' => "",
            'frequency' => "Weekly",
            'duration' => "Ongoing",
            'numberOfPayments' => "",
            'endDate' => "",
            'reprocessingCount' => "3",
            'invoiceNbr' => "",
            'description' => "",
            'emailReceipt' => "All",
            'emailAdvanceNotice' => "Yes",
            'debtRepayInd' => False,
            'poNumber' => ''
        );
        $this->request->initialize($data);
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');

        $this->assertSame(71292, $this->request->getCustomerKey());
        $this->assertSame('', $this->request->getScheduleName());
        $this->assertSame('ed595835-376c-4332-8fce-29f12084646e', $this->request->getPaymentMethodKey());
        $this->assertSame($data['subtotalAmount'], $this->request->getSubtotalAmount());
        $this->assertSame($data['taxAmount'], $this->request->getTaxAmount());
        $this->assertSame('02232015', $this->request->getStartDate());
        $this->assertSame('', $this->request->getProcessingDateInfo());
        $this->assertSame('Weekly', $this->request->getFrequency());
        $this->assertSame('Ongoing', $this->request->getDuration());
        $this->assertSame('', $this->request->getNumberOfPayments());
        $this->assertSame('', $this->request->getEndDate());
        $this->assertSame('3', $this->request->getReprocessingCount());
        $this->assertFalse($this->request->getDebtRepayInd());
        $this->assertSame('', $this->request->getInvoiceNbr());
        $this->assertSame('', $this->request->getPoNumber());
        $this->assertSame('', $this->request->getDescription());
        $this->assertSame('All', $this->request->getEmailReceipt());
        $this->assertSame('Yes', $this->request->getEmailAdvanceNotice());
    }
}
