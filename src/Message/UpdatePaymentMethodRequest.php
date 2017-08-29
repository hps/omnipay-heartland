<?php

/**
 * Heartland GetPayment Method By Id
 */
namespace Omnipay\Heartland\Message;

class UpdatePaymentMethodRequest extends CreatePaymentMethodRequest
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
        $this->validate('paymentMethodKey');

        $data = $this->getParameters();
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

        return array_merge($data, $result);
    }

    private function editCreditCard()
    {
        $data = [];
        $data['http'] = array(
            'uri' => 'paymentMethodsCreditCard/' . $this->getPaymentMethodReference(),
            'verb' => 'PUT',
        );
        return $data;
    }

    private function editACH()
    {
        $data = [];
        $data['http'] = array(
            'uri' => 'paymentMethodsACH/' . $this->getPaymentMethodReference(),
            'verb' => 'PUT',
        );
        return $data;
    }

    public function setPaymentMethodReference($value)
    {
        $this->setParameter('paymentMethodKey', $value);
        return $this;
    }

    public function getPaymentMethodReference()
    {
        return $this->getParameter('paymentMethodKey');
    }
}
