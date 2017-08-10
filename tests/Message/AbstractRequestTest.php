<?php

namespace Omnipay\Heartland\Message;

use Mockery;
use Omnipay\Tests\TestCase;

class AbstractRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = Mockery::mock('\Omnipay\Heartland\Message\AbstractRequest')->makePartial();          
        $this->request->initialize();
    }

    public function testCardReference()
    {
        $this->assertSame($this->request, $this->request->setCardReference('supt_ca67zN30E7YEE1etcQabwo4g'));
        $this->assertSame('supt_ca67zN30E7YEE1etcQabwo4g', $this->request->getCardReference());
    }

    public function testCardToken()
    {
        $this->assertSame($this->request, $this->request->setToken('supt_ca67zN30E7YEE1etcQabwo4g'));
        $this->assertSame('supt_ca67zN30E7YEE1etcQabwo4g', $this->request->getToken());
    }

    public function testCardData()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getCardData();

        $this->assertSame($card['number'], $data['number']);
        $this->assertSame($card['cvv'], $data['cvc']);
    }

    public function testCardDataEmptyCvv()
    {
        $card = $this->getValidCard();
        $card['cvv'] = '';
        $this->request->setCard($card);
        $data = $this->request->getCardData();

        $this->assertTrue(empty($data['cvv']));
    }


}
