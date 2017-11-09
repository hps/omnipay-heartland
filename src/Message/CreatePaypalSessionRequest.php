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
 *	$buyer = array(
*		'returnUrl' => 'https://developer.heartlandpaymentsystems.com',
*		'cancelUrl' => 'https://developer.heartlandpaymentsystems.com'
*	);
*
*	$payment = array(
*		'subtotal' => '10.00',
*		'shippingAmount' => '0',
*		'taxAmount' => '0',
*		'paymentType' => 'Sale'
*	);
*
*	$lineItems = array();
* 
*	$lineItem = array(
*		'number' => '1',
*		'quantity' => '1',
*		'name' => 'Name with special',
*		'description' => 'Description with special',
*		'amount' => '10.00'
*	);
* 
*   $lineItem1 = array(
*		'number' => '1',
*		'quantity' => '1',
*		'name' => 'Name with special',
*		'description' => 'Description with special',
*		'amount' => '10.00'
*	);
*
*	$lineItems[] = $lineItem;
*   $lineItems[] = $lineItem1;
*
*	$request = $gateway->createPaypalSession(array(
*		'amount' => $payment['subtotal'] + $payment['shippingAmount'] + $payment['taxAmount'],
*		'buyerDetails' => $buyer,
*		'shippingDetails' => $payment,
*		'itemDetails' => $lineItems
*	));
 *
 *   $response = $transaction->send();
 * 
 *   if ($response->isSuccessful()) {
 *       echo "Paypal session creation  successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *   }
 * </code>
 *
 * @see  \Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-CreditAuth.html
 * @codingStandardsIgnoreEnd
 */
class CreatePaypalSessionRequest extends AbstractPorticoRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'AltPaymentCreateSession';
    }
    
    /**
     * @return string
     */
    public function getAltPaymentTransactionType()
    {
        return 'PAYPAL';
    }
    
    public function getData()
    {
        parent::getData();

        $amount = HpsInputValidation::checkAmount($this->getAmount());
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $createSession = $xml->createElement('hps:' . $this->getTransactionType());
        
        $createSession->appendChild($xml->createElement('hps:TransactionType', $this->getAltPaymentTransactionType()));
        $createSession->appendChild($this->hydrateBuyerData($xml));
        $createSession->appendChild($xml->createElement('hps:Amt', $amount));
        $createSession->appendChild($this->hydratePaymentData($xml));
        if ($shippingAddress != null) {
            $createSession->appendChild($this->hydrateShippingData($xml));
        }
        if ($lineItems != null) {
            $createSession->appendChild($this->hydrateLineItems($xml));
        }

        $hpsTransaction->appendChild($createSession);

        return $hpsTransaction;
    }

    /**
     * @param \DOMDocument  $xml
     *
     * @return \DOMElement
     */
    private function hydrateBuyerData(DOMDocument $xml)
    {
        $buyer = (object) $this->getBuyerDetails();
        $data = $xml->createElement('hps:Buyer');
        
        if (isset($buyer->returnUrl)) {
            $data->appendChild($this->hydrateNameValuePair('ReturnUrl', $buyer->returnUrl, $xml));
        }
        if (isset($buyer->cancelUrl)) {
            $data->appendChild($this->hydrateNameValuePair('CancelUrl', $buyer->cancelUrl, $xml));
        }
        if (isset($buyer->emailAddress)) {
            $data->appendChild($this->hydrateNameValuePair('EmailAddress', $buyer->emailAddress, $xml));
        }
        if (isset($buyer->payerId)) {
            $data->appendChild($this->hydrateNameValuePair('BuyerId', $buyer->payerId, $xml));
        }
        if (isset($buyer->credit) && $buyer->credit != false) {
            $data->appendChild($this->hydrateNameValuePair('FundingSource', 'credit', $xml));
        }
        return $data;
    }
    /**
     * @param \DOMDocument $xml
     *
     * @return \DOMElement
     */
    private function hydrateLineItems(DOMDocument $xml)
    {
        $items = $this->getItemDetails();
        $lineItems = $xml->createElement('hps:LineItem');

        foreach ($items as $item) {
            $item = (object) $item;

            $detail = $xml->createElement('hps:Detail');
            if (isset($item->name)) {
                $detail->appendChild($this->hydrateNameValuePair('Name', $item->name, $xml));
            }
            if (isset($item->description)) {
                $detail->appendChild($this->hydrateNameValuePair('Description', $item->description, $xml));
            }
            if (isset($item->number)) {
                $detail->appendChild($this->hydrateNameValuePair('Number', $item->number, $xml));
            }
            if (isset($item->amount)) {
                $detail->appendChild($this->hydrateNameValuePair('Amount', $item->amount, $xml));
            }
            if (isset($item->quantity)) {
                $detail->appendChild($this->hydrateNameValuePair('Quantity', $item->quantity, $xml));
            }
            if (isset($item->taxAmount)) {
                $detail->appendChild($this->hydrateNameValuePair('TaxAmount', $item->taxAmount, $xml));
            }
            $lineItems->appendChild($detail);
        }
        return $lineItems;
    }
    /**
     * @param              $name
     * @param              $value
     * @param \DOMDocument $xml
     *
     * @return \DOMElement
     */
    private function hydrateNameValuePair($name, $value, DOMDocument $xml)
    {
        $nvp = $xml->createElement('hps:NameValuePair');
        $nvp->appendChild($xml->createElement('hps:Name', $name));
        $nvp->appendChild($xml->createElement('hps:Value', HpsInputValidation::cleanAscii($value)));
        return $nvp;
    }
    /**
     * @param \DOMDocument    $xml
     *
     * @return \DOMElement
     */
    private function hydratePaymentData(DOMDocument $xml)
    {
        $payment = (object) $this->getPaymentDetails();
        
        $data = $xml->createElement('hps:Payment');
        $data->appendChild($this->hydrateNameValuePair('ItemAmount', $payment->subtotal, $xml));
        if (isset($payment->shippingAmount)) {
            $data->appendChild($this->hydrateNameValuePair('ShippingAmount', $payment->shippingAmount, $xml));
        }
        if (isset($payment->taxAmount)) {
            $data->appendChild($this->hydrateNameValuePair('TaxAmount', $payment->taxAmount, $xml));
        }
        if (isset($payment->paymentType)) {
            $data->appendChild($this->hydrateNameValuePair('PaymentType', $payment->paymentType, $xml));
        }
        if (isset($payment->invoiceNumber)) {
            $data->appendChild($this->hydrateNameValuePair('InvoiceNbr', $payment->invoiceNumber, $xml));
        }
        return $data;
    }
    /**
     * @param \HpsShippingInfo $info
     * @param \DOMDocument     $xml
     *
     * @return \DOMElement
     */
    private function hydrateShippingData(DOMDocument $xml)
    {
        $info = (object) $this->getShippingDetails();
        
        $shipping = $xml->createElement('hps:Shipping');
        $address = $xml->createElement('hps:Address');
        $address->appendChild($this->hydrateNameValuePair('AllowAddressOverride', 'false', $xml));
        $address->appendChild($this->hydrateNameValuePair('ShipName', $info->name, $xml));
        $address->appendChild($this->hydrateNameValuePair('ShipAddress', $info->address->address, $xml));
        $address->appendChild($this->hydrateNameValuePair('ShipCity', $info->address->city, $xml));
        $address->appendChild($this->hydrateNameValuePair('ShipState', $info->address->state, $xml));
        $address->appendChild($this->hydrateNameValuePair('ShipZip', $info->address->zip, $xml));
        $address->appendChild($this->hydrateNameValuePair('ShipCountryCode', $info->address->country, $xml));
        $shipping->appendChild($address);
        return $shipping;
    }
    
    public function getBuyerDetails()
    {
        return $this->getParameter('buyerDetails');
    }

    public function setBuyerDetails($value)
    {
        return $this->setParameter('buyerDetails', $value);
    }
    
    public function getShippingDetails()
    {
        return $this->getParameter('shippingDetails');
    }
    
    public function setShippingDetails($value)
    {
        return $this->setParameter('shippingDetails', $value);
    }
    
    public function setItemDetails($value)
    {
        return $this->setParameter('itemDetails', $value);
    }
    
    public function getItemDetails($value)
    {
        return $this->getParameter('itemDetails', $value);
    }
    
    public function getPaymentDetails()
    {
        return $this->getParameter('paymentDetails');
    }

    public function setPaymentDetails($value)
    {
        return $this->setParameter('paymentDetails', $value);
    }   
    
    
}
