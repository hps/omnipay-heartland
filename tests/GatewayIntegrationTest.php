<?php

namespace Omnipay\Heartland;

use Omnipay\Tests\TestCase;

/**
 * Integration tests for the  Gateway. These tests make real requests to Heartland sandbox environment.
 *
 * In order to run, these tests require your Heartland sandbox credentials without which, they just skip. Configure
 * the following environment variables
 *
 * Once configured, the tests will no longer skip.
 */
class GatewayIntegrationTest extends TestCase {

    /** @var Gateway */
    protected $gateway;

    public function setUp() {
        parent::setUp();

        $secretAPIKey = 'skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A';

        if ($secretAPIKey) {
            $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
            $this->gateway->setSecretApiKey($secretAPIKey);
        } else {
            // No credentials were found, so skip this test
            $this->markTestSkipped();
        }
    }

    public function testAuthCaptureVoid() {
        // Authorize
        $request = $this->gateway->authorize(array(
            'amount' => '42.42',
            'card' => $this->getValidCard()
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Authorization should succeed');
        $transactionRef = $response->getTransactionReference();

        // Capture
        $request = $this->gateway->capture(array(
            'amount' => '42.42',
            'transactionReference' => $transactionRef
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Capture should succeed');

        // Void
        $request = $this->gateway->void(array(
            'transactionReference' => $transactionRef
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Void should succeed');
    }

    public function testPurchaseRefund() {
        // Purchase
        $request = $this->gateway->purchase(array(
            'amount' => 10.00,
            'card' => $this->getValidCard()
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $transactionRef = $response->getTransactionReference();

        $request = $this->gateway->refund(array(
            'transactionReference' => $transactionRef,
            'amount' => '10.00'
        ));

        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Refund should succeed');

        $request = $this->gateway->refund(array(
            'transactionReference' => $transactionRef,
            'amount' => '10.00'
        ));

        $response = $request->send();
    }

    public function testAuthReversal() {
        // Authorize
        $request = $this->gateway->authorize(array(
            'amount' => '42.42',
            'card' => $this->getValidCard()
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Authorization should succeed');
        $transactionRef = $response->getTransactionReference();

        // reverse
        $request = $this->gateway->reverse(array(
            'amount' => '42.42',
            'transactionReference' => $transactionRef
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Reversal should succeed');
    }

    public function testPurchaseWithInvalidCardReference() {
        // Purchase
        $request = $this->gateway->purchase(array(
            'amount' => 10.00,
            'cardReference' => '123456'
        ));
        $response = $request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Invalid card data', $response->getMessage());
    }

    public function testPurchaseWithInvalidToken() {
        // Purchase
        $request = $this->gateway->purchase(array(
            'amount' => 10.00,
            'token' => '123456'
        ));
        $response = $request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Invalid card data', $response->getMessage());
    }

    public function testFetchTransaction() {
        // Authorize
        $request = $this->gateway->authorize(array(
            'amount' => '42.42',
            'card' => $this->getValidCard()
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Authorize should succeed');
        $transactionRef = $response->getTransactionReference();

        //fetch the transaction
        $request = $this->gateway->fetchTransaction(array(
            'transactionReference' => $transactionRef
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Fetch transaction details failed');
    }

    public function testAuthCaptureTwice() {
        // Authorize
        $request = $this->gateway->authorize(array(
            'amount' => '42.42',
            'card' => $this->getValidCard()
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Authorization should succeed');
        $transactionRef = $response->getTransactionReference();

        // Capture
        $request = $this->gateway->capture(array(
            'amount' => '42.42',
            'transactionReference' => $transactionRef
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Capture should succeed');

        // Capture again
        $request = $this->gateway->capture(array(
            'amount' => '42.42',
            'transactionReference' => $transactionRef
        ));
        $response = $request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Transaction rejected because the referenced original transaction is invalid. Subject \''.$transactionRef.'\'.  Original transaction is already part of a batch.', $response->getMessage());
    }

    /// Recurring Payments (PayPlan)

    // Customers

    public function testCreateCustomerMinimumData()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['customerKey']);
    }

    public function testCreateCustomerAllData()
    {
        $customerData = array(
            'customerIdentifier' => $this->createTestIdentifier(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'company' => 'Acme',
            'customerStatus' => 'Active',
            'title' => 'Employee',
            'department' => 'N A',
            'primaryEmail' => 'john.doe@acme.com',
            'secondaryEmail' => 'john.doe@email.com',
            'phoneDay' => '5551112222',
            'phoneDayExt' => '123',
            'phoneEvening' => '5551112222',
            'phoneEveningExt' => '123',
            'phoneMobile' => '5551112222',
            'phoneMobileExt' => '123',
            'fax' => '5551112222',
            'addressLine1' => '123 Main St.',
            'addressLine2' => 'Suite 1A',
            'city' => 'Anytown',
            'stateProvince' => 'TX',
            'zipPostalCode' => '75024',
            'country' => 'USA',
        );

        // createCustomer
        $request = $this->gateway->createCustomer($customerData);
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['customerKey']);
        foreach ($customerData as $key => $value) {
            $this->assertSame($value, $response->getData()[$key]);
        }
    }

    public function testFetchCustomer()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['customerKey']);

        // fetchCustomer
        $customerKey = $response->getData()['customerKey'];
        $request = $this->gateway->fetchCustomer(array(
            'customerKey' => $customerKey,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['customerKey']);
        $this->assertSame($customerKey, $response->getData()['customerKey']);
    }

    public function testUpdateCustomer()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // updateCustomer
        $customer = $response->getData();
        $customer['customerStatus'] = 'Inactive';
        $request = $this->gateway->updateCustomer($customer);

        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testDeleteCustomer()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // deleteCustomer
        $request = $this->gateway->deleteCustomer(array(
            'customerKey' => $response->getData()['customerKey'],
        ));

        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    // Payment Methods

    public function testCreatePaymentMethodCCMinimumData()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // updateCustomer
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' =>    $customer['customerKey'],
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData['paymentMethodKey']);
    }

    public function testCreatePaymentMethodCCAllData()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // updateCustomer
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'addressLine1' => '123 Main St.',
            'addressLine2' => 'Suite 1A',
            'city' => 'Anytown',
            'stateProvince' => 'TX',
            'zipPostalCode' => '75024',
            'country' => 'USA',
            'cardVerificationValue' => '123',
        ));

        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData['paymentMethodKey']);
    }

    public function testCreatePaymentMethodACHMinimumData()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // updateCustomer
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'paymentMethodType' => 'ACH',
            'achType' => 'Checking',
            'accountType' => 'Personal',
            'routingNumber' => '490000018',
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '24413815',
            'addressLine1' => '123 Main St',
            'city' => 'Dallas',
            'stateProvince' => 'TX',
            'zipPostalCode' => '98765',
            'accountHolderYob' => '1989'
        ));

        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData['paymentMethodKey']);
    }

    public function testCreatePaymentMethodACHAllData()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // updateCustomer
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'paymentMethodType' => 'ACH',
            'achType' => 'Checking',
            'accountType' => 'Personal',
            'telephoneIndiciator' => 'False',
            'routingNumber' => '490000018',
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '24413815',
            'addressLine1' => '123 Main St.',
            'addressLine2' => 'Suite 1A',
            'city' => 'Anytown',
            'stateProvince' => 'TX',
            'zipPostalCode' => '75024',
            'accountHolderYob' => '1989',
            'driversLicenseState' => 'TX',
            'driversLicenseNumber' => '123456789',
            'socialSecurityNumberLast4' => '1234',
        ));

        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData['paymentMethodKey']);
    }

    public function testFetchPaymentMethod()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // createPaymentMethod
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' =>    $customer['customerKey'],
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $payment = $request->send();
        $paymentData = $payment->getData();

        $this->assertTrue($payment->isSuccessful(), $payment->getMessage());
        $this->assertNotNull($paymentData['paymentMethodKey']);

        //get the payment details
        $request = $this->gateway->fetchPaymentMethod(array(
            'paymentMethodKey' =>    $paymentData['paymentMethodKey']
        ));

        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testUpdatePaymentMethod()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // createPaymentMethod
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' =>    $customer['customerKey'],
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $response = $request->send();
        $paymentData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentData['paymentMethodKey']);

        // updatePaymentMethod
        $request = $this->gateway->updatePaymentMethod(array(
            'paymentMethodKey' => $paymentData['paymentMethodKey'],
            'paymentStatus' => 'Inactive',
        ));

        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testDeletePaymentMethod()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // updateCustomer
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' =>    $customer['customerKey'],
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $payment = $request->send();
        $paymentData = $payment->getData();

        $this->assertTrue($payment->isSuccessful(), $payment->getMessage());
        $this->assertNotNull($paymentData['paymentMethodKey']);

        //delete the payment details
        $request = $this->gateway->deletePaymentMethod(array(
            'paymentMethodKey' => $paymentData['paymentMethodKey']
        ));

        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    // Schedules

    public function testCreateScheduleMinimumData()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // createPaymentMethod
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod['paymentMethodKey']);

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerKey' => $customer['customerKey'],
            'paymentMethodKey' => $paymentMethod['paymentMethodKey'],
            'scheduleIdentifier' => $this->createTestIdentifier(),
            'scheduleStatus' => 'Active',
            'subtotalAmount' => array(
                'value' => 100,
            ),
            'startDate' => '02012027',
            'frequency' => 'Monthly',
            'processingDateInfo' => 'First',
            'duration' => 'Ongoing',
            'reprocessingCount' => 1,
            'emailReceipt' => 'Never',
            'emailAdvanceNotice' => 'No',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['scheduleKey']);
    }

    public function testCreateScheduleAllData()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // createPaymentMethod
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod['paymentMethodKey']);

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerKey' => $customer['customerKey'],
            'paymentMethodKey' => $paymentMethod['paymentMethodKey'],
            'scheduleIdentifier' => $this->createTestIdentifier(),
            'scheduleStatus' => 'Active',
            'subtotalAmount' => array(
                'currency' => 'USD',
                'value' => 100,
            ),
            'startDate' => '02012027',
            'frequency' => 'Monthly',
            'processingDateInfo' => 'First',
            'duration' => 'Ongoing',
            'reprocessingCount' => 1,
            'emailReceipt' => 'Never',
            'emailAdvanceNotice' => 'No',
            'scheduleName' => 'Test Schedule',
            'taxAmount' => array(
                'currency' => 'USD',
                'value' => 100,
            ),
            'deviceId' => '123456',
            'debtRepayInd' => false,
            'invoiceNbr' => '123456',
            'poNumber' => '123456',
            'description' => 'Test Schedule',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['scheduleKey']);
    }

    public function testFetchSchedule()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // createPaymentMethod
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod['paymentMethodKey']);

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerKey' => $customer['customerKey'],
            'paymentMethodKey' => $paymentMethod['paymentMethodKey'],
            'scheduleIdentifier' => $this->createTestIdentifier(),
            'scheduleStatus' => 'Active',
            'subtotalAmount' => array(
                'value' => 100,
            ),
            'startDate' => '02012027',
            'frequency' => 'Monthly',
            'processingDateInfo' => 'First',
            'duration' => 'Ongoing',
            'reprocessingCount' => 1,
            'emailReceipt' => 'Never',
            'emailAdvanceNotice' => 'No',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['scheduleKey']);

        $scheduleKey = $response->getData()['scheduleKey'];

        // fetchSchedule

        $request = $this->gateway->fetchSchedule(array(
            'scheduleKey' => $scheduleKey,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertSame($scheduleKey, $response->getData()['scheduleKey']);
    }

    public function testUpdateSchedule()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // createPaymentMethod
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod['paymentMethodKey']);

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerKey' => $customer['customerKey'],
            'paymentMethodKey' => $paymentMethod['paymentMethodKey'],
            'scheduleIdentifier' => $this->createTestIdentifier(),
            'scheduleStatus' => 'Active',
            'subtotalAmount' => array(
                'value' => 100,
            ),
            'startDate' => '02012027',
            'frequency' => 'Monthly',
            'processingDateInfo' => 'First',
            'duration' => 'Ongoing',
            'reprocessingCount' => 1,
            'emailReceipt' => 'Never',
            'emailAdvanceNotice' => 'No',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['scheduleKey']);

        $scheduleKey = $response->getData()['scheduleKey'];

        // updateSchedule

        $request = $this->gateway->updateSchedule(array(
            'scheduleKey' => $scheduleKey,
            'scheduleStatus' => 'Inactive',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testDeleteSchedule()
    {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'firstName' => 'John',
            'lastName' => 'Doe',
            'country' => 'USA',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());

        // createPaymentMethod
        $customer = $response->getData();

        $request = $this->gateway->createPaymentMethod(array(
            'customerKey' => $customer['customerKey'],
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod['paymentMethodKey']);

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerKey' => $customer['customerKey'],
            'paymentMethodKey' => $paymentMethod['paymentMethodKey'],
            'scheduleIdentifier' => $this->createTestIdentifier(),
            'scheduleStatus' => 'Active',
            'subtotalAmount' => array(
                'value' => 100,
            ),
            'startDate' => '02012027',
            'frequency' => 'Monthly',
            'processingDateInfo' => 'First',
            'duration' => 'Ongoing',
            'reprocessingCount' => 1,
            'emailReceipt' => 'Never',
            'emailAdvanceNotice' => 'No',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getData()['scheduleKey']);

        $scheduleKey = $response->getData()['scheduleKey'];

        // deleteSchedule

        $request = $this->gateway->deleteSchedule(array(
            'scheduleKey' => $scheduleKey,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    public function testSearchSchedules()
    {
        $request = $this->gateway->searchSchedules();
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
    }

    /// Helpers

    protected function createTestIdentifier()
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 1, 50);
    }
}
