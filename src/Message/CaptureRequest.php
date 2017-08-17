<?php

/**
 *  Heartland Capture Request.
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
 * Heartland Capture Request.
 *
 * Use this request to capture and process a previously created authorization.
 *
 * Example -- note this example assumes that the authorization has been successful
 * and that the authorization ID returned from the authorization is held in $auth_id.
 * See AuthorizeRequest for the first part of this example transaction:
 *
 * <code>
 *   // Once the transaction has been authorized, we can capture it for final payment.
 *   $transaction = $gateway->capture(array(
 *       'amount'        => '10.00',
 *       'currency'      => 'USD'
 *   ));
 *   $transaction->setTransactionReference($auth_id);

 *   $response = $transaction->send();
 * </code>
 *
 * @see  CaptureRequest
 * @codingStandardsIgnoreStart
 * @link https://cert.api2.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-CreditAddToBatch.html
 * @codingStandardsIgnoreStart
 */
class CaptureRequest extends AbstractPorticoRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'CreditAddToBatch';
    }

    public function getData()
    {
        parent::getData();
        $this->validate('transactionReference');
        $amount = HpsInputValidation::checkAmount($this->getAmount());

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsCreditAddToBatch = $xml->createElement('hps:' . $this->getTransactionType());

        $hpsCreditAddToBatch->appendChild($xml->createElement('hps:GatewayTxnId', $this->getTransactionReference()));
        if ($amount != null) {
            $amount = sprintf("%0.2f", round($amount, 3));
            $hpsCreditAddToBatch->appendChild($xml->createElement('hps:Amt', $amount));
        }
        $hpsCreditAddToBatch->appendChild($this->hydrateDirectMarketData($xml));

        $hpsTransaction->appendChild($hpsCreditAddToBatch);

        return $hpsTransaction;
    }
}
