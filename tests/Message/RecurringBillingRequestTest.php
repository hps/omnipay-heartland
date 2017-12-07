<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class RecurringBillingRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new RecurringBillingRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'currency' => 'USD',
                'paymentMethodReference' => 'd0e7eba5-7cdd-47af-9992-9f732f56f5d7',
                'description' => 'Order #42'
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('RecurringBillingSuccess.txt');

        $this->request->setOneTime(true);
        $this->request->setTransactionId('123456');

        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023609819', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
        $this->assertSame('00', $response->getCode());
    }

    public function testSendFailureInvalidPaymentMethodKey()
    {
        $this->setMockHttpResponse('RecurringBillingFailureInvalidPaymentMethodKey.txt');
        $response = $this->request->send();


        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023607706', $response->getTransactionReference());
        $this->assertSame('Invalid card data', $response->getMessage());
        $this->assertSame('34', $response->getCode());
    }

    public function testSendFailureIssuerDecline()
    {
        $this->setMockHttpResponse('RecurringBillingFailureIssuerDecline.txt');
        $response = $this->request->send();


        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1030206990', $response->getTransactionReference());
        $this->assertSame('The card was declined.', $response->getMessage());
        $this->assertSame('05', $response->getCode());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage Internal Server Error
     */
    public function testSendFailureInvalidGuid()
    {
        $this->setMockHttpResponse('RecurringBillingFailureInvalidGuid.txt');
        $response = $this->request->send();
    }
}
