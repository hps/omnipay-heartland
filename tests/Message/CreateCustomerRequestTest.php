<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class CreateCustomerRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreateCustomerRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'customerIdentifier' => '08102017-Omnipay-fghduisakhngiueqwbngikra',
                'customerStatus' => 'Active',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'country' => 'USA',
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
    }

    public function testGetValues()
    {
        $this->request->initialize(
            array(
                'customerIdentifier' => "Test Customer 106",
                'customerStatus' => "Active",
                'firstName' => "Test",
                'lastName' => "Customer",
                'company' => "Test Company",
                'primaryEmail' => "test@test.com",
                'secondaryEmail' => "",
                'phoneDay' => "5551112222",
                'phoneDayExt' => "1234",
                'phoneEvening' => "5552223333",
                'phoneEveningExt' => "",
                'phoneMobile' => "",
                'phoneMobileExt' => "",
                'fax' => "",
                'title' => "",
                'department' => "",
                'addressLine1' => "123 A Street",
                'addressLine2' => "",
                'city' => "Anytown",
                'country' => "USA",
                'stateProvince' => "PA",
                'zipPostalCode' => "54321"
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
        
        $this->assertSame('Test', $this->request->getFirstName());
        $this->assertSame('Customer', $this->request->getLastName());
        $this->assertSame('USA', $this->request->getCountry());
        $this->assertSame('Test Company', $this->request->getCompany());
        $this->assertSame('', $this->request->getTitle());
        $this->assertSame('', $this->request->getDepartment());
        $this->assertSame('test@test.com', $this->request->getPrimaryEmail());        
        $this->assertSame('', $this->request->getSecondaryEmail());
        $this->assertSame('5551112222', $this->request->getPhoneDay());
        $this->assertSame('1234', $this->request->getPhoneDayExt());
        $this->assertSame('5552223333', $this->request->getPhoneEvening());
        $this->assertSame('', $this->request->getPhoneEveningExt());
        $this->assertSame('', $this->request->getPhoneMobile());
        $this->assertSame('', $this->request->getPhoneMobileExt());
        $this->assertSame('', $this->request->getFax());
        $this->assertSame('123 A Street', $this->request->getAddressLine1());
        $this->assertSame('', $this->request->getAddressLine2());
        $this->assertSame('Anytown', $this->request->getCity());
        $this->assertSame('PA', $this->request->getStateProvince());
        $this->assertSame('54321', $this->request->getZipPostalCode());
    }
        
    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CreateCustomerSuccess.txt');
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }
}
