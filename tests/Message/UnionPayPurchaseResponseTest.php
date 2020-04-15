<?php

namespace Omnipay\Cathaybk\Message;

use DOMDocument;
use DOMNode;
use Omnipay\Tests\TestCase;

class UnionPayPurchaseResponseTest extends TestCase
{
    public function testSuccess()
    {
        $parameters = [
            'STOREID' => uniqid('store_id'),
            'CAVALUE' => uniqid('ca_value'),
            'ORDERNUMBER' => uniqid('order_number'),
            'AMOUNT' => '10.00',
        ];

        $response = new UnionPayPurchaseResponse($this->getMockRequest(), $parameters);

        $data = $response->getRedirectData();

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://sslpayment.uwccb.com.tw/EPOSService/UPOPPayment/OrderInitial.aspx', $response->getRedirectUrl());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertArrayHasKey('strRqXML', $data);
        $this->assertFalse(strpos($data['strRqXML'], 'TRS000'), 'strRqXML does not has TRS000');

        $expected = $this->getDocument(file_get_contents(__DIR__ . '/../fixtures/unionpay.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        $this->assertEqualXMLStructure($expected, $actual);
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
