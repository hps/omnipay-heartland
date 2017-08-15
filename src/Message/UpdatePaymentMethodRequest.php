<?php

/**
 * Heartland Get Payment Method By Id
 */
namespace Omnipay\Heartland\Message;

class UpdatePaymentMethodRequest extends AbstractPayPlanRequest
{
    const ACH         = 'ACH';
    const CREDIT_CARD = 'Credit Card';

    protected $allowedFields = array(
        'preferredPayment',
        'paymentStatus',
        'paymentMethodIdentifier',
        'nameOnAccount',
        'addressLine1',
        'addressLine2',
        'city',
        'stateProvince',
        'zipPostalCode',
    );

    protected $allowedFieldsIfACH = array(
        'telephoneIndicator',
        'accountHolderYob',
        'driversLicenseState',
        'driversLicenseNumber',
        'socialSecurityNumberLast4',
    );

    protected $allowedFieldsIfCC = array(
        'expirationDate',
        'country',
        'cpcTaxType',
    );

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanPaymentMethodEdit';
    }

    public function getData()
    {
        $data = parent::getData();
        $this->validate('paymentMethodKey');

        $allowedFields = $this->allowedFields;
        $result = [];

        if ($this->getPaymentMethodType() != null && $this->getPaymentMethodType() == self::ACH) {
            $result = $this->editACH();
            $allowedFields = array_merge($allowedFields, $this->allowedFieldsIfACH);
        } else {
            $result = $this->editCreditCard();
            $allowedFields = array_merge($allowedFields, $this->allowedFieldsIfCC);
        }

        $data = array_intersect_key($data, array_flip($allowedFields));

        return array_merge($actualData, $result);
    }

    private function editCreditCard()
    {
        $data = [];
        $data['http'] = array(
            'uri'     => 'PUT',
            'endpoint' => 'paymentMethodsCreditCard/' . $this->getPaymentMethodKey(),
        );
        return $data;
    }

    private function editACH()
    {
        $data = [];
        $data['http'] = array(
            'uri'     => 'PUT',
            'endpoint' => 'paymentMethodsACH/' . $this->getPaymentMethodKey(),
        );
        return $data;
    }

    public function setPaymentMethodKey($value)
    {
        $this->setParameter('paymentMethodKey', $value);
        return $this;
    }

    public function getPaymentMethodKey()
    {
        return $this->getParameter('paymentMethodKey');
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
}
