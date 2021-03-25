<?php

namespace Omnipay\Cathaybk\Tests\Message;

use DOMDocument;
use DOMNode;
use Omnipay\Cathaybk\Message\UnionPayPurchaseResponse;
use Omnipay\Tests\TestCase;

class UnionPayPurchaseResponseTest extends TestCase
{
    public function testSuccess()
    {
        $parameters = [
            'CAVALUE' => uniqid('ca_value', true),
            'ORDERINFO' => [
                'STOREID' => uniqid('store_id', true),
                'ORDERNUMBER' => strtoupper(uniqid('order_number', true)),
                'AMOUNT' => '10',
            ],
        ];

        $response = new UnionPayPurchaseResponse($this->getMockRequest(), $parameters);

        $data = $response->getRedirectData();

        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isRedirect());
        self::assertEquals('https://sslpayment.uwccb.com.tw/EPOSService/UPOPPayment/OrderInitial.aspx', $response->getRedirectUrl());
        self::assertEquals('POST', $response->getRedirectMethod());
        self::assertArrayHasKey('strRqXML', $data);
        self::assertFalse(strpos($data['strRqXML'], 'TRS000'), 'strRqXML does not has TRS000');

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/unionpay.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        self::assertEqualXMLStructure($expected, $actual);
    }

    /**
     * @param string $xml
     * @return DOMNode|null
     */
    private function getDocument($xml)
    {
        $document = new DOMDocument();
        $document->loadXML($xml);

        return $document->firstChild;
    }
}
