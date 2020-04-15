<?php

namespace Omnipay\Cathaybk\Message;

use DOMDocument;
use DOMNode;
use Omnipay\Tests\TestCase;

class PurchaseResponseTest extends TestCase
{
    public function testSuccess()
    {
        $parameters = $this->givenParameters();

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

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/normal.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        $this->assertEqualXMLStructure($expected, $actual);
    }

    public function testPeriodNumberSuccess()
    {
        $parameters = $this->givenParameters(['PERIODNUMBER' => '2']);

        $response = new PurchaseResponse($this->getMockRequest(), $parameters);

        $data = $response->getRedirectData();
        $signature = Helper::signSignature(
            $parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'PERIODNUMBER', 'LANGUAGE', 'CUBKEY']
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx', $response->getRedirectUrl());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertArrayHasKey('strRqXML', $data);
        $this->assertNotFalse(strpos($data['strRqXML'], 'TRS0005'), 'strRqXML does not has TRS0004');
        $this->assertNotFalse(strpos($data['strRqXML'], $signature), 'strRqXML does not has '.$signature);

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/period.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        $this->assertEqualXMLStructure($expected, $actual);
    }

    /**
     * @param array $parameters
     * @return array
     */
    private function givenParameters($parameters = []): array
    {
        return array_merge(
            [
                'STOREID' => uniqid('store_id'),
                'CUBKEY' => uniqid('cub_key'),
                'ORDERNUMBER' => uniqid('order_number'),
                'AMOUNT' => '10.00',
            ], $parameters, [
                'LANGUAGE' => 'ZH-TW',
            ]
        );
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
