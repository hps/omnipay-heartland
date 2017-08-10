<?php

/**
 *  Heartland Fetch Schedules Request.
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
 * @see CreateScheduleRequest
 * @see Omnipay\Heartland\Gateway
 */
class FetchSchedulesRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanSearchSchedules';
    }

    public function getData()
    {
        parent::getData();

        return array_merge($this->getParameters(), [
          'http' => [
            'uri' => 'searchSchedules',
            'verb' => 'POST'
          ]
        ]);
    }
}
