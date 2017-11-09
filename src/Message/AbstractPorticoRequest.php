<?php

/**
 *  Heartland Abstract Portico Request.
 *
 * @category    HPS
 * @package     Omnipay_Heartland
 * @author      Heartland Developer Portal <EntApp_DevPortal@e-hps.com>
 * @copyright   Heartland (http://heartland.us)
 * @license     https://github.com/hps/omnipay-heartland/blob/master/LICENSE.md
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

abstract class AbstractPorticoRequest extends AbstractRequest
{
    protected $responseType = '\Omnipay\Heartland\Message\PorticoResponse';

    // region Heartland Soap Building

    /**
     * Get the card data.
     *
     * Because the heartland gateway uses a common format for passing
     * card data to the API, this function can be called to get the
     * data from the associated card object in the format that the
     * API requires.
     *
     * @return array
     */
    protected function getCardData()
    {
        $card = $this->getCard();
        $card->validate();

        $data = array();
        $data['object'] = 'card';
        $data['number'] = $card->getNumber();
        $data['exp_month'] = $card->getExpiryMonth();
        $data['exp_year'] = $card->getExpiryYear();
        if ($card->getCvv()) {
            $data['cvc'] = $card->getCvv();
        }
        $data['name'] = $card->getName();
        $data['address_line1'] = $card->getAddress1();
        $data['address_line2'] = $card->getAddress2();
        $data['address_city'] = $card->getCity();
        $data['address_zip'] = $card->getPostcode();
        $data['address_state'] = $card->getState();
        $data['address_country'] = $card->getCountry();
        $data['email'] = $card->getEmail();

        return $data;
    }

    // region Heartland Soap Building
    public function getData()
    {
        $cardNotRequired = array(
            'CreditAddToBatch',
            'CreditVoid',
            'CreditReturn',
            'CreditReversal',
            'CreditCPCEdit',
            'AltPaymentCreateSession',
            'AltPaymentSale'
        );
        if (in_array($this->getTransactionType(), $cardNotRequired) === false) {
            //check the token value in card reference
            if ($this->getToken() == null && $this->getCardReference() != null) {
                $cardReference = trim($this->getCardReference());
                if (!empty($cardReference)) {
                    $this->setToken($cardReference);
                }
            }
            //if token not passed validate card
            $this->validate('amount');
            if (method_exists($this, 'getPaymentMethodReference') && $this->getPaymentMethodReference() != null) {
                $this->validate('paymentMethodKey');
            } elseif ($this->getToken() == null) {
                $this->validate('card');
                $this->getCard()->validate();
            } else {
                $this->validate('token');
            }
        }
    }

    public function sendData($data)
    {
        $xml = new DOMDocument('1.0', 'utf-8');
        $soapEnvelope = $xml->createElement('soapenv:Envelope');
        $soapEnvelope->setAttribute('xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $soapEnvelope->setAttribute('xmlns:hps', 'http://Hps.Exchange.PosGateway');

        $soapBody = $xml->createElement('soapenv:Body');
        $hpsRequest = $xml->createElement('hps:PosRequest');

        $hpsVersion = $xml->createElement('hps:Ver1.0');
        $hpsHeader = $xml->createElement('hps:Header');

        if ($this->getSecretApiKey() != null && $this->getSecretApiKey() != "") {
            $hpsHeader->appendChild($xml->createElement('hps:SecretAPIKey', trim($this->getSecretApiKey())));
        } else {
            $hpsHeader->appendChild($xml->createElement('hps:SiteId', $this->getSiteId()));
            $hpsHeader->appendChild($xml->createElement('hps:DeviceId', $this->getDeviceId()));
            $hpsHeader->appendChild($xml->createElement('hps:LicenseId', $this->getLicenseId()));
            $hpsHeader->appendChild($xml->createElement('hps:UserName', $this->getUsername()));
            $hpsHeader->appendChild($xml->createElement('hps:Password', $this->getPassword()));
        }
        if ($this->getDeveloperId() != null && $this->getDeveloperId() != "") {
            $hpsHeader->appendChild($xml->createElement('hps:DeveloperID', $this->getDeveloperId()));
            $hpsHeader->appendChild($xml->createElement('hps:VersionNbr', $this->getVersionNumber()));
            $hpsHeader->appendChild($xml->createElement('hps:SiteTrace', $this->getSiteTrace()));
        }
        if ($this->getTransactionHistoryId() !== null) {
            $hpsHeader->appendChild($xml->createElement('hps:ClientTxnId', $this->getTransactionHistoryId()));
        }

        $hpsVersion->appendChild($hpsHeader);
        $transaction = $xml->importNode($data, true);
        $hpsVersion->appendChild($transaction);

        $hpsRequest->appendChild($hpsVersion);
        $soapBody->appendChild($hpsRequest);
        $soapEnvelope->appendChild($soapBody);
        $xml->appendChild($soapEnvelope);


        $soapXML = $xml->saveXML();
        //print $soapXML;die;

        return $this->submitRequest(array(
            'body' => $soapXML,
            'headers' => array(
                'Content-type' => 'text/xml; charset="UTF-8"',
                'Accept' => 'text/xml',
                'SOAPAction' => '""',
            ),
        ));
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->getSecretApiKey() != null && $this->getSecretApiKey() != "") {
            if (strpos($this->getSecretApiKey(), '_cert_') !== false) {
                $this->setParameter('testMode', true);
                return "https://cert.api2.heartlandportico.com/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            } elseif (strpos($this->getSecretApiKey(), '_uat_') !== false) {
                $this->setParameter('testMode', true);
                return "https://posgateway.uat.secureexchange.net/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            } else {
                $this->setParameter('testMode', false);
                return "https://api2.heartlandportico.com/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            }
        } else {
            return $this->getServiceUri();
        }
    }

    /**
     * @return \stdClass
     */
    protected function hydrateCardHolderData(DOMDocument $xml)
    {
        //@var \Omnipay\Common\CreditCard $creditCard
        $creditCard = $this->getCard();

        $cardHolderData = $xml->createElement('hps:CardHolderData');

        if (!is_null($creditCard)) {
            $address = '';
            if (!is_null($creditCard->getBillingAddress1())) {
                $address .= $creditCard->getBillingAddress1();
            }

            if (!is_null($creditCard->getBillingAddress2())) {
                $address .= ' ' . $creditCard->getBillingAddress2();
            }

            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderFirstName',
                    HpsInputValidation::checkCardHolderData($creditCard->getBillingFirstName(), 'FirstName')
                )
            );
            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderLastName',
                    HpsInputValidation::checkCardHolderData($creditCard->getBillingLastName(), 'LastName')
                )
            );
            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderEmail',
                    HpsInputValidation::checkEmailAddress($creditCard->getEmail())
                )
            );
            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderPhone',
                    HpsInputValidation::checkPhoneNumber($creditCard->getBillingPhone())
                )
            );
            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderAddr',
                    HpsInputValidation::checkCardHolderData($address)
                )
            );
            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderCity',
                    HpsInputValidation::checkCardHolderData($creditCard->getBillingCity(), 'City')
                )
            );
            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderState',
                    HpsInputValidation::checkCardHolderData($creditCard->getBillingState(), 'State')
                )
            );
            $cardHolderData->appendChild(
                $xml->createElement(
                    'hps:CardHolderZip',
                    HpsInputValidation::checkZipCode($creditCard->getBillingPostcode())
                )
            );
        }
        return $cardHolderData;
    }

    /**
     * @param DOMDocument $xml
     * @param bool         $cardPresent
     * @param bool         $readerPresent
     *
     * @return \DOMElement
     */
    public function hydrateTokenData(DOMDocument $xml, $cardPresent = false, $readerPresent = false)
    {
        $token = ($this->getToken() !== null) ? $this->getToken() : $this->getCardReference();

        $tokenData = $xml->createElement('hps:TokenData');
        $tokenData->appendChild($xml->createElement('hps:TokenValue', $token));

        /*
          if (isset($token->expMonth)) {
          $tokenData->appendChild($xml->createElement('hps:ExpMonth', $token->expMonth));
          }

          if (isset($token->expYear)) {
          $tokenData->appendChild($xml->createElement('hps:ExpYear', $token->expYear));
          }

          if (isset($token->cvv)) {
          $tokenData->appendChild($xml->createElement('hps:CVV2', $token->cvv));
          }
         */
        $tokenData->appendChild($xml->createElement('hps:CardPresent', ($cardPresent ? 'Y' : 'N')));
        $tokenData->appendChild($xml->createElement('hps:ReaderPresent', ($readerPresent ? 'Y' : 'N')));

        return $tokenData;
    }

    /**
     * @param DOMDocument $xml
     * @param bool         $cardPresent
     * @param bool         $readerPresent
     *
     * @return \DOMElement
     */
    public function hydrateManualEntry(DOMDocument $xml, $cardPresent = false, $readerPresent = false)
    {
        $card = $this->getCard();
        $manualEntry = $xml->createElement('hps:ManualEntry');

        if ($card->getNumber()) {
            $manualEntry->appendChild($xml->createElement('hps:CardNbr', $card->getNumber()));
        }

        if ($card->getExpiryMonth()) {
            $manualEntry->appendChild($xml->createElement('hps:ExpMonth', $card->getExpiryMonth()));
        }

        if ($card->getExpiryYear()) {
            $manualEntry->appendChild($xml->createElement('hps:ExpYear', $card->getExpiryYear()));
        }

        if ($card->getCvv()) {
            $manualEntry->appendChild($xml->createElement('hps:CVV2', $card->getCvv()));
        }

        $manualEntry->appendChild($xml->createElement('hps:CardPresent', ($cardPresent ? 'Y' : 'N')));
        $manualEntry->appendChild($xml->createElement('hps:ReaderPresent', ($readerPresent ? 'Y' : 'N')));

        return $manualEntry;
    }

    /**
     * @param DOMDocument $xml
     *
     * @return \DOMElement
     */
    public function hydrateAdditionalTxnFields(DOMDocument $xml)
    {
        $additionalTxnFields = $xml->createElement('hps:AdditionalTxnFields');

        $additionalTxnFields->appendChild($xml->createElement('hps:Description', $this->getDescription()));
        $additionalTxnFields->appendChild($xml->createElement('hps:InvoiceNbr', $this->getTransactionId()));

        if ($this->getCustomerReference() !== null) {
            $additionalTxnFields->appendChild($xml->createElement('hps:CustomerID', $this->getCustomerReference()));
        }

        return $additionalTxnFields;
    }

    /**
     * @param DOMDocument $xml
     *
     * @return \DOMElement
     */
    public function hydrateDirectMarketData(DOMDocument $xml)
    {
        date_default_timezone_set("UTC");
        $current = new \DateTime();

        $directMktDataElement = $xml->createElement('hps:DirectMktData');
        $directMktDataElement->appendChild($xml->createElement('hps:DirectMktInvoiceNbr', $this->getTransactionId()));
        $directMktDataElement->appendChild($xml->createElement('hps:DirectMktShipMonth', $current->format('m')));
        $directMktDataElement->appendChild($xml->createElement('hps:DirectMktShipDay', $current->format('d')));

        return $directMktDataElement;
    }

    // endregion
}
