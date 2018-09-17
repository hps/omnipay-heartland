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
 *   // Do an authorize transaction on the gateway
 *   $transaction = $gateway->authorize(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'description'              => 'This is a test authorize transaction.',
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
 *       echo "Authorize transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *   }
 * </code>
 *
 * @see  \Omnipay\Heartland\Gateway
 * @codingStandardsIgnoreStart
 * @link https://cert.api2-c.heartlandportico.com/Gateway/PorticoSOAPSchema/build/Default/webframe.html#Portico_xsd~e-PosRequest~e-Ver1.0~e-Transaction~e-CreditAuth.html
 * @codingStandardsIgnoreEnd
 */
class AuthorizeRequest extends AbstractPorticoRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'CreditAuth';
    }

    public function getData()
    {
        parent::getData();

        $amount = HpsInputValidation::checkAmount($this->getAmount());
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsCreditAuth = $xml->createElement('hps:' . $this->getTransactionType());
        $hpsBlock1 = $xml->createElement('hps:Block1');

        $hpsBlock1->appendChild($xml->createElement('hps:AllowDup', 'Y'));
        //$hpsBlock1->appendChild($xml->createElement('hps:AllowPartialAuth', ($allowPartialAuth ? 'Y' : 'N')));
        $hpsBlock1->appendChild($xml->createElement('hps:Amt', $amount));

        if ($this->getPurchaseCardRequest()) {
            $hpsBlock1->appendChild($xml->createElement(
                'hps:CPCReq',
                $this->getPurchaseCardRequest() === true ? 'Y' : 'N'
            ));
        }

        $hpsBlock1->appendChild($this->hydrateCardHolderData($xml));

        if ($this->getTransactionId()) {
            $hpsBlock1->appendChild($this->hydrateAdditionalTxnFields($xml));
        }
        if ($this->getDescription()) {
            $hpsBlock1->appendChild($xml->createElement('hps:TxnDescriptor', $this->getDescription()));
        }

        $cardData = $xml->createElement('hps:CardData');

        if ($this->getToken()) {
            $cardData->appendChild($this->hydrateTokenData($xml));
        } else {
            $cardData->appendChild($this->hydrateManualEntry($xml));
        }

        if ($this->getRequestCardReference()) {
            $cardData->appendChild($xml->createElement(
                'hps:TokenRequest',
                $this->getRequestCardReference() === true ? 'Y' : 'N'
            ));
        }

        $hpsBlock1->appendChild($cardData);

        if ($this->getEcommerceInfo()) {
            $info = $this->getEcommerceInfo();
            $mktData = $xml->createElement('hps:DirectMktData');
            $mktData->appendChild($xml->createElement(
                'hps:DirectMktInvoiceNbr',
                isset($info['invoiceNumber']) ? $info['invoiceNumber'] : ''
            ));

            $tomorrow = (new \DateTime())->add(new \DateInterval('P1D'));
            $mktData->appendChild($xml->createElement(
                'hps:DirectMktShipDay',
                isset($info['shipDay']) ? $info['shipDay'] : $tomorrow->format('d')
            ));
            $mktData->appendChild($xml->createElement(
                'hps:DirectMktShipMonth',
                isset($info['shipMonth']) ? $info['shipMonth'] : $tomorrow->format('m')
            ));

            $hpsBlock1->appendChild($mktData);
        }


        $hpsCreditAuth->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditAuth);

        return $hpsTransaction;
    }

    public function handleResponse($response)
    {
        if ($this->getPurchaseCardRequest() && $response->getPurchaseCardIndicator()) {
            $cpcEdit = new PurchaseCardEditRequest($this->httpClient, $this->httpRequest);
            foreach ($this->getParameters() as $key => $value) {
                $cpcEdit->setParameter($key, $value);
            }
            $cpcEdit->setTransactionReference($response->getTransactionReference());
            $response->setPurchaseCardResponse($cpcEdit->send());
        }
        return $response;
    }

    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }

    public function setPaymentMethodReference($value)
    {
        return $this->setParameter('paymentMethodKey', $value);
    }

    public function getPaymentMethodReference()
    {
        return $this->getParameter('paymentMethodKey');
    }

    public function getPurchaseCardRequest()
    {
        return $this->getCardHolderPONumber()
            || $this->getTaxAmount()
            || $this->getTaxType();
    }

    public function getCardHolderPONumber()
    {
        return $this->getParameter('cardHolderPONumber');
    }

    public function setCardHolderPONumber($value)
    {
        return $this->setParameter('cardHolderPONumber', $value);
    }

    public function getTaxAmount()
    {
        return $this->getParameter('taxAmount');
    }

    public function setTaxAmount($value)
    {
        return $this->setParameter('taxAmount', $value);
    }

    public function getTaxType()
    {
        return $this->getParameter('taxType');
    }

    public function setTaxType($value)
    {
        return $this->setParameter('taxType', $value);
    }

    public function getRequestCardReference()
    {
        return $this->getParameter('requestCardReference');
    }

    public function setRequestCardReference($value)
    {
        return $this->setParameter('requestCardReference', $value);
    }

    public function getEcommerceInfo()
    {
        return $this->getParameter('ecommerceInfo');
    }

    public function setEcommerceInfo($value)
    {
        return $this->setParameter('ecommerceInfo', $value);
    }
}
