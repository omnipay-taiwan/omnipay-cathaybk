<?php

namespace Omnipay\Cathaybk\Tests\Message;

use Omnipay\Cathaybk\Message\UnionPayPurchaseResponse;
use Omnipay\Tests\TestCase;

class UnionPayPurchaseResponseTest extends TestCase
{
    public function testSuccess()
    {
        $options = [
            'CAVALUE' => uniqid('ca_value', true),
            'ORDERINFO' => [
                'STOREID' => uniqid('store_id', true),
                'ORDERNUMBER' => strtoupper(uniqid('order_number', true)),
                'AMOUNT' => '10',
            ],
        ];

        $response = new UnionPayPurchaseResponse($this->getMockRequest(), $options);

        $data = $response->getRedirectData();

        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isRedirect());
        self::assertEquals('https://sslpayment.uwccb.com.tw/EPOSService/UPOPPayment/OrderInitial.aspx',
            $response->getRedirectUrl());
        self::assertEquals('POST', $response->getRedirectMethod());
        self::assertArrayHasKey('strRqXML', $data);
        self::assertFalse(strpos($data['strRqXML'], 'TRS000'), 'strRqXML does not has TRS000');

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/unionpay.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        self::assertEquals($expected, $actual);
    }

    /**
     * @param  string  $xml
     * @return array
     */
    private function getDocument($xml)
    {
        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $xml, $values, $index);
        xml_parser_free($parser);

        return $index;
    }
}
