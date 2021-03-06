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

    public function testAuthorizeWithPaymentMethodReferenceIsRecurringBillingAuth()
    {
        $request = $this->gateway->purchase(array(
            'amount' => '10.00',
            'paymentMethodReference' => '123456',
        ));

        $this->assertInstanceOf('Omnipay\Heartland\Message\RecurringBillingAuthRequest', $request);
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

    public function testPurchaseWithPaymentMethodReferenceIsRecurringBilling()
    {
        $request = $this->gateway->purchase(array(
            'amount' => '10.00',
            'paymentMethodReference' => '123456',
        ));

        $this->assertInstanceOf('Omnipay\Heartland\Message\RecurringBillingRequest', $request);
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

    public function testVerify()
    {
        $request = $this->gateway->verify();

        $this->assertInstanceOf('Omnipay\Heartland\Message\VerifyRequest', $request);
    }

    public function testCreateCard()
    {
        $request = $this->gateway->createCard();

        $this->assertInstanceOf('Omnipay\Heartland\Message\VerifyRequest', $request);
        $this->assertNotNull($request->getRequestCardReference());
        $this->assertTrue($request->getRequestCardReference());
    }

    public function testPurchaseCardEdit()
    {
        $request = $this->gateway->purchaseCardEdit();

        $this->assertInstanceOf('Omnipay\Heartland\Message\PurchaseCardEditRequest', $request);
    }

    // Recurring (PayPlan)

    public function testCreateCustomer()
    {
        $request = $this->gateway->createCustomer(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\CreateCustomerRequest', $request);
    }

    public function testFetchCustomer()
    {
        $request = $this->gateway->fetchCustomer(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\FetchCustomerRequest', $request);
    }

    public function testUpdateCustomer()
    {
        $request = $this->gateway->updateCustomer(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\UpdateCustomerRequest', $request);
    }

    public function testDeleteCustomer()
    {
        $request = $this->gateway->deleteCustomer(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\DeleteCustomerRequest', $request);
    }

    public function testCreatePaymentMethod()
    {
        $request = $this->gateway->createPaymentMethod(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\CreatePaymentMethodRequest', $request);
    }

    public function testFetchPaymentMethod()
    {
        $request = $this->gateway->fetchPaymentMethod(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\FetchPaymentMethodRequest', $request);
    }

    public function testUpdatePaymentMethod()
    {
        $request = $this->gateway->updatePaymentMethod(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\UpdatePaymentMethodRequest', $request);
    }

    public function testDeletePaymentMethod()
    {
        $request = $this->gateway->deletePaymentMethod(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\DeletePaymentMethodRequest', $request);
    }

    public function testCreateSchedule()
    {
        $request = $this->gateway->createSchedule(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\CreateScheduleRequest', $request);
    }

    public function testSearchSchedules()
    {
        $request = $this->gateway->searchSchedules(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\SearchSchedulesRequest', $request);
    }

    public function testFetchSchedule()
    {
        $request = $this->gateway->fetchSchedule(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\FetchScheduleRequest', $request);
    }

    public function testUpdateSchedule()
    {
        $request = $this->gateway->updateSchedule(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\UpdateScheduleRequest', $request);
    }

    public function testDeleteSchedule()
    {
        $request = $this->gateway->deleteSchedule(array());

        $this->assertInstanceOf('Omnipay\Heartland\Message\DeleteScheduleRequest', $request);
    }

    public function testRecurringAuthorize()
    {
        $request = $this->gateway->recurringAuthorize(array(
            'amount' => '10',
            'currency' => 'usd',
            'paymentMethodReference' => '12345678',
        ));

        $this->assertInstanceOf('\Omnipay\Heartland\Message\RecurringBillingAuthRequest', $request);
    }

    public function testRecurring()
    {
        $request = $this->gateway->recurring(array(
            'amount' => '10',
            'currency' => 'usd',
            'paymentMethodReference' => '12345678',
        ));

        $this->assertInstanceOf('\Omnipay\Heartland\Message\RecurringBillingRequest', $request);
    }
}
