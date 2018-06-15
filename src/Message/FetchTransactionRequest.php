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
 * Heartland Fetch Transaction Request.
 *
 * Example -- note this example assumes that the purchase has been successful
 * and that the transaction ID returned from the purchase is held in $sale_id.
 * See PurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Fetch the transaction so that details can be found for refund, etc.
 *   $transaction = $gateway->fetchTransaction();
 *   $transaction->setTransactionReference($sale_id);
 *   $response = $transaction->send();
 *   $data = $response->getData();
 *   echo "Gateway fetchTransaction response data == " . print_r($data, true) . "\n";
 * </code>
 *
 * @see  PurchaseRequest
 * @see  Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2-c.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-ReportTxnDetail.html
 * @codingStandardsIgnoreEnd
 */
class FetchTransactionRequest extends AbstractPorticoRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'ReportTxnDetail';
    }

    public function getData()
    {
        $this->validate('transactionReference');

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsReportTxnDetail = $xml->createElement('hps:' . $this->getTransactionType());
        $hpsReportTxnDetail->appendChild($xml->createElement('hps:TxnId', $this->getTransactionReference()));
        $hpsTransaction->appendChild($hpsReportTxnDetail);

        return $hpsTransaction;
    }
}
