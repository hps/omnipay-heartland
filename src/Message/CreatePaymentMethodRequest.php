<?php

/**
 * Heartland Create Customer Request.
 */
namespace Omnipay\Heartland\Message;

/**
 * Heartland Create Customer Request.
 *
 * Customer objects allow you to perform recurring charges and
 * track multiple charges that are associated with the same customer.
 * The API allows you to create, delete, and update your customers.
 * You can retrieve individual customers as well as a list of all of
 * your customers.
 *
 * ### Examples
 *
 * #### Create Customer from Email Address
 *
 * This is the recommended way to create a customer object.
 *
 * <code>
 * $response = $gateway->createCustomer(array(
 *     'description'       => 'Test Customer',
 *     'email'             => 'test123@example.com',
 * ))->send();
 * if ($response->isSuccessful()) {
 *     echo "Gateway createCustomer was successful.\n";
 *     // Find the card ID
 *     $customer_id = $response->getCustomerReference();
 *     echo "Customer ID = " . $customer_id . "\n";
 * } else {
 *     echo "Gateway createCustomer failed.\n";
 *     echo "Error message == " . $response->getMessage() . "\n";
 * }
 * </code>
 *
 * The $customer_id can now be used in a createCard() call.
 *
 * #### Create Customer using Card Object
 *
 * Historically, this library used a card object to create customers.
 * Although this is no longer the recommended path, it is still supported.
 * Using this approach, a customer object and a card object can be created
 * at the same time.
 *
 * <code>
 * // Create a credit card object
 * // This card can be used for testing.
 * // The CreditCard object is also used for creating customers.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4242424242424242',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 *             'email'                 => 'customer@example.com',
 *             'billingAddress1'       => '1 Scrubby Creek Road',
 *             'billingCountry'        => 'AU',
 *             'billingCity'           => 'Scrubby Creek',
 *             'billingPostcode'       => '4999',
 *             'billingState'          => 'QLD',
 * ));
 *
 * // Do a create customer transaction on the gateway
 * $response = $gateway->createCustomer(array(
 *     'card'                     => $card,
 * ))->send();
 * if ($response->isSuccessful()) {
 *     echo "Gateway createCustomer was successful.\n";
 *     // Find the customer ID
 *     $customer_id = $response->getCustomerReference();
 *     echo "Customer ID = " . $customer_id . "\n";
 *     // Find the card ID
 *     $card_id = $response->getCardReference();
 *     echo "Card ID = " . $card_id . "\n";
 * }
 * </code>
 *
 * @link https://heartland.com/docs/api#customers
 */
class CreatePaymentMethodRequest extends AbstractPayPlanRequest
{
    private $alphabet = 'abcdefghijklmnopqrstuvwxyz';
    const ACH         = 'ACH';
    const CREDIT_CARD = 'Credit Card';

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanPaymentMethodAdd';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('customerKey');
        
        if ($this->getPaymentMethodType() != null && $this->getPaymentMethodType() == self::ACH) {
            $paymentMethodDetails = $this->addACH();
        } else { 
            $paymentMethodDetails = $this->addCreditCard();
        }

        return array_merge($this->getParameters(), $paymentMethodDetails);
    }
    
    public function addCreditCard()
    {       
        
        $data = array();
        if ($this->getAccountNumber() != null) {
            $data['accountNumber'] = $this->getAccountNumber();
        } else if ($this->getPaymentToken() != null) {
            $data['paymentToken'] = $this->getPaymentToken();
        }
        
        $data['http'] = array(
            'verb'     => 'POST',
            'uri' => 'paymentMethodsCreditCard',
        );
        return $data;
    }
    
    /**
     *
     * @return mixed
     */
    public function addACH()
    {
        $this->validate('accountNumber', 'accountType', 'achType', 'routingNumber');
        
        $data['http'] = array(
            'verb'     => 'POST',
            'uri' => 'paymentMethodsACH',
        );
        return $data;
    }
    
    public function setExpirationDate($value)
    {
        $this->setParameter('expirationDate', $value);
        return $this;
    }

    public function getExpirationDate()
    {
        return $this->getParameter('expirationDate');
    }
    
    public function setRoutingNumber($value)
    {
        $this->setParameter('routingNumber', $value);
        return $this;
    }

    public function getRoutingNumber()
    {
        return $this->getParameter('routingNumber');
    }
    
    public function setAchType($value)
    {
        $this->setParameter('achType', $value);
        return $this;
    }

    public function getAchType()
    {
        return $this->getParameter('achType');
    }
    
    public function setAccountType($value)
    {
        $this->setParameter('accountType', $value);
        return $this;
    }

    public function getAccountType()
    {
        return $this->getParameter('accountType');
    }
    
    public function setPaymentToken($value)
    {
        $this->setParameter('paymentToken', $value);
        return $this;
    }

    public function getPaymentToken()
    {
        return $this->getParameter('paymentToken');
    }
    
    public function setPaymentMethodType($value)
    {
        $this->setParameter('paymentMethodType', $value);
        return $this;
    }

    public function getPaymentMethodType()
    {
        return $this->getParameter('paymentMethodType');
    }

    public function setCustomerKey($value)
    {
        $this->setParameter('customerKey', $value);
        return $this;
    }

    public function getCustomerKey()
    {
        return $this->getParameter('customerKey');
    }

    public function setNameOnAccount($value)
    {
        $this->setParameter('nameOnAccount', $value);
        return $this;
    }

    public function getNameOnAccount()
    {
        return $this->getParameter('nameOnAccount');
    }

    public function setAccountNumber($value)
    {
        $this->setParameter('accountNumber', $value);
        return $this;
    }

    public function getAccountNumber()
    {
        return $this->getParameter('accountNumber');
    }
    
    public function setCustomerStatus($value)
    {
        $this->setParameter('customerStatus', $value);
        return $this;
    }

    public function getCustomerStatus()
    {
        return $this->getParameter('customerStatus') !== null && $this->getParameter('customerStatus') !== ''
            ? $this->getParameter('customerStatus')
            : static::ACTIVE;
    }

    public function setTitle($value)
    {
        $this->setParameter('title', $value);
        return $this;
    }

    public function getTitle()
    {
        return $this->getParameter('title');
    }

    public function setDepartment($value)
    {
        $this->setParameter('department', $value);
        return $this;
    }

    public function getDepartment()
    {
        return $this->getParameter('department');
    }

    public function setPrimaryEmail($value)
    {
        $this->setParameter('primaryEmail', $value);
        return $this;
    }

    public function getPrimaryEmail()
    {
        return $this->getParameter('primaryEmail');
    }

    public function setSecondaryEmail($value)
    {
        $this->setParameter('secondaryEmail', $value);
        return $this;
    }

    public function getSecondaryEmail()
    {
        return $this->getParameter('secondaryEmail');
    }

    public function setPhoneDay($value)
    {
        $this->setParameter('phoneDay', $value);
        return $this;
    }

    public function getPhoneDay()
    {
        return $this->getParameter('phoneDay');
    }

    public function setPhoneDayExt($value)
    {
        $this->setParameter('phoneDayExt', $value);
        return $this;
    }

    public function getPhoneDayExt()
    {
        return $this->getParameter('phoneDayExt');
    }

    public function setPhoneEvening($value)
    {
        $this->setParameter('phoneEvening', $value);
        return $this;
    }

    public function getPhoneEvening()
    {
        return $this->getParameter('phoneEvening');
    }

    public function setPhoneEveningExt($value)
    {
        $this->setParameter('phoneEveningExt', $value);
        return $this;
    }

    public function getPhoneEveningExt()
    {
        return $this->getParameter('phoneEveningExt');
    }

    public function setPhoneMobile($value)
    {
        $this->setParameter('phoneMobile', $value);
        return $this;
    }

    public function getPhoneMobile()
    {
        return $this->getParameter('phoneMobile');
    }

    public function setPhoneMobileExt($value)
    {
        $this->setParameter('phoneMobileExt', $value);
        return $this;
    }

    public function getPhoneMobileExt()
    {
        return $this->getParameter('phoneMobileExt');
    }

    public function setFax($value)
    {
        $this->setParameter('fax', $value);
        return $this;
    }

    public function getFax()
    {
        return $this->getParameter('fax');
    }

    public function setAddressLine1($value)
    {
        $this->setParameter('addressLine1', $value);
        return $this;
    }

    public function getAddressLine1()
    {
        return $this->getParameter('addressLine1');
    }

    public function setAddressLine2($value)
    {
        $this->setParameter('addressLine2', $value);
        return $this;
    }

    public function getAddressLine2()
    {
        return $this->getParameter('addressLine2');
    }

    public function setCity($value)
    {
        $this->setParameter('city', $value);
        return $this;
    }

    public function getCity()
    {
        return $this->getParameter('city');
    }

    public function setStateProvince($value)
    {
        $this->setParameter('stateProvince', $value);
        return $this;
    }

    public function getStateProvince()
    {
        return $this->getParameter('stateProvince');
    }

    public function setZipPostalCode($value)
    {
        $this->setParameter('zipPostalCode', $value);
        return $this;
    }

    public function getZipPostalCode()
    {
        return $this->getParameter('zipPostalCode');
    }

    public function setCountry($value)
    {
        $this->setParameter('country', $value);
        return $this;
    }

    public function getCountry()
    {
        return $this->getParameter('country');
    }

}
