<?php

/**
 * Heartland Get Payment Method By Id
 */
namespace Omnipay\Heartland\Message;

class getPaymentMethodRequest extends AbstractPayPlanRequest
{
    const ACH         = 'ACH';
    const CREDIT_CARD = 'Credit Card';

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'GetPayPlanPaymentMethod';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('paymentMethodKey');
        
        $newdata = [];
        $newdata['http'] = array(
            'verb'     => 'GET',
            'uri' => 'paymentMethods/'.$this->getPaymentMethodKey(),
        );
        
        $actualData = $this->getParameters();
        unset($actualData['paymentMethodKey']);
        
        return array_merge($actualData, $newdata);
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
}
