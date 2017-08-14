<?php

/**
 * Heartland Get Payment Method By Id
 */
namespace Omnipay\Heartland\Message;

class editPaymentMethodRequest extends AbstractPayPlanRequest
{
    const ACH         = 'ACH';
    const CREDIT_CARD = 'Credit Card';

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'EditPayPlanPaymentMethod';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('paymentMethodKey');
        
        if ($this->getPaymentMethodType() != null && $this->getPaymentMethodType() == self::ACH) {
            $result = $this->editACH();
        } else {
            $result = $this->editCreditCard();
        }
        
        return array_merge($actualData, $result);
    }
    
    private function editCreditCard()
    {
        $data = [];
        $data['http'] = array(
            'uri'     => 'PUT',
            'endpoint' => 'paymentMethodsCreditCard/'.$this->getPaymentMethodKey(),
        );
        return $data;
    }
    
    private function editACH()
    {
        $data = [];
        $data['http'] = array(
            'uri'     => 'PUT',
            'endpoint' => 'paymentMethodsACH/'.$paymentMethod->paymentMethodKey,
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
