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

        $secretAPIKey = 'skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ';

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
    
    public function testcreateCustomer() {
        // createCustomer
        $request = $this->gateway->createCustomer(array(
            'card' => $this->getValidCard()
        ));
        $request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
        
        $response = $request->send();        
        $this->assertTrue($response->isSuccessful(), 'createCustomer should succeed');
        $transactionRef = $response->getTransactionReference();      
    }

}
