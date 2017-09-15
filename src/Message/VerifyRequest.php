<?php

/**
 * Heartland Verify Request.
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

class VerifyRequest extends AbstractPorticoRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'CreditAccountVerify';
    }

    public function getData()
    {
        $this->setParameter('amount', '0.00');
        parent::getData();

        $amount = HpsInputValidation::checkAmount($this->getAmount());
        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsCreditAccountVerify = $xml->createElement('hps:' . $this->getTransactionType());
        $hpsBlock1 = $xml->createElement('hps:Block1');

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

        $hpsBlock1->appendChild($this->hydrateCardHolderData($xml));

        if ($this->getPaymentMethodReference()) {
            $hpsCreditAccountVerify->appendChild($xml->createElement(
                'hps:PaymentMethodKey',
                $this->getPaymentMethodReference()
            ));
        }

        $hpsCreditAccountVerify->appendChild($hpsBlock1);
        $hpsTransaction->appendChild($hpsCreditAccountVerify);

        return $hpsTransaction;
    }

    public function getRequestCardReference()
    {
        return $this->getParameter('requestCardReference');
    }

    public function setRequestCardReference($value)
    {
        return $this->setParameter('requestCardReference', $value);
    }

    public function getPaymentMethodReference()
    {
        return $this->getParameter('paymentMethodReference');
    }

    public function setPaymentMethodReference($value)
    {
        return $this->setParameter('paymentMethodReference', $value);
    }
}
