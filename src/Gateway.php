<?php

/**
 * Heartland Gateway.
 */

namespace Omnipay\Heartland;

use Omnipay\Common\AbstractGateway;

/**
 * Heartland Gateway.
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the Heartland Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('Heartland');
 *
 *   //Using api key and token
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'secretApiKey' => 'MySecretApiKey'
 *       'developerId' => 'MyDeveloperId',
 *       'versionNumber' => 'MyVersionNumber',
 *       'siteTrace' => 'MySiteTrace'
 *   ));
 *
 *   // Do a purchase transaction on the gateway. Either token / cardReference can be used to pass the token
 *   $transaction = $gateway->purchase(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'token'                     => 'supt_hS4mCc7AtLR4VO6GhyKrMiY8',
 *   ));
 *   $response = $transaction->send();
 *
 *   //Using api key and credit card details
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'secretApiKey' => 'MySecretApiKey',
 *       'developerId' => 'MyDeveloperId',
 *       'versionNumber' => 'MyVersionNumber',
 *       'siteTrace' => 'MySiteTrace'
 *   ));
 *
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   $card = new CreditCard(array(
 *         'firstName'    => 'Example',
 *         'lastName'     => 'Customer',
 *         'number'       => '4242424242424242',
 *         'expiryMonth'  => '01',
 *         'expiryYear'   => '2020',
 *         'cvv'          => '123',
 *         'email'           => 'customer@example.com',
 *         'billingAddress1' => '1 Scrubby Creek Road',
 *         'billingCountry'  => 'AU',
 *         'billingCity'     => 'Scrubby Creek',
 *         'billingPostcode' => '4999',
 *         'billingState'    => 'QLD',
 *   ));
 *
 *   // Do a purchase transaction on the gateway
 *   $transaction = $gateway->purchase(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'card'                     => $card,
 *   ));
 *   $response = $transaction->send();
 *
 *   //With out api key and credit card details
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'siteId' => 'MysiteId',
 *       'deviceId' => 'MydeviceId',
 *       'licenseId' => 'MyLicenseId',
 *       'username' => 'MyUsername',
 *       'password' => 'MyPassword',
 *       'soapServiceUri' => 'MySoapServiceUri',
 *       'developerId' => 'MyDeveloperId',
 *       'versionNumber' => 'MyVersionNumber',
 *       'siteTrace' => 'MySiteTrace'
 *   ));
 *
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   $card = new CreditCard(array(
 *         'firstName'    => 'Example',
 *         'lastName'     => 'Customer',
 *         'number'       => '4242424242424242',
 *         'expiryMonth'  => '01',
 *         'expiryYear'   => '2020',
 *         'cvv'          => '123',
 *         'email'           => 'customer@example.com',
 *         'billingAddress1' => '1 Scrubby Creek Road',
 *         'billingCountry'  => 'AU',
 *         'billingCity'     => 'Scrubby Creek',
 *         'billingPostcode' => '4999',
 *         'billingState'    => 'QLD',
 *   ));
 *
 *   // Do a purchase transaction on the gateway
 *   $transaction = $gateway->purchase(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'card'                     => $card,
 *   ));
 *   $response = $transaction->send();
 *
 *   if ($response->isSuccessful()) {
 *       echo "Purchase transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *
 *       $balance_transaction_id = $response->getBalanceTransactionReference();
 *       echo "Balance Transaction reference = " . $balance_transaction_id . "\n";
 *   }
 * </code>
 *
 * Test modes:
 *
 * Heartland accounts have test-mode Secret API keys as well as live-mode API keys. Data
 * created with test-mode credentials will never hit the credit
 * card networks and will never cost anyone money.
 *
 *
 * Setting the testMode flag on this gateway has no effect.  To
 * use test mode just use your test mode Secret API key.
 *
 * You can generate your Secret API key in heardland website
 * https://developer.heartlandpaymentsystems.com/Account/KeysandCredentials
 *
 * If you don't have a Secret API key, You can use your SiteId, DeviceId, licenseId, username and password details
 *
 * You can use any of the cards listed at https://github.com/hps/heartland-php
 * for testing.
 *
 * Authentication:
 *
 * Authentication is by means of a single secret API key set as
 * the apiKey parameter when creating the gateway object.
 *
 * @see \Omnipay\Common\AbstractGateway
 * @see \Omnipay\Heartland\Message\AbstractPorticoRequest
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Heartland';
    }

    /**
     * Get the gateway parameters.
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'secretApiKey' => '',
            'siteId' => '',
            'deviceId' => '',
            'licenseId' => '',
            'username' => '',
            'password' => '',
            'serviceUri' => '',
            'developerId' => '',
            'versionNumber' => '',
            'siteTrace' => ''
        );
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

    /**
     * Authorize Request.
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
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\AuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\AuthorizeRequest', $parameters);
    }

    /**
     * Capture Request.
     *
     * Use this request to capture and process a previously created authorization.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\CaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\CaptureRequest', $parameters);
    }

    /**
     * Purchase request.
     *
     * To charge a credit card, you create a new charge object. If your API key
     * is in test mode, the supplied card won't actually be charged, though
     * everything else will occur as if in live mode. (Heartland assumes that the
     * charge would have completed successfully).
     *
     * Either a payment token or a card is required.  Token is like the ones returned by
     * securesubmit.js, or a dictionary containing a user's credit card details.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\PurchaseRequest', $parameters);
    }

    /**
     * Refund Request.
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
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\RefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\RefundRequest', $parameters);
    }

    /**
     * Void Request.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\VoidRequest
     */
    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\VoidRequest', $parameters);
    }

    /**
     * Reversal Request.
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
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\RefundRequest
     */
    public function reverse(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\ReverseRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\FetchTransactionRequest
     */
    public function fetchTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\FetchTransactionRequest', $parameters);
    }

    // Recurring Payments (PayPlan)

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\RecurringBillingRequest
     */
    public function recurring(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\RecurringBillingRequest', $parameters);
    }

    //
    // Customers
    //

    /**
     * Create Customer.
     *
     * Customer objects allow you to perform recurring charges and
     * track multiple charges that are associated with the same customer.
     * The API allows you to create, delete, and update your customers.
     * You can retrieve individual customers as well as a list of all of
     * your customers.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\CreateCustomerRequest
     */
    public function createCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\CreateCustomerRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\UpdateCustomerRequest
     */
    public function updateCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\UpdateCustomerRequest', $parameters);
    }

    //
    // Schedules
    //

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\CreateScheduleRequest
     */
    public function createSchedule(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\CreateScheduleRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\FetchSchedulesRequest
     */
    public function fetchSchedules(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\FetchSchedulesRequest', $parameters);
    }
   
    //
    // Payment Methods
    //

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\CreatePaymentMethodRequest
     */
    public function createPaymentMethod(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\CreatePaymentMethodRequest', $parameters);
    }
    
    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\getPaymentMethodRequest
     */
    public function getPaymentMethod(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\getPaymentMethodRequest', $parameters);
    }
    
    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\deletePaymentMethodRequest
     */
    public function deletePaymentMethod(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\deletePaymentMethodRequest', $parameters);
    }
    
    /**
     * @param array $parameters
     *
     * @return \Omnipay\Heartland\Message\editPaymentMethodRequest
     */
    public function editPaymentMethodRequest(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Heartland\Message\editPaymentMethodRequest', $parameters);
    }
    
}
