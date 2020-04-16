<?php

namespace Omnipay\Cathaybk\Message;

use DOMDocument;
use DOMNode;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Tests\TestCase;

class PurchaseResponseTest extends TestCase
{
    public function testSuccess()
    {
        $parameters = $this->givenParameters('TRS0004', ['LANGUAGE' => 'ZH-TW']);
        $request = $this->getMockRequest();
        $request->shouldReceive('getTestMode')->andReturnFalse();
        $response = new PurchaseResponse($request, $parameters);

        $data = $response->getRedirectData();

        $this->assertInstanceOf(RedirectResponseInterface::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx',
            $response->getRedirectUrl()
        );
        $this->assertArrayHasKey('strRqXML', $data);

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/normal.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        $this->assertEqualXMLStructure($expected, $actual);
    }

    public function testPeriodNumberSuccess()
    {
        $parameters = $this->givenParameters('TRS0005', ['PERIODNUMBER' => '2', 'LANGUAGE' => 'EN-US']);
        $request = $this->getMockRequest();
        $request->shouldReceive('getTestMode')->andReturnFalse();
        $response = new PurchaseResponse($request, $parameters);

        $data = $response->getRedirectData();

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx', $response->getRedirectUrl());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertArrayHasKey('strRqXML', $data);

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/period.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        $this->assertEqualXMLStructure($expected, $actual);
    }

    public function testSetTestMode()
    {
        $parameters = $this->givenParameters('TRS0004', ['LANGUAGE' => 'ZH-TW']);
        $request = $this->getMockRequest();
        $request->shouldReceive('getTestMode')->andReturnTrue();
        $response = new PurchaseResponse($request, $parameters);

        $this->assertEquals(
            'https://sslpayment.cathaybkdev.com.tw/EPOSService/Payment/OrderInitial.aspx',
            $response->getRedirectUrl()
        );
    }

    /**
     * @param $msgId
     * @param array $orderInfo
     * @return array
     */
    private function givenParameters($msgId, $orderInfo = []): array
    {
        return [
            'CAVALUE' => uniqid('ca_value'),
            'MSGID' => $msgId,
            'ORDERINFO' => array_merge([
                'STOREID' => uniqid('store_id'),
                'ORDERNUMBER' => uniqid('order_number'),
                'AMOUNT' => '10',
            ], $orderInfo),
        ];
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
