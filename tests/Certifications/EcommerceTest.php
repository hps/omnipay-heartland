<?php

namespace Omnipay\Heartland\Certifications;

use Omnipay\Tests\TestCase;
use Omnipay\Heartland\Gateway;

/**
 * Integration tests for the  Gateway. These tests make real requests to Heartland sandbox environment.
 *
 * In order to run, these tests require your Heartland sandbox credentials without which, they just skip. Configure
 * the following environment variables
 *
 * Once configured, the tests will no longer skip.
 */
class EcommerceTest extends TestCase
{
    /** @var Gateway */
    protected $gateway;

    public function setUp()
    {
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

    public function test01CardVerifyVisa()
    {
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
}
