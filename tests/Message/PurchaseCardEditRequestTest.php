<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class PurchaseCardEditRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new PurchaseCardEditRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->setSecretApiKey('skapi_cert_MYl2AQAowiQAbLp5JesGKh7QFkcizOP2jcX9BrEMqQ');
        $this->request->setTransactionReference('1024868871')
            ->setTaxAmount('10.00')
            ->setTaxType('SALESTAX')
            ->setCardHolderPONumber('123456789');
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('PurchaseCardEditSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('Success', $response->getMessage());
    }

    public function testSendErrorInvalidState()
    {
        $this->setMockHttpResponse('PurchaseCardEditFailureInvalidState.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('Transaction rejected because the referenced original transaction is invalid. Subject \'1024868871\'.  Original transaction is not in a valid state.', $response->getMessage());
    }
}
