<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class AuthorizeRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new AuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'currency' => 'USD',
                'card' => $this->getValidCard(),
                'description' => 'Order #42'
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ');
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The siteId parameter is required
     */
    public function testSiteIdRequired()
    {
        $this->request->setSecretApiKey(null);
        $this->request->getData();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The deviceId parameter is required
     */
    public function testDeviceIdRequired()
    {
        $this->request->setSecretApiKey(null);
        $this->request->setSiteId('20518');
        $this->request->getData();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The licenseId parameter is required
     */
    public function testLicenseIdRequired()
    {
        $this->request->setSecretApiKey(null);
        $this->request->setSiteId('20518');
        $this->request->setDeviceId('90911395');        
        $this->request->getData();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The username parameter is required
     */
    public function testUsernameRequired()
    {
        $this->request->setSecretApiKey(null);
        $this->request->setSiteId('20518');
        $this->request->setDeviceId('90911395');  
        $this->request->setLicenseId('20527');  
        $this->request->getData();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The password parameter is required
     */
    public function testPasswordRequired()
    {
        $this->request->setSecretApiKey(null);
        $this->request->setSiteId('20518');
        $this->request->setDeviceId('90911395');  
        $this->request->setLicenseId('20527'); 
        $this->request->setUsername('30360021'); 
        $this->request->getData();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The soapServiceUri parameter is required
     */
    public function testSoapServiceUriRequired()
    {
        $this->request->setSecretApiKey(null);
        $this->request->setSiteId('20518');
        $this->request->setDeviceId('90911395');  
        $this->request->setLicenseId('20527'); 
        $this->request->setUsername('30360021'); 
        $this->request->setPassword('$Test1234'); 
        $this->request->getData();        
    }    
     
    public function testDOMElementCreated()
    {
        $this->request->setSecretApiKey(null);
        $this->request->setSiteId('20518');
        $this->request->setDeviceId('90911395');  
        $this->request->setLicenseId('20527'); 
        $this->request->setUsername('30360021'); 
        $this->request->setPassword('$Test1234'); 
        $this->request->setSoapServiceUri("https://api-uat.heartlandportico.com/paymentserver.v1/PosGatewayService.asmx"); 
        $data = $this->request->getData();  
        $this->assertInstanceOf('DOMElement', $data);
    }
    
    public function testSecretApikey()
    {
        $data = $this->request->getData(); 
        $this->assertSame('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ', $this->request->getSecretApiKey());
        $this->assertInstanceOf('DOMElement', $data);
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The card parameter is required
     */
    public function testCardRequired()
    {
        $this->request->setCard(null);
        $this->request->getData();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The card parameter is required
     */
    public function testTokenNull()
    {
        $this->request->setCard(null);
        $this->request->setToken(null);
        $this->request->getData();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The card parameter is required
     */
    public function testCardReferenceNull()
    {
        $this->request->setCard(null);
        $this->request->setToken(null);
        $this->request->setCardReference(null);
        $this->request->getData();
    }
    
    public function testDataWithCard()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertInstanceOf('DOMElement', $data);
    }
        
    
    public function testDataWithToken()
    {
        $this->request->setToken('supt_ca67zN30E7YEE1etcQabwo4g');
        $data = $this->request->getData();

        $this->assertSame('supt_ca67zN30E7YEE1etcQabwo4g', $this->request->getToken());
        $this->assertInstanceOf('DOMElement', $data);
    }
    
    public function testDataWithCardReference()
    {
        $this->request->setCardReference('supt_ca67zN30E7YEE1etcQabwo4g');
        $data = $this->request->getData();

        $this->assertSame('supt_ca67zN30E7YEE1etcQabwo4g', $this->request->getToken());
        $this->assertSame('supt_ca67zN30E7YEE1etcQabwo4g', $this->request->getCardReference());
        $this->assertInstanceOf('DOMElement', $data);
    }
    
    
    public function testSendSuccess()
    {
        $this->setMockHttpResponse('AuthorizeSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());        
        $this->assertSame('1023514041', (string) $response->getTransactionReference());
        $this->assertSame('Success', (string) $response->getMessage());
        $this->assertSame('200', (string) $response->getCode());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     * @expectedExceptionMessage Transaction rejected because the lookup on the supplied token failed.
     */
    public function testSendError()
    {
        $this->setMockHttpResponse('AuthorizeFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023522465', (string) $response->getTransactionReference());
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     * @expectedExceptionMessage gateway_time-out
     */
    public function testSendGatewayError()
    {
        $this->setMockHttpResponse('AuthorizeFailureGatewayError.txt');
        $response = $this->request->send();
    }
    
    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     * @expectedExceptionMessage PHP-SDK cURL TLS 1.2 handshake failed. If you have any questions, please contact Specialty Products Team at 866.802.9753
     */
    public function testSendCurlError()
    {
        $this->setMockHttpResponse('AuthorizeFailureCurlError.txt');
        $response = $this->request->send();
    }
    
    
}
