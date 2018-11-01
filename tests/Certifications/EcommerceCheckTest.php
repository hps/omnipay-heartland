<?php

namespace Omnipay\Heartland\Certifications;

use Omnipay\Tests\TestCase;
use Omnipay\Heartland\Gateway;

/**
 * Integration tests for Heartland ACH for eCommerce. These tests make real requests to Heartland sandbox environment.
 *
 * In order to run, these tests require your Heartland sandbox credentials without which, they just skip. Configure
 * the following environment variables
 *
 * Once configured, the tests will no longer skip.
 */
class EcommerceCheckTest extends TestCase
{
    /** @var Gateway */
    protected $gateway;

    /** @var string */
    protected $publicKey = 'pkapi_cert_9nUz56xau7nkZmrsye';

    public function setUp()
    {
        parent::setUp();

        $secretAPIKey = 'skapi_cert_MbPdAQBL1l4A2ThZoTBKXEdEG1rIi7KAa6Yskl9Nzg';

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

    public function test01CheckSalePersonalCheckingManualEntry()
    {
        // Authorize
        $request = $this->gateway->purchase(array(
            'check' => array(
                'accountNumber' => '1357902468',
                'routingNumber' => '122000030',
                'accountType' => 'checking',
                'consumer' => array(
                    'billingFirstName' => 'First',
                    'billingLastName' => 'Last',
                ),
            ),
            'secCode' => 'WEB',
            'checkType' => 'personal',
            'entryMode' => 'manual',
            'amount' => '1.23',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test02CheckSalePersonalSavingsManualEntry()
    {
        // Authorize
        $request = $this->gateway->purchase(array(
            'check' => array(
                'accountNumber' => '1357902468',
                'routingNumber' => '122000030',
                'accountType' => 'checking',
                'consumer' => array(
                    'billingFirstName' => 'First',
                    'billingLastName' => 'Last',
                ),
            ),
            'secCode' => 'WEB',
            'checkType' => 'personal',
            'entryMode' => 'manual',
            'amount' => '12.34',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test03CheckSaleBusinessCheckingManualEntry()
    {
        // Authorize
        $request = $this->gateway->purchase(array(
            'check' => array(
                'accountNumber' => '1357902468',
                'routingNumber' => '122000030',
                'accountType' => 'checking',
                'consumer' => array(
                    'billingFirstName' => 'First',
                    'billingLastName' => 'Last',
                ),
            ),
            'secCode' => 'WEB',
            'checkType' => 'personal',
            'entryMode' => 'manual',
            'amount' => '123.45',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test04FailAtProcessor()
    {
        // Authorize
        $request = $this->gateway->purchase(array(
            'check' => array(
                'accountNumber' => '1357902468',
                'routingNumber' => '122000030',
                'accountType' => 'checking',
                'consumer' => array(
                    'billingFirstName' => 'First',
                    'billingLastName' => 'Last',
                ),
            ),
            'secCode' => 'WEB',
            'checkType' => 'personal',
            'entryMode' => 'manual',
            'amount' => '1.00',
        ));
        $response = $request->send();

        $this->assertFalse($response->isSuccessful(), 'Purchase should fail');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEquals($response->getMessage(), 'Success');
    }
}
