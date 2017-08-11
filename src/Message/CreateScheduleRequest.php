<?php

/**
 * Heartland Create Schedule Request.
 */
namespace Omnipay\Heartland\Message;

/**
 * Heartland Create Schedule Request.
 */
class CreateScheduleRequest extends AbstractPayPlanRequest
{
    private $alphabet = 'abcdefghijklmnopqrstuvwxyz';
    const ACTIVE   = 'Active';
    const INACTIVE = 'Inactive';

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'PayPlanScheduleAdd';
    }

    public function getData()
    {
        parent::getData();

        return array_merge($this->getParameters(), array(
            'scheduleIdentifier' => $this->getScheduleIdentifier(),
            'scheduleStatus'     => $this->getScheduleStatus(),
            'http' => array(
              'uri' => 'schedules',
              'verb' => 'POST'
            ),
        ));
    }

    public function setScheduleIdentifier($value)
    {
        $this->setParameter('scheduleIdentifier', $value);
        return $this;
    }

    public function getScheduleIdentifier()
    {
        return $this->getParameter('scheduleIdentifier') !== null && $this->getParameter('scheduleIdentifier') !== ''
            ? $this->getParameter('scheduleIdentifier')
            : $this->generateIdentifier();
    }

    public function setCustomerKey($value)
    {
        $this->setParameter('customerKey', $value);
        return $this;
    }

    public function getCustomerKey()
    {
        return $this->getParameter('customerKey');
    }

    public function setScheduleName($value)
    {
        $this->setParameter('scheduleName', $value);
        return $this;
    }

    public function getScheduleName()
    {
        return $this->getParameter('scheduleName');
    }

    public function setScheduleStatus($value)
    {
        $this->setParameter('scheduleStatus', $value);
        return $this;
    }

    public function getScheduleStatus()
    {
        return $this->getParameter('scheduleStatus') !== null && $this->getParameter('scheduleStatus') !== ''
            ? $this->getParameter('scheduleStatus')
            : static::ACTIVE;
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

    public function setSubtotalAmount($value)
    {
        $this->setParameter('subtotalAmount', $value);
        return $this;
    }

    public function getSubtotalAmount()
    {
        return $this->getParameter('subtotalAmount');
    }

    public function setTaxAmount($value)
    {
        $this->setParameter('taxAmount', $value);
        return $this;
    }

    public function getTaxAmount()
    {
        return $this->getParameter('taxAmount');
    }

    public function setStartDate($value)
    {
        $this->setParameter('startDate', $value);
        return $this;
    }

    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    public function setProcessingDateInfo($value)
    {
        $this->setParameter('processingDateInfo', $value);
        return $this;
    }

    public function getProcessingDateInfo()
    {
        return $this->getParameter('processingDateInfo');
    }

    public function setFrequency($value)
    {
        $this->setParameter('frequency', $value);
        return $this;
    }

    public function getFrequency()
    {
        return $this->getParameter('frequency');
    }

    public function setDuration($value)
    {
        $this->setParameter('duration', $value);
        return $this;
    }

    public function getDuration()
    {
        return $this->getParameter('duration');
    }

    public function setNumberOfPayments($value)
    {
        $this->setParameter('numberOfPayments', $value);
        return $this;
    }

    public function getNumberOfPayments()
    {
        return $this->getParameter('numberOfPayments');
    }

    public function setEndDate($value)
    {
        $this->setParameter('endDate', $value);
        return $this;
    }

    public function getEndDate()
    {
        return $this->getParameter('endDate');
    }

    public function setReprocessingCount($value)
    {
        $this->setParameter('reprocessingCount', $value);
        return $this;
    }

    public function getReprocessingCount()
    {
        return $this->getParameter('reprocessingCount');
    }

    public function setDebtRepayInd($value)
    {
        $this->setParameter('debtRepayInd', $value);
        return $this;
    }

    public function getDebtRepayInd()
    {
        return $this->getParameter('debtRepayInd');
    }

    public function setInvoiceNbr($value)
    {
        $this->setParameter('invoiceNbr', $value);
        return $this;
    }

    public function getInvoiceNbr()
    {
        return $this->getParameter('invoiceNbr');
    }

    public function setPoNumber($value)
    {
        $this->setParameter('poNumber', $value);
        return $this;
    }

    public function getPoNumber()
    {
        return $this->getParameter('poNumber');
    }

    public function setDescription($value)
    {
        $this->setParameter('description', $value);
        return $this;
    }

    public function getDescription()
    {
        return $this->getParameter('description');
    }

    public function setEmailReceipt($value)
    {
        $this->setParameter('emailReceipt', $value);
        return $this;
    }

    public function getEmailReceipt()
    {
        return $this->getParameter('emailReceipt');
    }

    public function setEmailAdvanceNotice($value)
    {
        $this->setParameter('emailAdvanceNotice', $value);
        return $this;
    }

    public function getEmailAdvanceNotice()
    {
        return $this->getParameter('emailAdvanceNotice');
    }


    protected function generateIdentifier()
    {
        $format = '%s-Omnipay-%s';
        return sprintf($format, date('Ymd'), substr(str_shuffle($this->alphabet), 0, 10));
    }
}
