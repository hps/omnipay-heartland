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

    /** @var string */
    protected $publicKey = 'pkapi_cert_jKc1FtuyAydZhZfbB3';

    /** @var string */
    protected static $visaToken;

    /** @var string */
    protected static $mastercardToken;

    /** @var string */
    protected static $discoverToken;

    /** @var string */
    protected static $amexToken;

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

    public function test01CardVerifyVisaManualEntry()
    {
        // Authorize
        $request = $this->gateway->verify(array(
            'card' => $this->getVisaCard(),
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);

        static::$visaToken = $response->getData()['TokenValue'];
    }

    public function test01CardVerifyVisaSingleUseToken()
    {
        // Authorize
        $request = $this->gateway->verify(array(
            'token' => $this->getToken($this->getVisaCard()),
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test02CardVerifyMasterCardManualEntry()
    {
        $request = $this->gateway->verify(array(
            'card' => $this->getMastercardCard(),
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);

        static::$mastercardToken = $response->getData()['TokenValue'];
    }

    public function test02CardVerifyMasterCardSingleUseToken()
    {
        $request = $this->gateway->verify(array(
            'token' => $this->getToken($this->getMastercardCard()),
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test03CardVerifyDiscoverManualEntry()
    {
        $card = $this->getDiscoverCard();
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->verify(array(
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);

        static::$discoverToken = $response->getData()['TokenValue'];
    }

    public function test03CardVerifyDiscoverSingleUseToken()
    {
        $token = $this->getToken($this->getDiscoverCard());
        $card = array();
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->verify(array(
            'token' => $token,
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test04CardVerifyAmexManualEntry()
    {
        $card = $this->getAmexCard();
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->verify(array(
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);

        static::$amexToken = $response->getData()['TokenValue'];
    }

    public function test04CardVerifyAmexSingleUseToken()
    {
        $token = $this->getToken($this->getAmexCard());
        $card = array();
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->verify(array(
            'token' => $token,
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Verify should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test06SaleVisaManualEntry()
    {
        $card = $this->getVisaCard();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '13.01',
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test06SaleVisaSingleUseToken()
    {
        $token = $this->getToken($this->getVisaCard());
        $card = array();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '13.01',
            'token' => $token,
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test07SaleMasterCardManualEntry()
    {
        $card = $this->getMastercardCard();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '13.02',
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test07SaleMasterCardSingleUseToken()
    {
        $token = $this->getToken($this->getMastercardCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '13.02',
            'token' => $token,
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test08SaleDiscoverManualEntry()
    {
        $card = $this->getDiscoverCard();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->purchase(array(
            'amount' => '13.03',
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test08SaleDiscoverSingleUseToken()
    {
        $token = $this->getToken($this->getDiscoverCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->purchase(array(
            'amount' => '13.03',
            'token' => $token,
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test09SaleAmexManualEntry()
    {
        $card = $this->getAmexCard();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '13.04',
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test09SaleAmexSingleUseToken()
    {
        $token = $this->getToken($this->getAmexCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '13.04',
            'token' => $token,
            'card' => $card,
            'requestCardReference' => true,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
        $this->assertNotEmpty($response->getData()['TokenValue']);
    }

    public function test10SaleVisaManualEntry()
    {
        // sale
        $token = static::$visaToken;
        $card = array();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '17.01',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // reversal
        $request = $this->gateway->reverse(array(
            'transactionReference' => $response->getTransactionReference(),
            'amount' => '17.01',
        ));
        $reponse = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Reverse should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test10SaleVisaSingleUseToken()
    {
        // sale
        $token = $this->getToken($this->getVisaCard());
        $card = array();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '17.01',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // reversal
        $request = $this->gateway->reverse(array(
            'transactionReference' => $response->getTransactionReference(),
            'amount' => '17.01',
        ));
        $reponse = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Reverse should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test11SaleMasterCardManualEntry()
    {
        $token = static::$mastercardToken;
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '17.02',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test11SaleMasterCardSingleUseToken()
    {
        $token = $this->getToken($this->getMastercardCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '17.02',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test12SaleDiscoverManualEntry()
    {
        $token = static::$discoverToken;
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->purchase(array(
            'amount' => '17.03',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test12SaleDiscoverSingleUseToken()
    {
        $token = $this->getToken($this->getDiscoverCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->purchase(array(
            'amount' => '17.03',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test13SaleAmexManualEntry()
    {
        $token = static::$amexToken;
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '17.04',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test13SaleAmexSingleUseToken()
    {
        $token = $this->getToken($this->getAmexCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->purchase(array(
            'amount' => '17.04',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test14SaleJcbManualEntry()
    {
        $card = $this->getJcbCard();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->purchase(array(
            'amount' => '17.05',
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test14SaleJcbSingleUseToken()
    {
        $token = $this->getToken($this->getJcbCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->purchase(array(
            'amount' => '17.05',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Purchase should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test15AuthorizeVisaManualEntry()
    {
        // authorize
        $card = $this->getVisaCard();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->authorize(array(
            'amount' => '17.06',
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Authorize should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // capture
        $request = $this->gateway->capture(array(
            'transactionReference' => $response->getTransactionReference(),
            'amount' => '17.06',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Capture should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test15AuthorizeVisaSingleUseToken()
    {
        // authorize
        $token = $this->getToken($this->getVisaCard());
        $card = array();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->authorize(array(
            'amount' => '17.06',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Authorize should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // capture
        $request = $this->gateway->capture(array(
            'transactionReference' => $response->getTransactionReference(),
            'amount' => '17.06',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Capture should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test16AuthorizeMasterCardManualEntry()
    {
        // authorize
        $card = $this->getMastercardCard();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->authorize(array(
            'amount' => '17.07',
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Authorize should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // capture
        $request = $this->gateway->capture(array(
            'transactionReference' => $response->getTransactionReference(),
            'amount' => '17.07',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Capture should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test16AuthorizeMasterCardSingleUseToken()
    {
        // authorize
        $token = $this->getToken($this->getMastercardCard());
        $card = array();
        $card['billingAddress1'] = '6860 Dallas Pkwy';
        $card['billingPostcode'] = '750241234';

        $request = $this->gateway->authorize(array(
            'amount' => '17.07',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Authorize should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // capture
        $request = $this->gateway->capture(array(
            'transactionReference' => $response->getTransactionReference(),
            'amount' => '17.07',
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Capture should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test17AuthorizeDiscoverManualEntry()
    {
        // authorize
        $card = $this->getDiscoverCard();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->authorize(array(
            'amount' => '17.08',
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Authorize should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // capture
        // ignore
    }

    public function test17AuthorizeDiscoverSingleUseToken()
    {
        // authorize
        $token = $this->getToken($this->getDiscoverCard());
        $card = array();
        $card['billingAddress1'] = '6860';
        $card['billingPostcode'] = '75024';

        $request = $this->gateway->authorize(array(
            'amount' => '17.08',
            'token' => $token,
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Authorize should succeed');
        $this->assertNotNull($response->getTransactionReference());

        // capture
        // ignore
    }

    public function test34ReturnMasterCardManualEntry()
    {
        $card = $this->getMastercardCard();

        $request = $this->gateway->refund(array(
            'amount' => '15.15',
            'card' => $card,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Refund should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    public function test34ReturnMasterCardSingleUseToken()
    {
        $token = $this->getToken($this->getMastercardCard());

        $request = $this->gateway->refund(array(
            'amount' => '15.15',
            'token' => $token,
        ));
        $response = $request->send();

        $this->assertTrue($response->isSuccessful(), 'Refund should succeed');
        $this->assertNotNull($response->getTransactionReference());
    }

    protected function getAmexCard()
    {
        return array(
            'number' => '372700699251018',
            'expiryMonth' => 12,
            'expiryYear' => 2017,
            'cvv' => 1234,
        );
    }

    protected function getDiscoverCard()
    {
        return array(
            'number' => '6011000990156527',
            'expiryMonth' => 12,
            'expiryYear' => 2017,
            'cvv' => 123,
        );
    }

    protected function getJcbCard()
    {
        return array(
            'number' => '3566007770007321',
            'expiryMonth' => 12,
            'expiryYear' => 2017,
            'cvv' => 123,
        );
    }

    protected function getMastercardCard()
    {
        return array(
            'number' => '5473500000000014',
            'expiryMonth' => 12,
            'expiryYear' => 2017,
            'cvv' => 123,
        );
    }

    protected function getVisaCard()
    {
        return array(
            'number' => '4012002000060016',
            'expiryMonth' => 12,
            'expiryYear' => 2017,
            'cvv' => 123,
        );
    }

    protected function getToken(array $card)
    {
        $payload = array(
            'object' => 'token',
            'token_type' => 'supt',
            'card' => array(
                'number' => $card['number'],
                'exp_month' => $card['expiryMonth'],
                'exp_year' => $card['expiryYear'],
                'cvc' => $card['cvv'],
            ),
        );

        $url = 'https://cert.api2.heartlandportico.com/Hps.Exchange.PosGateway.Hpf.v1/api/token?api_key=' . $this->publicKey;

        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($payload),
            ),
        );
        $context = stream_context_create($options);

        $response = json_decode(file_get_contents($url, false, $context));

        if (!$response || isset($response->error)) {
            $this->fail('no singl-use token obtained');
        }

        return $response->token_value;
    }
}
