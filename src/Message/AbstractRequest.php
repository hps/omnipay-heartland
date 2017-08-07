<?php

/**
 *  Heartland Abstract Request.
 *
 * @category    HPS
 * @package     Omnipay_Heartland
 * @author      Heartland Developer Portal <EntApp_DevPortal@e-hps.com>
 * @copyright   Heartland (http://heartland.us)
 * @license     https://github.com/hps/omnipay-heartland/blob/master/LICENSE.md
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;
use Guzzle\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Omnipay\Heartland;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{

    const MIN_OPENSSL_VER = 268439615; //OPENSSL_VERSION_NUMBER openSSL 1.0.1c

   
    /**
     * @var \stdClass The retrieved SOAP response, saved immediately after a transaction is run.
     */
    protected $response;

    /**
     * @var float The amount of time in seconds to wait for both a connection and a response. 
     * Total potential wait time is this value times 2 (connection + response).
     */
    public $timeout = 100;
    private $_config;

    /**
     * Create a new Request
     *
     * @param ClientInterface $httpClient  A Guzzle client to make API calls with
     * @param HttpRequest     $httpRequest A Symfony HTTP request object
     */
    public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest) 
    {
        parent::__construct($httpClient, $httpRequest);
        
        
    }

    /**
     * Get the gateway Secret API Key.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * @return string
     */
    public function getSecretApiKey() 
    {
        return $this->getParameter('secretApiKey');
    }

    /**
     * Set the gateway Secret API Key.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * Heartland accounts have test-mode API keys as well as live-mode
     * API keys. These keys can be active at the same time. Data
     * created with test-mode credentials will never hit the credit
     * card networks and will never cost anyone money.
     *
     * Unlike some gateways, there is no test mode endpoint separate
     * to the live mode endpoint, the Heartland API endpoint is the same
     * for test and for live.
     *
     * Setting the testMode flag on this gateway has no effect.  To
     * use test mode just use your test mode API key.
     * 
     * You can get your secret API key in heartland payments developer site
     *
     * @link https://developer.heartlandpaymentsystems.com/Account/KeysandCredentials
     *
     * @param string $value
     *
     * @return Gateway provides a fluent interface.
     */
    public function setSecretApiKey($value) 
    {
        return $this->setParameter('secretApiKey', $value);
    }
    
    /**
     * Get the gateway Site Id.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     * 
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @return string
     */
    public function getSiteId() 
    {
        return $this->getParameter('siteId');
    }
    
    public function setSiteId($value) 
    {
        return $this->setParameter('siteId', $value);
    }
    
    public function getDeviceId() 
    {
        return $this->getParameter('deviceId');
    }
    
    public function setDeviceId($value) 
    {
        return $this->setParameter('deviceId', $value);
    }
    
    public function getLicenseId() 
    {
        return $this->getParameter('licenseId');
    }
    
    public function setLicenseId($value) 
    {
        return $this->setParameter('licenseId', $value);
    }
    
    public function getUsername() 
    {
        return $this->getParameter('username');
    }
    
    public function setUsername($value) 
    {
        return $this->setParameter('username', $value);
    }
    
    public function getPassword() 
    {
        return $this->getParameter('password');
    }
    
    public function setPassword($value) 
    {
        return $this->setParameter('password', $value);
    }
    
    public function getDeveloperId() 
    {
        return $this->getParameter('developerId');
    }
    
    public function setDeveloperId($value) 
    {
        return $this->setParameter('developerId', $value);
    }
    
    public function getVersionNumber() 
    {
        return $this->getParameter('versionNumber');
    }
    
    public function setVersionNumber($value) 
    {
        return $this->setParameter('versionNumber', $value);
    }
    
    public function getSiteTrace() 
    {
        return $this->getParameter('siteTrace');
    }
    
    public function setSiteTrace($value) 
    {
        return $this->setParameter('siteTrace', $value);
    }
    
    public function getSoapServiceUri() 
    {
        return $this->getParameter('soapServiceUri');
    }
    
    public function setSoapServiceUri($value) 
    {
        return $this->setParameter('soapServiceUri', $value);
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
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderFirstName', HpsInputValidation::checkCardHolderData($creditCard->getBillingFirstName(), 'FirstName')));
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderLastName', HpsInputValidation::checkCardHolderData($creditCard->getBillingLastName(), 'LastName')));
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderEmail', HpsInputValidation::checkEmailAddress($creditCard->getEmail())));
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderPhone', HpsInputValidation::checkPhoneNumber($creditCard->getBillingPhone())));
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderAddr', HpsInputValidation::checkCardHolderData($address)));
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderCity', HpsInputValidation::checkCardHolderData($creditCard->getBillingCity(), 'City')));
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderState', HpsInputValidation::checkCardHolderData($creditCard->getBillingState(), 'State')));
            $cardHolderData->appendChild($xml->createElement('hps:CardHolderZip', HpsInputValidation::checkZipCode($creditCard->getBillingPostcode())));
        }
        return $cardHolderData;
    }

    // endregion
    // region Parameters
    
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
    protected function getCardData() {
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
        //check whether secretApiKey or siteid details passed
        if($this->getSecretApiKey() == null){
            $this->validate('siteId', 'deviceId', 'licenseId', 'username', 'password', 'soapServiceUri');
        } else {        
            $this->validate('secretApiKey');
        }      
        
        $cardNotRequired = array('CreditAddToBatch', 'CreditVoid', 'CreditReturn');
        if (in_array($this->getTransactionType(), $cardNotRequired) === false) {            
            //check the token value in card reference
            if ($this->getToken() == null && $this->getCardReference() != null) {
                $cardReference = trim($this->getCardReference());
                if (!empty($cardReference)){ 
                    $this->setToken($cardReference);
                }
            }
            //if token not passed validate card
            $this->validate('amount');
            if ($this->getToken() == null) {
                $this->validate('card');
                $this->getCard()->validate();
            } else {
                $this->validate('token');
            }
        }
    }

    public function sendData($data) 
    {
        $data = $this->getData();

        // build the class for the request
        $this->_config = new \stdClass();

        $this->_config->secretApiKey = $this->getSecretApiKey();
        
        $this->_config->siteId = $this->getSiteId();
        $this->_config->deviceId = $this->getDeviceId();
        $this->_config->licenseId = $this->getLicenseId();
        $this->_config->username = $this->getUsername();
        $this->_config->password = $this->getPassword();
        $this->_config->soapServiceUri = $this->getSoapServiceUri();
        
        $this->_config->developerId = $this->getDeveloperId();
        $this->_config->versionNumber = $this->getVersionNumber();
        $this->_config->siteTrace = $this->getSiteTrace();
        

        $xml = new DOMDocument('1.0', 'utf-8');
        $soapEnvelope = $xml->createElement('soapenv:Envelope');
        $soapEnvelope->setAttribute('xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $soapEnvelope->setAttribute('xmlns:hps', 'http://Hps.Exchange.PosGateway');

        $soapBody = $xml->createElement('soapenv:Body');
        $hpsRequest = $xml->createElement('hps:PosRequest');

        $hpsVersion = $xml->createElement('hps:Ver1.0');
        $hpsHeader = $xml->createElement('hps:Header');

        if ($this->_config->secretApiKey != null && $this->_config->secretApiKey != "") {
            $hpsHeader->appendChild($xml->createElement('hps:SecretAPIKey', trim($this->_config->secretApiKey)));
        } else {
            $hpsHeader->appendChild($xml->createElement('hps:SiteId', $this->_config->siteId));
            $hpsHeader->appendChild($xml->createElement('hps:DeviceId', $this->_config->deviceId));
            $hpsHeader->appendChild($xml->createElement('hps:LicenseId', $this->_config->licenseId));
            $hpsHeader->appendChild($xml->createElement('hps:UserName', $this->_config->username));
            $hpsHeader->appendChild($xml->createElement('hps:Password', $this->_config->password));
        }
        if ($this->_config->developerId != null && $this->_config->developerId != "") {
            $hpsHeader->appendChild($xml->createElement('hps:DeveloperID', $this->_config->developerId));
            $hpsHeader->appendChild($xml->createElement('hps:VersionNbr', $this->_config->versionNumber));
            $hpsHeader->appendChild($xml->createElement('hps:SiteTrace', $this->_config->siteTrace));
        }
        if (isset($options['clientTransactionId'])) {
            $hpsHeader->appendChild($xml->createElement('hps:ClientTxnId', $options['clientTransactionId']));
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
        
        return $this->submitRequest($soapXML);
    }

    /**
     * @return string
     */
    public function getEndpoint() 
    {
        if ($this->_config->secretApiKey != null && $this->_config->secretApiKey != "") {
            if (strpos($this->_config->secretApiKey, '_cert_') !== false) {
                return "https://cert.api2.heartlandportico.com/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            } else if (strpos($this->_config->secretApiKey, '_uat_') !== false) {
                return "https://posgateway.uat.secureexchange.net/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            } else {
                return "https://api2.heartlandportico.com/Hps.Exchange.PosGateway/PosGatewayService.asmx";
            }
        } else {
            return $this->_config->soapServiceUri;
        }
    }

    /**
     * @param        $url
     * @param null $data     
     *
     * @return mixed
     * @throws \InvalidResponseException
     */
    private function submitRequest($data = null, $httpVerb = 'POST') 
    {
        $url = $this->getEndpoint();
        $headers = array(
            'Content-type' => 'text/xml; charset="UTF-8"',
            'Accept' => 'text/xml',
            'SOAPAction' => '""',
            'Content-Length' => '' . strlen($data),
        );
        try {
            $config = $this->httpClient->getConfig();
            $curlOptions = $config->get('curl.options');
            $curlOptions[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
            $curlOptions[CURLOPT_CONNECTTIMEOUT] = $this->timeout;
            $curlOptions[CURLOPT_TIMEOUT] = $this->timeout;

            $config->set('curl.options', $curlOptions);
            $this->httpClient->setConfig($config);

            // don't throw exceptions for 4xx errorsd
            $this->httpClient->getEventDispatcher()->addListener(
                'request.error', function ($event) {
                    if ($event['response']->isClientError()) {
                        $event->stopPropagation();
                    }
                }
            );

            $httpResponse = $this->httpClient->post($url, $headers, $data)->send();             

            $response = new \stdClass();
            $response->response = (string) $httpResponse->getBody();
            $response->status = $httpResponse->getStatusCode();            
            
            if ($response->status == 28) { //CURLE_OPERATION_TIMEOUTED
                throw new InvalidResponseException("gateway_time-out");
            }

            if ($response->status == 35) { //CURLE_SSL_CONNECT_ERROR
                $err_msg = 'PHP-SDK cURL TLS 1.2 handshake failed. If you have any questions, please contact Specialty Products Team at 866.802.9753.';
                if (extension_loaded('openssl') && OPENSSL_VERSION_NUMBER < self::MIN_OPENSSL_VER) { // then you don't have openSSL 1.0.1c or greater
                    $err_msg .= 'Your current version of OpenSSL is ' . OPENSSL_VERSION_TEXT . 'You do not have the minimum version of OpenSSL 1.0.1c which is required for curl to use TLS 1.2 handshake.';
                }
                throw new InvalidResponseException($err_msg);
            }
            //process the response
            return new Response($this, $response, $this->getTransactionType());
        } catch (Exception $e) {
            throw new InvalidRequestException($e->getMessage());
        }
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
        $token = $this->getToken();

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

        if ($this->getCustomerReference()) {
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
