<?php

/**
 * Heartland Authorize Request.
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

/**
 * Heartland Authorize Request.
 *
 * An Authorize request is similar to a purchase request but the
 * charge issues an authorization (or pre-authorization), and no money
 * is transferred.  The transaction will need to be captured later
 * in order to effect payment. Uncaptured charges expire in 7 days.
 *
 * Either a payment token or a card is required.  Token is like the ones returned by
 * securesubmit.js, or a dictionary containing a user's credit card details.
 * Either token / cardReference parameters can be used to pass the token
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the Heartland Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('Heartland');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'secretApiKey' => 'MySecretApiKey',
 *       'siteId' => 'MysiteId',
 *       'deviceId' => 'MydeviceId',
 *       'licenseId' => 'MyLicenseId',
 *       'username' => 'MyUsername',
 *       'password' => 'MyPassword',
 *       'developerId' => 'developerId',
 *       'versionNumber' => 'versionNumber',
 *       'siteTrace' => 'siteTrace'
 *
 *   ));
 *
 *   // Prepare the transaction
 *   $transaction = $gateway->recurring(array(
 *       'amount'            => '10.00',
 *       'currency'          => 'USD',
 *       'description'       => 'This is a test authorize transaction.', // optional
 *       'cardReference'     => 'payment-method-key',
 *       'scheduleReference' => 'schedule-key', // optional
 *       'oneTime'           => true, // default is false
 *   ));
 *
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Authorize transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *   }
 * </code>
 *
 * @see  \Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2-c.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-RecurringBilling.html
 * @codingStandardsIgnoreEnd
 */
class RecurringBillingRequest extends RecurringBillingAuthRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'RecurringBilling';
    }
}
