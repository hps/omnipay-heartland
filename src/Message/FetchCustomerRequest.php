<?php

/**
 *  Heartland Fetch Customer Request.
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
 * @see FetchCustomerRequest
 * @see Omnipay\Heartland\Gateway
 */
class FetchCustomerRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanCustomerGet';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('customerKey');

        return array(
          'http' => array(
            'uri' => 'customers/' . $this->getCustomerReference(),
            'verb' => 'GET'
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
}
