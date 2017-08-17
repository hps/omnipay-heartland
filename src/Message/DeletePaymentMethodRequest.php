<?php

/**
 * Heartland Get Payment Method By Id
 */
namespace Omnipay\Heartland\Message;

class DeletePaymentMethodRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanPaymentMethodDelete';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('paymentMethodKey');

        $newdata = [];
        $newdata['http'] = array(
            'verb'     => 'DELETE',
            'uri' => 'paymentMethods/' . $this->getPaymentMethodReference(),
        );

        $actualData = $this->getParameters();
        unset($actualData['paymentMethodKey']);

        return array_merge($actualData, $newdata);
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

    public function setForceDelete($value)
    {
        $this->setParameter('forceDelete', $value);
        return $this;
    }

    public function getForceDelete()
    {
        return $this->getParameter('forceDelete');
    }
}
