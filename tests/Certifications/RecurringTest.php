<?php

namespace Omnipay\Heartland\Certifications;

use Omnipay\Tests\TestCase;
use Omnipay\Heartland\Gateway;

class RecurringTest extends TestCase
{
    /** @var Gateway */
    protected $gateway;

    /** @var string */
    protected $publicKey = 'pkapi_cert_jKc1FtuyAydZhZfbB3';

    protected static $customerIdentifier = 'TestCustomer100';

    protected static $customerReference;

    protected static $paymentMethodCCIdentifier = 'TC105-02-TK';

    protected static $paymentMethodCCReference;

    protected static $paymentMethodACHIdentifier = 'TC105-02-TK';

    protected static $paymentMethodACHReference;

    protected static $scheduleIdentifier = '123456ABC';

    protected static $scheduleReference;

    public function setUp()
    {
        parent::setUp();

        $secretAPIKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';

        if ($secretAPIKey) {
            $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
            $this->gateway
                ->setSecretApiKey($secretAPIKey)
                ->setDeveloperId('002914')
                ->setVersionNumber('2778');
        } else {
            // No credentials were found, so skip this test
            $this->markTestSkipped();
        }
    }

    public function testCreateCustomer()
    {
        $customerData = array(
            'customerIdentifier' => static::$customerIdentifier,
            'firstName' => 'Walt',
            'lastName' => 'Disney',
            'company' => 'Disneyland',
            'customerStatus' => 'Active',
            'title' => 'Manager',
            'department' => 'Sales',
            'primaryEmail' => 'walt.disney@disneyland.com',
            'secondaryEmail' => 'walt.disney@disneyworld.com',
            'phoneDay' => '2221113333',
            'phoneDayExt' => '12345',
            'phoneEvening' => '4445556666',
            'phoneEveningExt' => '12345',
            'phoneMobile' => '7778889999',
            'phoneMobileExt' => '12345',
            'fax' => '2141234567',
            'addressLine1' => '100 Paradise Drive',
            'addressLine2' => 'Suite 100',
            'city' => 'Funland',
            'stateProvince' => 'FL',
            'zipPostalCode' => '123456789',
            'country' => 'USA',
        );

        // createCustomer
        $request = $this->gateway->createCustomer($customerData);
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getCustomerReference());
        foreach ($customerData as $key => $value) {
            $this->assertSame($value, $response->getData()[$key]);
        }

        static::$customerReference = $response->getCustomerReference();
    }

    public function testUpdateCustomer()
    {
        $customerData = array(
            'customerReference' => static::$customerReference,
            'customerIdentifier' => static::$customerIdentifier,
            'firstName' => 'Walt',
            'lastName' => 'Disney',
            'company' => 'Disneyland',
            'customerStatus' => 'Active',
            'title' => 'Manager',
            'department' => 'Sales',
            'primaryEmail' => 'walt.disney@disneyland.com',
            'secondaryEmail' => 'walt.disney@disneyworld.com',
            'phoneDay' => '2221113333',
            'phoneDayExt' => '12345',
            'phoneEvening' => '4445556666',
            'phoneEveningExt' => '12345',
            'phoneMobile' => '7778889999',
            'phoneMobileExt' => '12345',
            'fax' => '214-123-4567',
            'addressLine1' => '100 Paradise Drive',
            'addressLine2' => 'Suite 100',
            'city' => 'Funland',
            'stateProvince' => 'FL',
            'zipPostalCode' => '12345-6789',
            'country' => 'USA',
        );

        // updateCustomer
        $request = $this->gateway->updateCustomer($customerData);
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getCustomerReference());
        $this->assertEquals(static::$customerReference, $response->getCustomerReference());
    }

    public function testFetchCustomer()
    {
        $request = $this->gateway->fetchCustomer(array(
            'customerReference' => static::$customerReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getCustomerReference());
        $this->assertEquals(static::$customerReference, $response->getCustomerReference());
    }

    public function testSearchCustomers()
    {
        $request = $this->gateway->searchCustomers(array(
            'customerIdentifier' => static::$customerIdentifier,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testCreatePaymentMethodACH()
    {
        $request = $this->gateway->createPaymentMethod(array(
            'paymentMethodType' => 'ACH',
            'customerReference' => static::$customerReference,
            'preferredPayment' => true,
            'achType' => 'Checking',
            'accountType' => 'Personal',
            'telephoneIndicator' => 'false',
            'nameOnAccount' => static::$customerIdentifier,
            'accountNumber' => '9987765505',
            'routingNumber' => '124001545',
            'addressLine1' => '100 Paradise Drive',
            'addressLine2' => 'Suite 100',
            'city' => 'Funland',
            'stateProvince' => 'FL',
            'zipPostalCode' => '123456789',
            'accountHolderYob' => '2015',
            'driversLicenseState' => 'FL',
            'driversLicenseNumber' => '98765432198765',
            'socialSecurityNumberLast4' => '8888',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getPaymentMethodReference());
        static::$paymentMethodACHReference = $response->getPaymentMethodReference();
    }

    public function testUpdatePaymentMethodACH()
    {
        $request = $this->gateway->updatePaymentMethod(array(
            'paymentMethodType' => 'ACH',
            'paymentMethodReference' => static::$paymentMethodACHReference,
            'preferredPayment' => true,
            'paymentStatus' => 'Active',
            'paymentMethodIdentifier' => static::$paymentMethodACHIdentifier,
            'nameOnAccount' => static::$customerIdentifier,
            'telephoneIndicator' => true,
            'addressLine1' => '100 Paradise Drive',
            'addressLine2' => 'Suite 100',
            'city' => 'Funland',
            'stateProvince' => 'FL',
            'zipPostalCode' => '123456789',
            'accountHolderYob' => '2015',
            'driversLicenseState' => 'FL',
            'driversLicenseNumber' => '98765432198765',
            'socialSecurityNumberLast4' => '8888',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getPaymentMethodReference());
    }

    public function testFetchPaymentMethodACH()
    {
        $request = $this->gateway->fetchPaymentMethod(array(
            'paymentMethodReference' => static::$paymentMethodACHReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getPaymentMethodReference());
        $this->assertEquals(static::$paymentMethodACHReference, $response->getPaymentMethodReference());
    }

    public function testSearchPaymentMethodsACH()
    {
        $request = $this->gateway->searchPaymentMethods(array(
            'paymentMethodIdentifier' => static::$paymentMethodACHIdentifier,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testDeletePaymentMethodACH()
    {
        $request = $this->gateway->deletePaymentMethod(array(
            'paymentMethodReference' => static::$paymentMethodACHReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testCreatePaymentMethodCC()
    {
        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => static::$customerReference,
            'preferredPayment' => true,
            'nameOnAccount' => static::$customerIdentifier,
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'cardVerificationValue' => '999',
            'addressLine1' => '100 Paradise Drive',
            'addressLine2' => 'Suite 100',
            'city' => 'Funland',
            'stateProvince' => 'FL',
            'zipPostalCode' => '123456789',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getPaymentMethodReference());
        static::$paymentMethodCCReference = $response->getPaymentMethodReference();
    }

    public function testUpdatePaymentMethodCC()
    {
        $request = $this->gateway->updatePaymentMethod(array(
            'paymentMethodReference' => static::$paymentMethodCCReference,
            'preferredPayment' => true,
            'paymentStatus' => 'Active',
            'paymentMethodIdentifier' => static::$paymentMethodCCIdentifier,
            'nameOnAccount' => static::$customerIdentifier,
            'expirationDate' => '0125',
            'addressLine1' => '100 Paradise Drive',
            'addressLine2' => 'Suite 100',
            'city' => 'Funland',
            'stateProvince' => 'FL',
            'zipPostalCode' => '123456789',
            'country' => 'USA',
            'cpcTaxType' => 'SALESTAX',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getPaymentMethodReference());
    }

    public function testFetchPaymentMethodCC()
    {
        $request = $this->gateway->fetchPaymentMethod(array(
            'paymentMethodReference' => static::$paymentMethodCCReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getPaymentMethodReference());
        $this->assertEquals(static::$paymentMethodCCReference, $response->getPaymentMethodReference());
    }

    public function testSearchPaymentMethodsCC()
    {
        $request = $this->gateway->searchPaymentMethods(array(
            'paymentMethodIdentifier' => static::$paymentMethodCCIdentifier,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testCreateSchedule()
    {
        $request = $this->gateway->createSchedule(array(
            'scheduleIdentifier' => static::$scheduleIdentifier,
            'customerReference' => static::$customerReference,
            'scheduleName' => 'ABCDEFG 1234',
            'scheduleStatus' => 'Active',
            'paymentMethodReference' => static::$paymentMethodCCReference,
            'subtotalAmount' => array(
                'value' => '1200',
                'currency' => 'USD',
            ),
            'taxAmount' => array(
                'value' => '100',
                'currency' => 'USD',
            ),
            'deviceId' => '90255035',
            'startDate' => '1225' . date('Y'),
            'processingDateInfo' => '15',
            'frequency' => 'Monthly',
            'duration' => 'Limited Number',
            'numberOfPayments' => '10',
            'reprocessingCount' => '1',
            'invoiceNbr' => 'Wildcard',
            'description' => 'Text msg on receipts',
            'emailReceipt' => 'Never',
            'emailAdvanceNotice' => 'No',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getScheduleReference());
        static::$scheduleReference = $response->getScheduleReference();
    }

    public function testUpdateSchedule()
    {
        $request = $this->gateway->updateSchedule(array(
            'scheduleReference' => static::$scheduleReference,
            'scheduleIdentifier' => static::$scheduleIdentifier,
            'scheduleName' => 'ABCDEFG 1234',
            'scheduleStatus' => 'Active',
            'subtotalAmount' => array(
                'value' => '1200',
                'currency' => 'USD',
            ),
            'taxAmount' => array(
                'value' => '100',
                'currency' => 'USD',
            ),
            'deviceId' => '90255035',
            'startDate' => '1225' . date('Y'),
            'processingDateInfo' => '15',
            'frequency' => 'Monthly',
            'duration' => 'Limited Number',
            'numberOfPayments' => '10',
            'reprocessingCount' => '1',
            'invoiceNbr' => 'Wildcard',
            'description' => 'Text msg on receipts',
            'emailReceipt' => 'Never',
            'emailAdvanceNotice' => 'No',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getScheduleReference());
        $this->assertEquals(static::$scheduleReference, $response->getScheduleReference());
    }

    public function testFetchSchedule()
    {
        $request = $this->gateway->fetchSchedule(array(
            'scheduleReference' => static::$scheduleReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getScheduleReference());
        $this->assertEquals(static::$scheduleReference, $response->getScheduleReference());
    }

    public function testSearchSchedules()
    {
        $request = $this->gateway->searchSchedules(array(
            'sceduleIdentifier' => static::$scheduleIdentifier,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testDeleteSchedule()
    {
        $request = $this->gateway->deleteSchedule(array(
            'scheduleReference' => static::$scheduleReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testDeletePaymentMethodCC()
    {
        $request = $this->gateway->deletePaymentMethod(array(
            'paymentMethodReference' => static::$paymentMethodCCReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testDeleteCustomer()
    {
        $request = $this->gateway->deleteCustomer(array(
            'customerReference' => static::$customerReference,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }
}
