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

use Guzzle\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    const MIN_OPENSSL_VER = 268439615; //OPENSSL_VERSION_NUMBER openSSL 1.0.1c

    /**
     * @var \stdClass The retrieved response, saved immediately after a transaction is run.
     */
    protected $response;

    /**
     * @var float The amount of time in seconds to wait for both a connection and a response.
     * Total potential wait time is this value times 2 (connection + response).
     */
    public $timeout = 100;

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
     * Gets the full URL to the required web service.
     *
     * @return string
     */
    abstract public function getEndpoint();

    /**
     * Gets the transaction type for response parsing and logging.
     *
     * @return string
     */
    abstract public function getTransactionType();

    public function validateConnectionParameters()
    {
        //check whether secretApiKey or siteid details passed
        if ($this->getSecretApiKey() == null) {
            $this->validate(
                'siteId',
                'deviceId',
                'licenseId',
                'username',
                'password',
                'serviceUri'
            );
        } else {
            $this->validate('secretApiKey');
        }

        // TODO: Build correct validations based on request type.
    }

    /**
     * Submits the request to the configured HTTP client
     *
     * @param array $args
     *
     * @return Response
     * @throws InvalidRequestException
     * @throws InvalidResponseException
     */
    protected function submitRequest($args = array())
    {
        $this->validateConnectionParameters();

        $http = array_merge(array(
            'options' => array(),
            'uri' => '',
            'verb' => 'POST',
        ), isset($args['http']) ? $args['http'] : array());
        $body = isset($args['body']) ? $args['body'] : null;
        $url = $this->getEndpoint() . $http['uri'];

        if (strtoupper($http['verb']) === 'GET') {
            $body = null;
        }

        $headers = array_merge(array(
            'Content-Length' => (string) strlen($body),
        ), isset($args['headers']) ? $args['headers'] : array());

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
                /** @var \Symfony\Component\EventDispatcher\Event $event */
                'request.error',
                function ($event) {
                    if ($event['response']->isClientError()) {
                        $event->stopPropagation();
                    }
                }
            );

            if (!isset($http['verb']) || !method_exists($this->httpClient, strtolower($http['verb']))) {
                throw new InvalidRequestException("unknown http method/verb");
            }

            $httpResponse = $this->httpClient
                ->createRequest(strtoupper($http['verb']), $url, $headers, $body, $http['options'])
                ->send();

            $response = new \stdClass();
            $response->response = (string) $httpResponse->getBody();
            $response->status = $httpResponse->getStatusCode();
            
            if ($response->status == 35) { //CURLE_SSL_CONNECT_ERROR
                $err_msg = 'PHP-SDK cURL TLS 1.2 handshake failed. If you have any questions, please contact '
                    . 'Heartland\'s Specialty Products Team at 866.802.9753.';
                if (extension_loaded('openssl') && OPENSSL_VERSION_NUMBER < static::MIN_OPENSSL_VER) {
                    // then you don't have openSSL 1.0.1c or greater
                    $err_msg .= 'Your current version of OpenSSL is ' . OPENSSL_VERSION_TEXT . 'You do not '
                        . 'have the minimum version of OpenSSL 1.0.1c which is required for curl to use TLS '
                        . '1.2 handshake.';
                }
                throw new InvalidResponseException($err_msg);
            }
            //process the response
            $gatewayResponse = new $this->responseType($this, $response, $this->getTransactionType());

            //perform reversal incase of gateway error
            //CURLE_OPERATION_TIMEOUTED
            if (($response->status == 28 || $gatewayResponse->reversalRequired === true) && in_array($this->getTransactionType(), array('CreditSale', 'CreditAuth')) && $gatewayResponse->getTransactionReference() != null) {
                try {
                    $reverseRequest = new ReverseRequest($this->httpClient, $this->httpRequest);
                    $reverseRequest->initialize($this->getParameters());
                    $reverseRequest->setTransactionReference($gatewayResponse->getTransactionReference());
                    $reverseResponse = $reverseRequest->send();   
                } catch (\Exception $e) {
                    throw new InvalidResponseException(
                        'Error occurred while reversing a charge due to HPS issuer timeout. '
                        . $e->getMessage()
                    );
                    return;
                }
                throw new InvalidResponseException("gateway_time-out");
            }

            return $gatewayResponse;
        } catch (\Exception $e) {
            throw new InvalidRequestException($e->getMessage());
        }
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
     * @return \Omnipay\Common\Message\AbstractRequest
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

    /**
     * Set the gateway Site Id.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSiteId($value)
    {
        return $this->setParameter('siteId', $value);
    }

    /**
     * Get the gateway Device Id.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @return string
     */
    public function getDeviceId()
    {
        return $this->getParameter('deviceId');
    }

    /**
     * Set the gateway Device Id.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setDeviceId($value)
    {
        return $this->setParameter('deviceId', $value);
    }

    /**
     * Get the gateway License Id.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @return string
     */
    public function getLicenseId()
    {
        return $this->getParameter('licenseId');
    }

    /**
     * Set the gateway License Id.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setLicenseId($value)
    {
        return $this->setParameter('licenseId', $value);
    }

    /**
     * Get the gateway username.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getParameter('username');
    }

    /**
     * Set the gateway username.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    /**
     * Get the gateway password.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Get the gateway password.
     *
     * Authentication is by means of a single secret API key set as
     * the secretApiKey parameter when creating the gateway object.
     *
     * When you don't have a Secret API Key you can use your Site Id, Device Id, License Id
     * User name and Password details
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Get the integration developer ID.
     *
     * The developer ID, in conjunction with the version number, is used to identify a
     * specific integration.
     *
     * @return string
     */
    public function getDeveloperId()
    {
        return $this->getParameter('developerId');
    }

    /**
     * Set the integration developer ID.
     *
     * The developer ID, in conjunction with the version number, is used to identify a
     * specific integration.
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setDeveloperId($value)
    {
        return $this->setParameter('developerId', $value);
    }

    /**
     * Get the integration version number.
     *
     * The version number, in conjunction with the developer ID, is used to identify a
     * specific integration.
     *
     * @return string
     */
    public function getVersionNumber()
    {
        return $this->getParameter('versionNumber');
    }

    /**
     * Set the integration version number.
     *
     * The version number, in conjunction with the developer ID, is used to identify a
     * specific integration.
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setVersionNumber($value)
    {
        return $this->setParameter('versionNumber', $value);
    }

    /**
     * Get the gateway site trace value.
     *
     * This can be used to debug issues with the gateway.
     *
     * @return string
     */
    public function getSiteTrace()
    {
        return $this->getParameter('siteTrace');
    }

    /**
     * Set the gateway site trace value.
     *
     * This can be used to debug issues with the gateway.
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSiteTrace($value)
    {
        return $this->setParameter('siteTrace', $value);
    }

    /**
     * Get the gateway service URI
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function getServiceUri()
    {
        return $this->getParameter('serviceUri');
    }

    /**
     * Set the gateway service URI
     *
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setServiceUri($value)
    {
        return $this->setParameter('serviceUri', $value);
    }
    
    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }
   
    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }
    
    public function getTransactionHistoryId()
    {
        return $this->getParameter('transactionHistoryId');
    }
   
    public function setTransactionHistoryId($value)
    {
        return $this->setParameter('transactionHistoryId', $value);
    }
}
