<?php

/**
 * Heartland Get Payment Method By Id
 */
namespace Omnipay\Heartland\Message;

class FetchPaymentMethodRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanPaymentMethodGet';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('paymentMethodKey');

        $newdata = [];
        $newdata['http'] = array(
            'verb'     => 'GET',
            'uri' => 'paymentMethods/' . $this->getPaymentMethodKey(),
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
