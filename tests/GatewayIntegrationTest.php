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
            'card' => $this->getValidCard(),
            'transactionId' => 1
        ));
        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), 'Authorization should succeed');
        $this->assertNotNull($responseData['GatewayTxnId']);

        $transactionRef = $response->getTransactionReference();

        // Capture
        $request = $this->gateway->capture(array(
            'amount' => '42.42',
            'transactionReference' => $transactionRef,
            'transactionId' => 1,
            'customerReference' => 'abc-123',
            'transactionHistoryId' => 12
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

    public function testAuthWithPaymentMethodReference()
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        $request = $this->gateway->authorize(array(
            'amount' => '42.42',
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Authorization should succeed');
        $transactionRef = $response->getTransactionReference();
    }

    public function testPurchaseWithPaymentMethodReference()
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        $request = $this->gateway->purchase(array(
            'amount' => '42.42',
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $transactionRef = $response->getTransactionReference();
    }

    public function testPurchaseCardEdit()
    {
        $request = $this->gateway->authorize(array(
            'amount' => '112.34',
            'card' => $this->getValidCard(),
            'taxAmount' => '1.00',
            'taxType' => 'SALESTAX',
        ));
        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), 'Authorization should succeed');
        $this->assertNotNull($responseData['GatewayTxnId']);
        $this->assertNotNull($response->getPurchaseCardResponse());
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
        $this->assertNotNull($response->getCustomerReference());
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
        $this->assertNotNull($response->getCustomerReference());
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
        $this->assertNotNull($response->getCustomerReference());

        // fetchCustomer
        $customerKey = $response->getCustomerReference();
        $request = $this->gateway->fetchCustomer(array(
            'customerReference' => $customerKey,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getCustomerReference());
        $this->assertSame($customerKey, $response->getCustomerReference());
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
        $customer = $response;
        $request = $this->gateway->updateCustomer(array(
            'customerReference' => $customer->getCustomerReference(),
            'customerStatus' => 'Inactive',
        ));

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
            'customerReference' => $response->getCustomerReference(),
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' =>    $customer->getCustomerReference(),
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $response = $request->send();
        $responseData = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData->getPaymentMethodReference());
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
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
        $responseData = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData->getPaymentMethodReference());
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
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
        $responseData = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData->getPaymentMethodReference());
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
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
        $responseData = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($responseData->getPaymentMethodReference());
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' =>    $customer->getCustomerReference(),
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $payment = $request->send();
        $paymentData = $payment;

        $this->assertTrue($payment->isSuccessful(), $payment->getMessage());
        $this->assertNotNull($paymentData->getPaymentMethodReference());

        //get the payment details
        $request = $this->gateway->fetchPaymentMethod(array(
            'paymentMethodReference' =>    $paymentData->getPaymentMethodReference()
        ));

        $response = $request->send();
        $responseData = $response;

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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' =>    $customer->getCustomerReference(),
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $response = $request->send();
        $paymentData = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentData->getPaymentMethodReference());

        // updatePaymentMethod
        $request = $this->gateway->updatePaymentMethod(array(
            'paymentMethodReference' => $paymentData->getPaymentMethodReference(),
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' =>    $customer->getCustomerReference(),
            'nameOnAccount'  => 'John Doe',
            'accountNumber'  => '5473500000000014',
            'expirationDate' => '1225',
            'country'        => 'USA'
        ));

        $payment = $request->send();
        $paymentData = $payment;

        $this->assertTrue($payment->isSuccessful(), $payment->getMessage());
        $this->assertNotNull($paymentData->getPaymentMethodReference());

        //delete the payment details
        $request = $this->gateway->deletePaymentMethod(array(
            'paymentMethodReference' => $paymentData->getPaymentMethodReference(),
            'forceDelete' => true,
        ));

        $this->assertTrue($request->getForceDelete());
        $response = $request->send();
        $responseData = $response;

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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerReference' => $customer->getCustomerReference(),
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
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
        $this->assertNotNull($response->getScheduleReference());
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerReference' => $customer->getCustomerReference(),
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
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
        $this->assertNotNull($response->getScheduleReference());
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerReference' => $customer->getCustomerReference(),
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
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
        $this->assertNotNull($response->getScheduleReference());

        $scheduleKey = $response->getScheduleReference();

        // fetchSchedule

        $request = $this->gateway->fetchSchedule(array(
            'scheduleReference' => $scheduleKey,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertSame($scheduleKey, $response->getScheduleReference());
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerReference' => $customer->getCustomerReference(),
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
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
        $this->assertNotNull($response->getScheduleReference());

        $scheduleKey = $response->getScheduleReference();

        // updateSchedule

        $request = $this->gateway->updateSchedule(array(
            'scheduleReference' => $scheduleKey,
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerReference' => $customer->getCustomerReference(),
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
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
        $this->assertNotNull($response->getScheduleReference());

        $scheduleKey = $response->getScheduleReference();

        // deleteSchedule

        $request = $this->gateway->deleteSchedule(array(
            'scheduleReference' => $scheduleKey,
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

    public function testRefundBycard() {
        // Purchase
        $request = $this->gateway->purchase(array(
            'amount' => 10.00,
            'card' => $this->getValidCard()
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $transactionRef = $response->getTransactionReference();

        $request = $this->gateway->refund(array(
            'card' => $this->getValidCard(),
            'transactionId' => 1,
            'amount' => '10.00'
        ));

        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Refund should succeed');

    }

    public function testAuthReversal() {
        // Authorize
        $request = $this->gateway->authorize(array(
            'amount' => '42.42',
            'card' => $this->getValidCard()
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Authorization should succeed');

        // reverse
        $request = $this->gateway->reverse(array(
            'card' => $this->getValidCard(),
            'transactionId' => 1,
            'customerReference' => 'abc-123',
            'transactionHistoryId' => 12,
            'amount' => '42.42'
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), 'Reversal should succeed');
    }

    public function testReversalByToken() {
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

    public function testPurchaseWithSiteId() {
        // Purchase
        $this->gateway->setSecretApiKey(null);
        $request = $this->gateway->purchase(array(
            'amount' => 10.00,
            'card' => $this->getValidCard(),
            'deviceId' => 1520053,
            'licenseId' => 20903,
            'password' => '$Test1234',
            'siteId' => 20904,
            'siteTrace' => "trace0001",
            'username' => "777700004597",
            'developerId' => "123456",
            'versionNumber' => "1234",
            'serviceUri' => "https://cert.api2.heartlandportico.com/Hps.Exchange.PosGateway/PosGatewayService.asmx"
        ));
        $response = $request->send();
        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($response->getTransactionReference());
    }

    public function testPurchaseWithInvalidCredentials() {
        // Purchase
        $this->gateway->setSecretApiKey(null);
        $request = $this->gateway->purchase(array(
            'amount' => 10.00,
            'card' => $this->getValidCard(),
            'deviceId' => 123,
            'licenseId' => 20903,
            'password' => 'test',
            'siteId' => 20904,
            'siteTrace' => "001",
            'username' => "111",
            'developerId' => "123456",
            'versionNumber' => "1234",
            'serviceUri' => "https://cert.api2.heartlandportico.com/Hps.Exchange.PosGateway/PosGatewayService.asmx"
        ));
        $response = $request->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Authentication Error. Please double check your service configuration', $response->getMessage());
    }

    public function testUpdatePaymentMethodACH()
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
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
        $this->assertNotNull($response->getPaymentMethodReference());
        $this->assertSame($responseData['paymentStatus'], 'Active');

        // updatePaymentMethod
        $request = $this->gateway->updatePaymentMethod(array(
            'paymentMethodReference' => $response->getPaymentMethodReference(),
            'paymentStatus' => 'Inactive',
            'paymentMethodType' => 'ACH'
        ));

        $response = $request->send();
        $responseData = $response->getData();

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertSame($responseData['paymentStatus'], 'Inactive');
    }

    public function testUpdateScheduleWhenStarted()
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerReference' => $customer->getCustomerReference(),
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
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
        $this->assertNotNull($response->getScheduleReference());

        $scheduleKey = $response->getScheduleReference();

        // updateSchedule

        $request = $this->gateway->updateSchedule(array(
            'scheduleReference' => $scheduleKey,
            'scheduleStatus' => 'Inactive',
            'scheduleStarted' => 'true'
        ));
        $response = $request->send();
        $responseData = $response->getData();
        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertSame($responseData['scheduleStatus'], 'Inactive');
    }

    public function testUpdateScheduleWhenNotStarted()
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
        $customer = $response;

        $request = $this->gateway->createPaymentMethod(array(
            'customerReference' => $customer->getCustomerReference(),
            'nameOnAccount' => 'John Doe',
            'accountNumber' => '5473500000000014',
            'expirationDate' => '1225',
            'country' => 'USA'
        ));

        $response = $request->send();
        $paymentMethod = $response;

        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertNotNull($paymentMethod->getPaymentMethodReference());

        //createSchedule
        $request = $this->gateway->createSchedule(array(
            'customerReference' => $customer->getCustomerReference(),
            'paymentMethodReference' => $paymentMethod->getPaymentMethodReference(),
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
        $this->assertNotNull($response->getScheduleReference());

        $scheduleKey = $response->getScheduleReference();

        // updateSchedule

        $request = $this->gateway->updateSchedule(array(
            'scheduleReference' => $scheduleKey,
            'scheduleStatus' => 'Inactive',
            'scheduleStarted' => 'false'
        ));
        $response = $request->send();
        $responseData = $response->getData();
        $this->assertTrue($response->isSuccessful(), $response->getMessage());
        $this->assertSame($responseData['scheduleStatus'], 'Inactive');
    }
}
