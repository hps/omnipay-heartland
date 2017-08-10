<?php

/**
 *  Heartland Purchase Request.
 *
 * @category    HPS
 * @package     Omnipay_Heartland
 * @author      Heartland Developer Portal <EntApp_DevPortal@e-hps.com>
 * @copyright   Heartland (http://heartland.us)
 * @license     https://github.com/hps/omnipay-heartland/blob/master/LICENSE.md
 */

namespace Omnipay\Heartland\Message;

/**
 * Heartland Purchase Request.
 *
 * To charge a credit card, you create a new charge object. If your API key
 * is in test mode, the supplied card won't actually be charged, though
 * everything else will occur as if in live mode. (Heartland assumes that the
 * charge would have completed successfully).
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
 *   // By using card details
 *
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   $card = new CreditCard(array(
 *      'firstName'    => 'Example',
 *      'lastName'     => 'Customer',
 *      'number'       => '4242424242424242',
 *      'expiryMonth'  => '01',
 *      'expiryYear'   => '2020',
 *      'cvv'          => '123',
 *      'email'                 => 'customer@example.com',
 *      'billingAddress1'       => '1 Scrubby Creek Road',
 *      'billingCountry'        => 'AU',
 *      'billingCity'           => 'Scrubby Creek',
 *      'billingPostcode'       => '4999',
 *      'billingState'          => 'QLD',
 *   ));
 *
 *   // Do a purchase transaction on the gateway
 *   $transaction = $gateway->purchase(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'description'              => 'This is a test purchase transaction.',
 *       'card'                     => $card,
 *   ));
 *
 *   //By using token details
 *   $transaction = $gateway->authorize(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'description'              => 'This is a test authorize transaction.'
 *   ));
 *
 *   $transaction->setToken('abc-123');
 *
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Purchase transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *   }
 * </code>
 *
 * Because a purchase request in Heartland looks similar to an
 * Authorize request, this class simply extends the AuthorizeRequest
 * class and over-rides the getData method setting capture = true.
 *
 * @see  \Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-CreditSale.html
 * @codingStandardsIgnoreEnd
 * @package Omnipay\Heartland\Message
 */
class PurchaseRequest extends AuthorizeRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'CreditSale';
    }
}
