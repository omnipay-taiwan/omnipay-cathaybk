<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Tests\TestCase;

class PurchaseResponseTest extends TestCase
{
    public function testSuccess()
    {
        $parameters = [
            'STOREID' => uniqid('store_id'),
            'CUBKEY' => uniqid('cub_key'),
            'ORDERNUMBER' => uniqid('order_number'),
            'LANGUAGE' => 'ZH-TW',
            'AMOUNT' => '10.00',
        ];

        $response = new PurchaseResponse($this->getMockRequest(), $parameters);

        $data = $response->getRedirectData();
        $signature = Helper::signSignature(
            $parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'LANGUAGE', 'CUBKEY']
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertArrayHasKey('strRqXML', $data);
        $this->assertNotFalse(strpos($data['strRqXML'], 'TRS0004'), 'strRqXML does not has TRS0004');
        $this->assertNotFalse(strpos($data['strRqXML'], $signature), 'strRqXML does not has '.$signature);
    }

    public function testPeriodNumberSuccess()
    {
        $parameters = [
            'STOREID' => uniqid('store_id'),
            'CUBKEY' => uniqid('cub_key'),
            'ORDERNUMBER' => uniqid('order_number'),
            'LANGUAGE' => 'ZH-TW',
            'AMOUNT' => '10.00',
            'PERIODNUMBER' => '2',
        ];

        $response = new PurchaseResponse($this->getMockRequest(), $parameters);

        $data = $response->getRedirectData();
        $signature = Helper::signSignature(
            $parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'PERIODNUMBER', 'LANGUAGE', 'CUBKEY']
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertArrayHasKey('strRqXML', $data);
        $this->assertNotFalse(strpos($data['strRqXML'], 'TRS0005'), 'strRqXML does not has TRS0004');
        $this->assertNotFalse(strpos($data['strRqXML'], $signature), 'strRqXML does not has '.$signature);
    }
}
