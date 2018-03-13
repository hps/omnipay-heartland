<?php

/**
 *  Heartland ACH Purchase Request.
 *
 * @category    HPS
 * @package     Omnipay_Heartland
 * @author      Heartland Developer Portal <EntApp_DevPortal@e-hps.com>
 * @copyright   Heartland (http://heartland.us)
 * @license     https://github.com/hps/omnipay-heartland/blob/master/LICENSE.md
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

/**
 * Heartland ACH Purchase Request.
 *
 * @see  \Omnipay\Heartland\Gateway
 * @package Omnipay\Heartland\Message
 */
class AchPurchaseRequest extends AbstractPorticoRequest
{
    protected $responseType = '\Omnipay\Heartland\Message\PorticoAchResponse';

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'CheckSale';
    }

    public function getData()
    {
        parent::getData();

        $this->validate('check', 'secCode', 'checkType');

        $amount = HpsInputValidation::checkAmount($this->getAmount());
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsCreditAuth = $xml->createElement('hps:' . $this->getTransactionType());
        $hpsBlock1 = $xml->createElement('hps:Block1');

        $hpsBlock1->appendChild($xml->createElement('hps:CheckAction', 'SALE'));

        if ($this->getEntryMode()) {
            $hpsBlock1->appendChild($xml->createElement('hps:DataEntryMode', strtoupper($this->getEntryMode())));
        }

        $hpsBlock1->appendChild($xml->createElement('hps:CheckType', strtoupper($this->getCheckType())));
        $hpsBlock1->appendChild($xml->createElement('hps:Amt', $amount));
        $hpsBlock1->appendChild($xml->createElement('hps:SECCode', strtoupper($this->getSecCode())));

        if ($this->getCheckReference()) {
            $hpsBlock1->appendChild($xml->createElement('hps:PaymentMethodKey', $this->getCheckReference()));
        } elseif ($this->getToken()) {
            $hpsBlock1->appendChild($xml->createElement('hps:TokenValue', $this->getToken()));
        } else {
            $hpsBlock1->appendChild($this->hydrateAccountInfo($xml));
        }

        $hpsBlock1->appendChild($this->hydrateCheckHolderData($xml));

        if ($this->getTransactionId()) {
            $hpsBlock1->appendChild($this->hydrateAdditionalTxnFields($xml));
        }

        $hpsCreditAuth->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditAuth);

        return $hpsTransaction;
    }

    public function getCheck()
    {
        return $this->getParameter('check');
    }

    public function setCheck($value)
    {
        return $this->setParameter('check', $value);
    }

    public function getSecCode()
    {
        return $this->getParameter('secCode');
    }

    public function setSecCode($value)
    {
        return $this->setParameter('secCode', $value);
    }

    public function getCheckType()
    {
        return $this->getParameter('checkType');
    }

    public function setCheckType($value)
    {
        return $this->setParameter('checkType', $value);
    }

    public function getEntryMode()
    {
        return $this->getParameter('entryMode');
    }

    public function setEntryMode($value)
    {
        return $this->setParameter('entryMode', $value);
    }

    public function getCheckReference()
    {
        return $this->getParameter('checkReference');
    }

    public function setCheckReference($value)
    {
        return $this->setParameter('checkReference', $value);
    }

    public function getToken()
    {
        return $this->getParameter('token');
    }

    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }

    private function hydrateAccountInfo(DOMDocument $xml)
    {
        $check = $this->getCheck();
        $info = $xml->createElement('hps:AccountInfo');

        if (!empty($check['accountNumber'])) {
            $info->appendChild($xml->createElement('hps:AccountNumber', $check['accountNumber']));
        }

        if (!empty($check['routingNumber'])) {
            $info->appendChild($xml->createElement('hps:RoutingNumber', $check['routingNumber']));
        }

        if (!empty($check['checkNumber'])) {
            $info->appendChild($xml->createElement('hps:CheckNumber', $check['checkNumber']));
        }

        if (!empty($check['micrData'])) {
            $info->appendChild($xml->createElement('hps:MICRData', $check['micrData']));
        }

        if (!empty($check['accountType'])) {
            $info->appendChild($xml->createElement('hps:AccountType', strtoupper($check['accountType'])));
        }

        return $info;
    }

    private function hydrateCheckHolderData(DOMDocument $xml)
    {
        $check = $this->getCheck();
        $info = $xml->createElement('hps:ConsumerInfo');

        if (empty($check['consumer'])) {
            return $info;
        }

        $consumer = $check['consumer'];

        if (!empty($consumer['billingFirstName'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:FirstName',
                    HpsInputValidation::checkCardHolderData($consumer['billingFirstName'], 'FirstName')
                )
            );
        }

        if (!empty($consumer['billingFirstName'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:LastName',
                    HpsInputValidation::checkCardHolderData($consumer['billingLastName'], 'LastName')
                )
            );
        }

        $checkName = 
            isset($consumer['checkName']) 
            ? $consumer['checkName']
            : sprintf(
                '%s %s',
                !empty($consumer['billingFirstName']) ? $consumer['billingFirstName'] : '',
                !empty($consumer['billingLastName']) ? $consumer['billingLastName'] : ''
            );
        if (!empty($checkName)) {
            $info->appendChild($xml->createElement('hps:CheckName', $checkName));
        }

        if (!empty($consumer['email'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:EmailAddress',
                    HpsInputValidation::checkEmailAddress($consumer['email'])
                )
            );
        }

        if (!empty($consumer['billingPhone'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:PhoneNumber',
                    HpsInputValidation::checkPhoneNumber($consumer['billingPhone'])
                )
            );
        }

        if (!empty($consumer['billingAddress1'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:Address1',
                    HpsInputValidation::checkCardHolderData($consumer['billingAddress1'])
                )
            );
        }

        if (!empty($consumer['billingAddress2'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:Address2',
                    HpsInputValidation::checkCardHolderData($consumer['billingAddress2'])
                )
            );
        }

        if (!empty($consumer['billingCity'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:City',
                    HpsInputValidation::checkCardHolderData($consumer['billingCity'], 'City')
                )
            );
        }

        if (!empty($consumer['billingState'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:State',
                    HpsInputValidation::checkCardHolderData($consumer['billingState'], 'State')
                )
            );
        }

        if (!empty($consumer['billingPostcode'])) {
            $info->appendChild(
                $xml->createElement(
                    'hps:Zip',
                    HpsInputValidation::checkZipCode($consumer['billingPostcode'])
                )
            );
        }
        
        return $info;
    }
}
