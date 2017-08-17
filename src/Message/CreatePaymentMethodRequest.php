<?php

/**
 * Heartland Create Payment Method Request.
 */
namespace Omnipay\Heartland\Message;

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

        return $paymentMethodDetails;
    }

    public function addCreditCard()
    {
        $data = array();
        if ($this->getAccountNumber() != null) {
            $data['accountNumber'] = $this->getAccountNumber();
        } elseif ($this->getPaymentToken() != null) {
            $data['paymentToken'] = $this->getPaymentToken();
        }

        $data['http'] = array(
            'verb'     => 'POST',
            'uri' => 'paymentMethodsCreditCard',
        );
        return array_merge($this->getParameters(), $data);
    }

    /**
     *
     * @return mixed
     */
    public function addACH()
    {
        $this->validate('accountNumber', 'accountType', 'achType', 'routingNumber');

        $data = $this->getParameters();
        //remove unwanted param from request
        unset($data['paymentMethodType']);

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

    public function setCustomerReference($value)
    {
        $this->setParameter('customerKey', $value);
        return $this;
    }

    public function getCustomerReference()
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

    public function setPaymentStatus($value)
    {
        $this->setParameter('paymentStatus', $value);
        return $this;
    }

    public function getPaymentStatus()
    {
        return $this->getParameter('paymentStatus') !== null && $this->getParameter('paymentStatus') !== ''
            ? $this->getParameter('paymentStatus')
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
