<?php

namespace Omnipay\Heartland\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Class HpsInputValidation
 */
class HpsInputValidation
{
    private static $defaultAllowedCurrencies = array('usd');
    private static $inputFldMaxLength = array(
        'PhoneNumber' => 20,
        'ZipCode' => 9,
        'FirstName' => 26,
        'LastName' => 26,
        'City' => 20,
        'Email' => 100,
        'State' => 20,
    );

    /**
     * @param $amount
     *
     * @return string
     * @throws \InvalidRequestException
     */
    public static function checkAmount($amount)
    {
        if ($amount < 0 || $amount === null) {
            throw new InvalidRequestException(
                'Amount must be greater than or equal to 0.'
            );
        }
        $amount = preg_replace('/[^0-9\.]/', '', $amount);
        return sprintf("%0.2f", round($amount, 3));
    }

    /**
     * @param      $currency
     * @param null     $allowedCurrencies
     *
     * @throws \InvalidRequestException
     */
    public static function checkCurrency($currency, $allowedCurrencies = null)
    {
        $currencies = self::$defaultAllowedCurrencies;
        if (isset($allowedCurrencies) && is_array($allowedCurrencies)) {
            $currencies = $allowedCurrencies;
        }

        if ($currency == null || $currency == '') {
            throw new InvalidRequestException(
                'Currency cannot be none'
            );
        } elseif (!in_array(strtolower($currency), $currencies)) {
            throw new InvalidRequestException(
                "'".strtolower($currency)."' is not a supported currency"
            );
        }
    }

    /**
     * @param $number
     *
     * @return mixed
     */
    public static function cleanPhoneNumber($number)
    {
        return preg_replace('/\D+/', '', trim($number));
    }

    /**
     * @param $zip
     *
     * @return mixed
     */
    public static function cleanZipCode($zip)
    {
        return preg_replace('/[^0-9A-Za-z]/', '', trim($zip));
    }

    /**
     * @param $date
     *
     * @throws \InvalidRequestException
     */
    public static function checkDateNotFuture($date)
    {
        $current = date('Y-m-d\TH:i:s.00\Z', time());

        if ($date != null && $date > $current) {
            throw new InvalidRequestException(
                'Date cannot be in the future'
            );
        }
    }

    /**
     * @param $text
     *
     * @return mixed
     */
    public static function cleanAscii($text)
    {
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
    }

    /**
     * This method clears the user input and return the phone number in correct format or throw an exception
     *
     * @param  string $phoneNumber this is user entered phone number
     * @return string
     * @throws InvalidRequestException
     */
    public static function checkPhoneNumber($phoneNumber)
    {
        $phoneNumber = self::cleanPhoneNumber($phoneNumber);

        if (!empty($phoneNumber) && strlen($phoneNumber) > self::$inputFldMaxLength['PhoneNumber']) {
            $errorMessage = 'The value for phone number can be no more than '
                . self::$inputFldMaxLength['PhoneNumber']
                . ' characters, Please try again after making corrections';
            throw new InvalidRequestException(
                $errorMessage
            );
        }
        return $phoneNumber;
    }

    /**
     * This method clears the user input and return the Zip code in correct format or throw an exception
     *
     * @param  string $zipCode this is user entered zip code
     * @return string
     * @throws InvalidRequestException
     */
    public static function checkZipCode($zipCode)
    {
        $zipCode = self::cleanZipCode($zipCode);

        if (!empty($zipCode) && strlen($zipCode) > self::$inputFldMaxLength['ZipCode']) {
            $errorMessage = 'The value for zip code can be no more than '
                . self::$inputFldMaxLength['ZipCode']
                . ' characters, Please try again after making corrections';
            throw new InvalidRequestException(
                $errorMessage
            );
        }
        return $zipCode;
    }

    /**
     * This method clears the user input and return the user input in correct format or throw an exception
     *
     * @param  string $value this is user entered value (first name or last name or email or city)
     * @param  string $type  this is user entered value field name
     * @return string
     * @throws InvalidRequestException
     */
    public static function checkCardHolderData($value, $type = '')
    {
        $value = filter_var(trim($value), FILTER_SANITIZE_SPECIAL_CHARS);

        //validate length of input data and throw exception
        //if maximum characters is not mentioned in $inputFldMaxLength the sanitized values will be returned
        if (!empty(self::$inputFldMaxLength[$type]) && strlen($value) > self::$inputFldMaxLength[$type]) {
            $errorMessage = "The value for $type can be no more than "
                . self::$inputFldMaxLength[$type]
                . ' characters, Please try again after making corrections';
            throw new InvalidRequestException(
                $errorMessage
            );
        }
        return $value;
    }

    /**
     * This method clears the user input and return the email in correct format or throw an exception
     *
     * @param  string $value this is user entered email address
     * @return string
     * @throws InvalidRequestException
     */
    public static function checkEmailAddress($value)
    {
        $value = filter_var(trim($value), FILTER_SANITIZE_EMAIL);

        //validate the email address format
        if (!empty($value) && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidRequestException(
                'Invalid email address'
            );
        }

        //validate length of input data and throw exception
        if (!empty(self::$inputFldMaxLength['Email']) && strlen($value) > self::$inputFldMaxLength['Email']) {
            $errorMessage = "The value for Email can be no more than "
                . self::$inputFldMaxLength['Email']
                . ' characters, Please try again after making corrections';
            throw new InvalidRequestException(
                $errorMessage
            );
        }
        return $value;
    }
}
