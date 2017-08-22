<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Tests\TestCase;

class CreatePaymentMethodRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreatePaymentMethodRequest($this->getHttpClient(), $this->getHttpRequest());        
    }
    
    public function testGetMethodsForCC()
    {
        $ccDetails = array(
                'customerKey' => 66770,
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
                'accountHolderYob' => '1989',
                'driversLicenseState' => 'TX',
                'driversLicenseNumber' => '123456789',
                'socialSecurityNumberLast4' => '1234'
            );
        $this->request->initialize($ccDetails);
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');
        
        $this->assertSame($this->request->getCustomerKey(), 66770);
        $this->assertSame($this->request->getNameOnAccount(), 'John Doe');
        $this->assertSame($this->request->getAccountNumber(), '5473500000000014');
        $this->assertSame($this->request->getExpirationDate(), '1225');
        $this->assertSame($this->request->getAddressLine1(), '123 Main St.');
        $this->assertSame($this->request->getAddressLine2(), 'Suite 1A');
        $this->assertSame($this->request->getCity(), 'Anytown');        
        $this->assertSame($this->request->getStateProvince(), 'TX');
        $this->assertSame($this->request->getZipPostalCode(), '75024');
        $this->assertSame($this->request->getCountry(), 'USA');
    }
    
    public function testGetMethodsForACH()
    {
        $this->request->initialize(
            array(
                'customerKey' => 66770,
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
                'socialSecurityNumberLast4' => '1234'
            )
        );
        $this->request->setSecretApiKey('skapi_cert_MTyMAQBiHVEAewvIzXVFcmUd2UcyBge_eCpaASUp0A');

        $this->assertSame($this->request->getPaymentMethodType(), 'ACH');
        $this->assertSame($this->request->getAchType(), 'Checking');
        $this->assertSame($this->request->getAccountType(), 'Personal');
        $this->assertSame($this->request->getRoutingNumber(), '490000018');
        $this->assertSame($this->request->getAccountNumber(), '24413815');
        $this->assertSame($this->request->getAddressLine1(), '123 Main St.');
        $this->assertSame($this->request->getAddressLine2(), 'Suite 1A');
        $this->assertSame($this->request->getCity(), 'Anytown');
        $this->assertSame($this->request->getStateProvince(), 'TX');
        $this->assertSame($this->request->getZipPostalCode(), '75024');
    }    
}