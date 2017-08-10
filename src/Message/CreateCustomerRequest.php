<?php

/**
 * Heartland Create Customer Request.
 */
namespace Omnipay\Heartland\Message;

/**
 * Heartland Create Customer Request.
 *
 * Customer objects allow you to perform recurring charges and
 * track multiple charges that are associated with the same customer.
 * The API allows you to create, delete, and update your customers.
 * You can retrieve individual customers as well as a list of all of
 * your customers.
 *
 * ### Examples
 *
 * #### Create Customer from Email Address
 *
 * This is the recommended way to create a customer object.
 *
 * <code>
 * $response = $gateway->createCustomer(array(
 *     'description'       => 'Test Customer',
 *     'email'             => 'test123@example.com',
 * ))->send();
 * if ($response->isSuccessful()) {
 *     echo "Gateway createCustomer was successful.\n";
 *     // Find the card ID
 *     $customer_id = $response->getCustomerReference();
 *     echo "Customer ID = " . $customer_id . "\n";
 * } else {
 *     echo "Gateway createCustomer failed.\n";
 *     echo "Error message == " . $response->getMessage() . "\n";
 * }
 * </code>
 *
 * The $customer_id can now be used in a createCard() call.
 *
 * #### Create Customer using Card Object
 *
 * Historically, this library used a card object to create customers.
 * Although this is no longer the recommended path, it is still supported.
 * Using this approach, a customer object and a card object can be created
 * at the same time.
 *
 * <code>
 * // Create a credit card object
 * // This card can be used for testing.
 * // The CreditCard object is also used for creating customers.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4242424242424242',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 *             'email'                 => 'customer@example.com',
 *             'billingAddress1'       => '1 Scrubby Creek Road',
 *             'billingCountry'        => 'AU',
 *             'billingCity'           => 'Scrubby Creek',
 *             'billingPostcode'       => '4999',
 *             'billingState'          => 'QLD',
 * ));
 *
 * // Do a create customer transaction on the gateway
 * $response = $gateway->createCustomer(array(
 *     'card'                     => $card,
 * ))->send();
 * if ($response->isSuccessful()) {
 *     echo "Gateway createCustomer was successful.\n";
 *     // Find the customer ID
 *     $customer_id = $response->getCustomerReference();
 *     echo "Customer ID = " . $customer_id . "\n";
 *     // Find the card ID
 *     $card_id = $response->getCardReference();
 *     echo "Card ID = " . $card_id . "\n";
 * }
 * </code>
 *
 * @link https://heartland.com/docs/api#customers
 */
class CreateCustomerRequest extends AbstractPayPlanRequest
{
    private $alphabet = 'abcdefghijklmnopqrstuvwxyz';
    const ACTIVE   = 'Active';
    const INACTIVE = 'Inactive';
    
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'HpsPayPlanCustomerAdd';
    }
    
    public function getData()
    {
        parent::getData();
        $this->validate('card');
        $data = array();
        $data['description'] = $this->getDescription();
        
        $creditCard = $this->getCard();
        
        date_default_timezone_set('UTC');
        $id = date('Ymd').'-SecureSubmit-'.substr(str_shuffle($this->alphabet), 0, 10);
        $data['customerIdentifier'] = $id;
        $data['firstName']          = $creditCard->getBillingFirstName();
        $data['lastName']           = $creditCard->getBillingLastName();
        $data['company']            = $creditCard->getBillingCompany();
        $data['country']            = $creditCard->getBillingCountry();
        $data['customerStatus']     = self::ACTIVE;
        
        $data['http'] = array(
            'verb'     => 'POST',
            'uri' => '/customers',
        );

        return $data;
    }
}
