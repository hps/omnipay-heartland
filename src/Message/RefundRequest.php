<?php

/**
 *  Heartland Refund Request.
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
 * Heartland Refund Request.
 *
 * When you create a new refund, you must specify a
 * charge to create it on.
 *
 * Creating a new refund will refund a charge that has
 * previously been created but not yet refunded. Funds will
 * be refunded to the credit or debit card that was originally
 * charged. The fees you were originally charged are also
 * refunded.
 *
 * You can optionally refund only part of a charge. You can
 * do so as many times as you wish until the entire charge
 * has been refunded.
 *
 * Once entirely refunded, a charge can't be refunded again.
 * This method will return an error when called on an
 * already-refunded charge, or when trying to refund more
 * money than is left on a charge.
 *
 * Example -- note this example assumes that the purchase has been successful
 * and that the transaction ID returned from the purchase is held in $sale_id.
 * See PurchaseRequest for the first part of this example transaction:
 *
 * <code>
 *   // Do a refund transaction on the gateway
 *   $transaction = $gateway->refund(array(
 *       'amount'                   => '10.00',
 *       'transactionReference'     => $sale_id,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Refund transaction was successful!\n";
 *       $refund_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $refund_id . "\n";
 *   }
 * </code>
 *
 * @see PurchaseRequest
 * @see \Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2-c.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-CreditReturn.html
 * @codingStandardsIgnoreEnd
 */
class RefundRequest extends AbstractPorticoRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'CreditReturn';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('amount');

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsCreditReturn = $xml->createElement('hps:' . $this->getTransactionType());
        $hpsBlock1 = $xml->createElement('hps:Block1');

        $hpsBlock1->appendChild($xml->createElement('hps:AllowDup', 'Y'));
        $hpsBlock1->appendChild($xml->createElement('hps:Amt', HpsInputValidation::checkAmount($this->getAmount())));

        if ($this->getTransactionReference() !== null) {
            $hpsBlock1->appendChild($xml->createElement('hps:GatewayTxnId', $this->getTransactionReference()));
        } else {
            $cardData = $xml->createElement('hps:CardData');
            $tokenRef = ($this->getToken() !== null) ? $this->getToken() : $this->getCardReference();
            if ($tokenRef !== null) {
                $cardData->appendChild($this->hydrateTokenData($xml));
            } else {
                $this->validate('card');
                $cardData->appendChild($this->hydrateManualEntry($xml));
            }
            $hpsBlock1->appendChild($cardData);
        }

        if ($this->getCard() !== null) {
            $hpsBlock1->appendChild($this->hydrateCardHolderData($xml));
        }
        if ($this->getTransactionId() !== null) {
            $hpsBlock1->appendChild($this->hydrateAdditionalTxnFields($xml));
        }

        $hpsCreditReturn->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditReturn);

        return $hpsTransaction;
    }
}
