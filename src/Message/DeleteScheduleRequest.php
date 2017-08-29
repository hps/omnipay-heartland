<?php

/**
 *  Heartland Delete Schedule Request.
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
 * @see DeleteScheduleRequest
 * @see Omnipay\Heartland\Gateway
 */
class DeleteScheduleRequest extends AbstractPayPlanRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanScheduleDelete';
    }

    public function getData()
    {
        parent::getData();

        return array(
          'http' => array(
            'uri' => 'schedules/' . $this->getScheduleReference(),
            'verb' => 'DELETE'
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
