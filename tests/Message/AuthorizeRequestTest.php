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

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('AuthorizeSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023514041', $response->getTransactionReference());
        $this->assertSame('Success', $response->getMessage());
        $this->assertSame('00', $response->getCode());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('AuthorizeFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('1023522465', $response->getTransactionReference());
        $this->assertSame('Transaction rejected because the lookup on the supplied token failed.', $response->getMessage());
        $this->assertSame('6', $response->getReasonCode());
    }


    public function testSendGatewayError()
    {
        $this->setMockHttpResponse('AuthorizeFailureGatewayError.txt');
        $response = $this->request->send();

        $this->assertSame($response->getMessage(), 'Unexpected response');
        $this->assertNotNull($response->reversalDataObject->getData());    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage PHP-SDK cURL TLS 1.2 handshake failed. If you have any questions, please contact Heartland's Specialty Products Team at 866.802.9753
     */
    public function testSendCurlError()
    {
        $this->setMockHttpResponse('AuthorizeFailureCurlError.txt');
        $response = $this->request->send();
    }
}
