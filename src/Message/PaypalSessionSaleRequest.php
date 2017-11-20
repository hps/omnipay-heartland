<?php

/**
 * Heartland Authorize Request.
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

/**
 * Heartland Paypal session Sale Request.
 *
 * This request is used to creates a unique Session for Electronic Commerce Alternate Payment Processing. 
 * This service must be called first to perform Alternate payment processing.
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
 * 	$buyer = array(
 * 		'returnUrl' => 'https://developer.heartlandpaymentsystems.com',
 * 		'cancelUrl' => 'https://developer.heartlandpaymentsystems.com',
 *      'payerId' => 'Your Paypal Payer Id'
 * 	);
 *
 * 	$payment = array(
 * 		'subtotal' => '10.00',
 * 		'shippingAmount' => '0',
 * 		'taxAmount' => '0',
 * 		'paymentType' => 'Sale'
 * 	);
 *
 * 	$lineItems = array();
 * 
 * 	$lineItem = array(
 * 		'number' => '1',
 * 		'quantity' => '1',
 * 		'name' => 'Name with special',
 * 		'description' => 'Description with special',
 * 		'amount' => '10.00'
 * 	);
 * 
 *   $lineItem1 = array(
 * 		'number' => '1',
 * 		'quantity' => '1',
 * 		'name' => 'Name with special',
 * 		'description' => 'Description with special',
 * 		'amount' => '10.00'
 * 	);
 *
 * 	$lineItems[] = $lineItem;
 *  $lineItems[] = $lineItem1;
 *
 * 	$request = $gateway->createPaypalSession(array(
 *      'paypalSessionId' => 'Your Paypal Session Id',
 * 		'amount' => $payment['subtotal'] + $payment['shippingAmount'] + $payment['taxAmount'],
 * 		'buyerDetails' => $buyer,
 * 		'shippingDetails' => $payment,
 * 		'itemDetails' => $lineItems
 * 	));
 *
 *   $response = $transaction->send();
 * 
 *   if ($response->isSuccessful()) {
 *       echo "Paypal sale completed  successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *   }
 * </code>
 *
 * @see  \Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-AltPaymentCreateSession.html
 * @codingStandardsIgnoreEnd
 */
class PaypalSessionSaleRequest extends CreatePaypalSessionRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'AltPaymentSale';
    }
    
    public function getPaypalSessionId()
    {
        return $this->getParameter('paypalSessionId');
    }

    public function setPaypalSessionId($value)
    {
        return $this->setParameter('paypalSessionId', $value);
    }   
    
}
