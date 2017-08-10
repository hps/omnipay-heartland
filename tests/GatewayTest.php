<?php

namespace Omnipay\Heartland;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->initialize(array(
            'secretApiKey' => 'skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ'
        ));
    }

    public function testAuthorize()
    {
        $request = $this->gateway->authorize(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Heartland\Message\AuthorizeRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testCapture()
    {
        $request = $this->gateway->capture(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Heartland\Message\CaptureRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Heartland\Message\PurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }


    public function testRefund()
    {
        $request = $this->gateway->refund(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Heartland\Message\RefundRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testVoid()
    {
        $request = $this->gateway->void();

        $this->assertInstanceOf('Omnipay\Heartland\Message\VoidRequest', $request);
    }

    public function testReversal()
    {
        $request = $this->gateway->reverse();

        $this->assertInstanceOf('Omnipay\Heartland\Message\ReverseRequest', $request);
    }

    public function testFetchTransaction()
    {
        $request = $this->gateway->fetchTransaction(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\FetchTransactionRequest', $request);
    }

    // Recurring (PayPlan)

    public function testCreateCustomer()
    {
        $request = $this->gateway->createCustomer(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\CreateCustomerRequest', $request);
    }

    public function testUpdateCustomer()
    {
        $request = $this->gateway->updateCustomer(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\UpdateCustomerRequest', $request);
    }

    public function testFetchSchedules()
    {
        $request = $this->gateway->fetchSchedules(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\FetchSchedulesRequest', $request);
    }

    public function testRecurring()
    {
        $request = $this->gateway->recurring(array(
            'amount' => '10',
            'currency' => 'usd',
            'cardReference' => '12345678',
        ));

        $this->assertInstanceOf('\Omnipay\Heartland\Message\RecurringBillingRequest', $request);
    }
}
