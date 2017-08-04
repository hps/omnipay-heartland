<?php

/**
 *  Heartland Void Request.
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
 * Heartland Void Request.
 *
 * CreditVoid is used to cancel an open auth or remove a transaction from the current open batch. 
 * The original transaction must be a CreditAuth, CreditSale, CreditReturn, RecurringBilling, RecurringBillingAuth.
 * 
 * Note: Once a batch is closed, associated transactions can no longer be voided. 
 *       In these cases, a CreditReturn can be used to adjust a customer's account.
 * 
 * Note: If a transaction has been fully or partially returned, it cannot be voided.
 *  
 * Example -- note this example assumes that the purchase has been successful
 * and that the transaction ID returned from the purchase is held in $sale_id.
 * See PurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Do a void transaction on the gateway
 *   $transaction = $gateway->void(array(
 *       'transactionReference' => $sale_id,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Void transaction was successful!\n";
 *       $void_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $void_id . "\n";
 *   }
 * </code>
 *
 * @see RefundRequest
 * @see Omnipay\Heartland\Gateway
 * @link https://cert.api2.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-CreditVoid.html
 */
class VoidRequest extends AbstractRequest {

    /**
     * @return string
     */
    public function getTransactionType() {
        return 'CreditVoid';
    }

    public function getData() {
        parent::getData();
        $this->validate('transactionReference');

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsCreditVoid = $xml->createElement('hps:' . $this->getTransactionType());
        $hpsCreditVoid->appendChild($xml->createElement('hps:GatewayTxnId', $this->getTransactionReference()));
        $hpsTransaction->appendChild($hpsCreditVoid);

        return $hpsTransaction;
    }

}
