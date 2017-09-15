<?php

/**
 * Heartland Purchase Card Edit Request.
 */

namespace Omnipay\Heartland\Message;

use DOMDocument;

class PurchaseCardEditRequest extends AbstractPorticoRequest
{
    /**
     * @return string
     */
    public function getTransactionType()
    {
        return 'CreditCPCEdit';
    }

    public function getData()
    {
        parent::getData();

        $xml = new DOMDocument();
        $hpsTransaction = $xml->createElement('hps:Transaction');
        $hpsCreditCPCEdit = $xml->createElement('hps:' . $this->getTransactionType());

        $hpsCreditCPCEdit->appendChild($xml->createElement('hps:GatewayTxnId', $this->getTransactionReference()));

        $cpcDataElement = $xml->createElement('hps:CPCData');
        if ($this->getCardHolderPONumber()) {
            $cpcDataElement->appendChild($xml->createElement('hps:CardHolderPONbr', $this->getCardHolderPONumber()));
        }
        if ($this->getTaxAmount()) {
            $cpcDataElement->appendChild($xml->createElement('hps:TaxAmt', $this->getTaxAmount()));
        }
        if ($this->getTaxType()) {
            $cpcDataElement->appendChild($xml->createElement('hps:TaxType', $this->getTaxType()));
        }

        $hpsCreditCPCEdit->appendChild($cpcDataElement);
        $hpsTransaction->appendChild($hpsCreditCPCEdit);

        return $hpsTransaction;
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
}
