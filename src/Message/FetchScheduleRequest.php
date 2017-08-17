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
 * @see FetchScheduleRequest
 * @see Omnipay\Heartland\Gateway
 */
class FetchScheduleRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanScheduleGet';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('scheduleKey');

        return array(
          'http' => array(
            'uri' => 'schedules/' . $this->getScheduleReference(),
            'verb' => 'GET'
          ),
        );
    }

    public function getScheduleReference()
    {
        return $this->getParameter('scheduleKey');
    }

    public function setScheduleReference($value)
    {
        return $this->setParameter('scheduleKey', $value);
    }
}
