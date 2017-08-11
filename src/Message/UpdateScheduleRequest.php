<?php

/**
 * Heartland Update Schedule Request.
 */
namespace Omnipay\Heartland\Message;

/**
 * Heartland Update Schedule Request.
 */
class UpdateScheduleRequest extends CreateScheduleRequest
{
    protected $allowedFields = array(
        'scheduleName',
        'scheduleStatus',
        'deviceId',
        'paymentMethodKey',
        'subtotalAmount',
        'taxAmount',
        'numberOfPaymentRemaining',
        'endDate',
        'reprocessingCount',
        'emailReceipt',
        'emailAdvanceNotice',
        'processingDateInfo',
        'invoiceNbr',
        'poNumber',
        'description',
    );

    protected $allowedFieldsIfNotStarted = array(
        'scheduleIdentifier',
        'startDate',
        'frequency',
        'duration',
    );

    protected $allowedFieldsIfStarted = array(
        'nextProcessingDate',
        'cancellationDate',
    );

    /**
        * @return string
        */
    public function getTransactionType()
    {
        return 'PayPlanScheduleEdit';
    }

    public function getData()
    {
        $data = parent::getData();
        $key = $data['scheduleKey'];

        $allowedFields = array();

        switch ($this->getScheduleStarted()) {
            case 'true':
                $allowedFields = array_merge(
                    $this->allowedFields,
                    $this->allowedFieldsIfStarted
                );
                break;
            case 'false':
                $allowedFields = array_merge(
                    $this->allowedFields,
                    $this->allowedFieldsIfNotStarted
                );
                break;
            default:
                $allowedFields = array_merge(
                    $this->allowedFields,
                    $this->allowedFieldsIfStarted,
                    $this->allowedFieldsIfNotStarted
                );
                break;
        }

        $data = array_intersect_key($data, array_flip($allowedFields));

        return array_merge($data, array(
            'http' => array(
                'uri' => 'schedules/' . $key,
                'verb' => 'PUT',
            ),
        ));
    }

    public function getScheduleKey()
    {
        return $this->getParameter('scheduleKey');
    }

    public function setScheduleKey($value)
    {
        $this->setParameter('scheduleKey', $value);
        return $this;
    }

    public function getScheduleStarted()
    {
        return $this->getParameter('scheduleStarted');
    }

    public function setScheduleStarted($value)
    {
        return $this->setParameter('scheduleStarted', $value);
    }
}
