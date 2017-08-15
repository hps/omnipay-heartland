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
            'uri' => 'customers/' . $this->getCustomerKey(),
            'verb' => 'DELETE'
          ),
        );
    }

    public function getCustomerKey()
    {
        return $this->getParameter('customerKey');
    }

    public function setCustomerKey($value)
    {
        return $this->setParameter('customerKey', $value);
    }
}
