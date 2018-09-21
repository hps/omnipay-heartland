<?php

/**
 *  Heartland Fetch Transaction Request.
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
 * Heartland Search Transactions Request.
 *
 * Example -- note this example assumes that the purchase has been successful
 * and that the transaction ID returned from the purchase is held in $sale_id.
 * See PurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Search for transactions
 *   $transaction = $gateway->searchTransactions();
 *   $response = $transaction->send();
 *   $data = $response->getData();
 *   echo "Gateway SearchTransactions response data == " . print_r($data, true) . "\n";
 * </code>
 *
 * @see  PurchaseRequest
 * @see  Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2-c.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-FindTransactions.html
 * @codingStandardsIgnoreEnd
 */
class SearchTransactionsRequest extends AbstractPorticoRequest
{
    protected $availableCriteria = [
        'StartUtcDT',
        'EndUtcDT',
        'AuthCode',
        'CardHolderLastName',
        'CardHolderFirstName',
        'CardNbrFirstSix',
        'CardNbrLastFour',
        'InvoiceNbr',
        'CardHolderPONbr',
        'CustomerID',
        'ServiceName',
        'PaymentType',
        'CardType',
        'IssuerResult',
        'SettlementAmt',
        'IssTxnId',
        'RefNbr',
        'UserName',
        'ClerkID',
        'BatchSeqNbr',
        'BatchId',
        'SiteTrace',
        'DisplayName',
        'ClientTxnId',
        'UniqueDeviceId',
        'AcctNbrLastFour',
        'BankRoutingNbr',
        'CheckNbr',
        'CheckFirstName',
        'CheckLastName',
        'CheckName',
        'GiftCurrency',
        'GiftMaskedAlias',
        'OneTime',
        'PaymentMethodKey',
        'ScheduleID',
        'BuyerEmailAddress',
        'AltPaymentStatus',
        'FullyCapturedInd',
    ];

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'FindTransactions';
    }

    public function getData()
    {
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsFindTransactions = $xml->createElement('hps:' . $this->getTransactionType());

        if ($this->getTransactionReference() && !$this->hasSearchCriteria()) {
            $hpsFindTransactions->appendChild($xml->createElement('hps:TxnId', $this->getTransactionReference()));
        }

        if (!$this->getTransactionReference() && $this->hasSearchCriteria()) {
            $hpsCriteria = $xml->createElement('hps:Criteria');

            foreach ($this->availableCriteria as $criteria) {
                if ($this->getParameter($criteria)) {
                    $hpsCriteria->appendChild($xml->createElement('hps:' . $criteria, $this->getParameter($criteria)));
                }
            }

            $hpsFindTransactions->appendChild($hpsCriteria);
        }

        $hpsTransaction->appendChild($hpsFindTransactions);

        return $hpsTransaction;
    }

    protected function hasSearchCriteria()
    {
        $result = false;
        $params = $this->getParameters();

        foreach ($params as $criteria => $value) {
            if (in_array($criteria, $this->availableCriteria)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Start date time used to filter the results to a particular date time range.
     * The date time must be specified in UTC (Coordinated Universal Time). If the
     * element is not present, start date time will be defaulted to six days prior
     * at 00:00:00.
     *
     * Transactions must have been run at or after this time to be returned.
     */
    public function setStartDatetime($value)
    {
        return $this->setParameter('StartUtcDT', $value);
    }

    public function getStartDatetime()
    {
        return $this->getParameter('StartUtcDT');
    }

    /**
     * End date time used to filter the results to a particular date time range.
     * The date time must be specified in UTC (Coordinated Universal Time). If
     * the element is not present, start date time will be defaulted to tomorrow
     * at 00:00:00.
     *
     * Transactions must have been run before or at this time to be returned.
     *
     * Note: The end date/time must be after the start date/time.
     */
    public function setEndDatetime($value)
    {
        return $this->setParameter('EndUtcDT', $value);
    }

    public function getEndDatetime()
    {
        return $this->getParameter('EndUtcDT');
    }

    /**
     * A specific authorization code
     */
    public function setAuthorizationCode($value)
    {
        return $this->setParameter('AuthCode', $value);
    }

    public function getAuthorizationCode()
    {
        return $this->getParameter('AuthCode');
    }

    /**
     * The supplied string must appear somewhere in the last name of the
     * associated card holder.
     */
    public function setCardHolderLastName($value)
    {
        return $this->setParameter('CardHolderLastName', $value);
    }

    public function getCardHolderLastName()
    {
        return $this->getParameter('CardHolderLastName');
    }

    /**
     * The supplied string must appear somewhere in the first name of the
     * associated card holder.
     */
    public function setCardHolderFirstName($value)
    {
        return $this->setParameter('CardHolderFirstName', $value);
    }

    public function getCardHolderFirstName()
    {
        return $this->getParameter('CardHolderFirstName');
    }

    /**
     * The card number must start with this string.
     */
    public function setCardNumberFirstSix($value)
    {
        return $this->setParameter('CardNbrFirstSix', $value);
    }

    public function getCardNumberFirstSix()
    {
        return $this->getParameter('CardNbrFirstSix');
    }

    /**
     * The card number must end with this string.
     */
    public function setCardNumberLastFour($value)
    {
        return $this->setParameter('CardNbrLastFour', $value);
    }

    public function getCardNumberLastFour()
    {
        return $this->getParameter('CardNbrLastFour');
    }

    /**
     * A specific invoice number; originally supplied in AdditionalTxnFields
     * or DirectMktInvoiceNbr in DirectMktData
     */
    public function setInvoiceNumber($value)
    {
        return $this->setParameter('InvoiceNbr', $value);
    }

    public function getInvoiceNumber()
    {
        return $this->getParameter('InvoiceNbr');
    }

    /**
     * A specific purchase order number; originally supplied in a CPCEdit
     */
    public function setPurchaseOrderNumber($value)
    {
        return $this->setParameter('CardHolderPONbr', $value);
    }

    public function getPurchaseOrderNumber()
    {
        return $this->getParameter('CardHolderPONbr');
    }

    /**
     * A specific customer id; originally supplied in AdditionalTxnFields
     */
    public function setCustomerReference($value)
    {
        return $this->setParameter('CustomerID', $value);
    }

    public function getCustomerReference()
    {
        return $this->getParameter('CustomerID');
    }

    /**
     * A list of transaction types to search for (i.e. CreditSale); see the
     * associated Type enumerations for specific values supported.
     *
     * Note: If not supplied, 'all' is assumed.
     */
    public function setServiceName($value)
    {
        return $this->setParameter('ServiceName', $value);
    }

    public function getServiceName()
    {
        return $this->getParameter('ServiceName');
    }

    /**
     * A list of payment methods to search for (i.e. Credit, Debit, etc.);
     * see the associated Type enumerations for specific values supported.
     *
     * Note: If not supplied, 'all' is assumed.
     */
    public function setPaymentType($value)
    {
        return $this->setParameter('PaymentType', $value);
    }

    public function getPaymentType()
    {
        return $this->getParameter('PaymentType');
    }

    /**
     * A list of card types to search for (i.e. VISA, MC, etc.); see the
     * associated Type enumerations for specific values supported.
     *
     * Note: If not supplied, 'all' is assumed.
     */
    public function setCardType($value)
    {
        return $this->setParameter('CardType', $value);
    }

    public function getCardType()
    {
        return $this->getParameter('CardType');
    }

    /**
     * A specific resulting status based on the issuer response:
     *
     * F - full approvals
     * P - partial approvals
     * A - all approvals (full and partial)
     * D - declines
     * FR - fraud declines
     * Note: If not sent then all will be included.
     */
    public function setIssuerResult($value)
    {
        return $this->setParameter('IssuerResult', $value);
    }

    public function getIssuerResult()
    {
        return $this->getParameter('IssuerResult');
    }

    /**
     * A specific settlement amount
     */
    public function setSettlementAmount($value)
    {
        return $this->setParameter('SettlementAmt', $value);
    }

    public function getSettlementAmount()
    {
        return $this->getParameter('SettlementAmt');
    }

    /**
     * A specific issuer transaction identifier
     */
    public function setIssuerTransactionId($value)
    {
        return $this->setParameter('IssTxnId', $value);
    }

    public function getIssuerTransactionId()
    {
        return $this->getParameter('IssTxnId');
    }

    /**
     * A specific retrieval reference number
     */
    public function setReferenceNumber($value)
    {
        return $this->setParameter('RefNbr', $value);
    }

    public function getReferenceNumber()
    {
        return $this->getParameter('RefNbr');
    }

    /**
     * A specific user name
     */
    public function setUserName($value)
    {
        return $this->setParameter('UserName', $value);
    }

    public function getUserName()
    {
        return $this->getParameter('UserName');
    }

    /**
     * A specific clerk id
     */
    public function setClerkId($value)
    {
        return $this->setParameter('ClerkID', $value);
    }

    public function getClerkId()
    {
        return $this->getParameter('ClerkID');
    }

    /**
     * A specific batch sequence number
     */
    public function setBatchSequenceNumber($value)
    {
        return $this->setParameter('BatchSeqNbr', $value);
    }

    public function getBatchSequenceNumber()
    {
        return $this->getParameter('BatchSeqNbr');
    }

    /**
     * A specific batch id
     */
    public function setBatchId($value)
    {
        return $this->setParameter('BatchId', $value);
    }

    public function getBatchId()
    {
        return $this->getParameter('BatchId');
    }

    /**
     * A specific site trace
     */
    public function setSiteTrace($value)
    {
        return $this->setParameter('SiteTrace', $value);
    }

    public function getSiteTrace()
    {
        return $this->getParameter('SiteTrace');
    }

    /**
     * A specific user display name
     *
     * Note: This field is for future use and is ignored.
     */
    public function setDisplayName($value)
    {
        return $this->setParameter('DisplayName', $value);
    }

    public function getDisplayName()
    {
        return $this->getParameter('DisplayName');
    }

    /**
     * A specific client-generated transaction id
     *
     * Note: Since this value should be unique, this should find at most
     * one match.
     */
    public function setClientTransactionId($value)
    {
        return $this->setParameter('ClientTxnId', $value);
    }

    public function getClientTransactionId()
    {
        return $this->getParameter('ClientTxnId');
    }

    /**
     * A specific client-generated device sub-identifier
     */
    public function setUniqueDeviceId($value)
    {
        return $this->setParameter('UniqueDeviceId', $value);
    }

    public function getUniqueDeviceId()
    {
        return $this->getParameter('UniqueDeviceId');
    }

    /**
     * The check account number must end with this string
     */
    public function setAccountNumberLastFour($value)
    {
        return $this->setParameter('AcctNbrLastFour', $value);
    }

    public function getAccountNumberLastFour()
    {
        return $this->getParameter('AcctNbrLastFour');
    }

    /**
     * A specific routing number
     */
    public function setBankRoutingNumber($value)
    {
        return $this->setParameter('BankRoutingNbr', $value);
    }

    public function getBankRoutingNumber()
    {
        return $this->getParameter('BankRoutingNbr');
    }

    /**
     * A specific check number
     */
    public function setCheckNumber($value)
    {
        return $this->setParameter('CheckNbr', $value);
    }

    public function getCheckNumber()
    {
        return $this->getParameter('CheckNbr');
    }

    /**
     * A specific first name on the check
     */
    public function setCheckFirstName($value)
    {
        return $this->setParameter('CheckFirstName', $value);
    }

    public function getCheckFirstName()
    {
        return $this->getParameter('CheckFirstName');
    }

    /**
     * A specific last name on the check
     */
    public function setCheckLastName($value)
    {
        return $this->setParameter('CheckLastName', $value);
    }

    public function getCheckLastName()
    {
        return $this->getParameter('CheckLastName');
    }

    /**
     * A specific business name on the check
     */
    public function setCheckName($value)
    {
        return $this->setParameter('CheckName', $value);
    }

    public function getCheckName()
    {
        return $this->getParameter('CheckName');
    }

    /**
     * A specific type of gift currency; see the associated Type
     * enumerations for specific values supported
     *
     * Note: If not supplied, 'all' is assumed.
     */
    public function setGiftCurrency($value)
    {
        return $this->setParameter('GiftCurrency', $value);
    }

    public function getGiftCurrency()
    {
        return $this->getParameter('GiftCurrency');
    }

    /**
     * A specific gift card alias
     */
    public function setGiftMaskedAlias($value)
    {
        return $this->setParameter('GiftMaskedAlias', $value);
    }

    public function getGiftMaskedAlias()
    {
        return $this->getParameter('GiftMaskedAlias');
    }

    /**
     * Search for only card-on-file, one-time payments; the
     * default is N.
     */
    public function setOneTime($value)
    {
        return $this->setParameter('OneTime', $value);
    }

    public function getOneTime()
    {
        return $this->getParameter('OneTime');
    }

    /**
     * A specific PayPlan payment method key
     */
    public function setPaymentMethodReference($value)
    {
        return $this->setParameter('PaymentMethodKey', $value);
    }

    public function getPaymentMethodReference()
    {
        return $this->getParameter('PaymentMethodKey');
    }

    /**
     * A specific schedule ID
     */
    public function setScheduleReference($value)
    {
        return $this->setParameter('ScheduleID', $value);
    }

    public function getScheduleReference()
    {
        return $this->getParameter('ScheduleID');
    }

    /**
     * A buyer's email address
     */
    public function setAlternativePaymentBuyerEmailAddress($value)
    {
        return $this->setParameter('BuyerEmailAddress', $value);
    }

    public function getAlternativePaymentBuyerEmailAddress()
    {
        return $this->getParameter('BuyerEmailAddress');
    }

    /**
     * A payment status of Altpayment transaction
     */
    public function setAlternativePaymentStatus($value)
    {
        return $this->setParameter('AltPaymentStatus', $value);
    }

    public function getAlternativePaymentStatus()
    {
        return $this->getParameter('AltPaymentStatus');
    }

    /**
     * Fully Captured Indicator for searching for Altpayment
     * Transactions; the default is null.
     */
    public function setFullyCapturedIndicator($value)
    {
        return $this->setParameter('FullyCapturedInd', $value);
    }

    public function getFullyCapturedIndicator()
    {
        return $this->getParameter('FullyCapturedInd');
    }
}
