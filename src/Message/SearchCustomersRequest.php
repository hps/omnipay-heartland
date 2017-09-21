<?php

/**
 *  Heartland Search Customers Request.
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
 * @see SearchCustomersRequest
 * @see Omnipay\Heartland\Gateway
 */
class SearchCustomersRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanCustomersSearch';
    }

    public function getData()
    {
        parent::getData();

        return array_merge($this->getParameters(), array(
          'http' => array(
            'uri' => 'searchCustomers',
            'verb' => 'POST'
          ),
        ));
    }
}
