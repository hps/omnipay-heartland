<?php

/**
 *  Heartland Delete Customer Request.
 *
 * @category    HPS
 * @package     Omnipay_Heartland
 * @author      Heartland Developer Portal <EntApp_DevPortal@e-hps.com>
 * @copyright   Heartland (http://heartland.us)
 * @license     https://github.com/hps/omnipay-heartland/blob/master/LICENSE.md
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

/**
 *
 * @see DeleteCustomerRequest
 * @see Omnipay\Heartland\Gateway
 */
class DeleteCustomerRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanCustomerDelete';
    }

    public function getData()
    {
        parent::getData();

        return array(
          'http' => array(
            'uri' => 'customers/' . $this->getCustomerReference(),
            'verb' => 'DELETE'
          ),
        );
    }

    public function getCustomerReference()
    {
        return $this->getParameter('customerKey');
    }

    public function setCustomerReference($value)
    {
        return $this->setParameter('customerKey', $value);
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
