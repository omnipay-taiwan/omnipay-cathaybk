<?php

namespace Omnipay\Cathaybk\Tests\Message;

use DOMDocument;
use DOMNode;
use Omnipay\Cathaybk\Message\PurchaseResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Tests\TestCase;

class PurchaseResponseTest extends TestCase
{
    public function testSuccess()
    {
        $options = $this->givenOptions('TRS0004', ['LANGUAGE' => 'ZH-TW']);
        $request = $this->getMockRequest();
        $request->shouldReceive('getTestMode')->andReturnFalse();
        $response = new PurchaseResponse($request, $options);

        $data = $response->getRedirectData();

        self::assertInstanceOf(RedirectResponseInterface::class, $response);
        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isRedirect());
        self::assertEquals('POST', $response->getRedirectMethod());
        self::assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx',
            $response->getRedirectUrl()
        );
        self::assertArrayHasKey('strRqXML', $data);

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/normal.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        self::assertEqualXMLStructure($expected, $actual);
    }

    public function testPeriodNumberSuccess()
    {
        $options = $this->givenOptions('TRS0005', ['PERIODNUMBER' => '2', 'LANGUAGE' => 'EN-US']);
        $request = $this->getMockRequest();
        $request->shouldReceive('getTestMode')->andReturnFalse();
        $response = new PurchaseResponse($request, $options);

        $data = $response->getRedirectData();

        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isRedirect());
        self::assertEquals('https://sslpayment.uwccb.com.tw/EPOSService/Payment/OrderInitial.aspx', $response->getRedirectUrl());
        self::assertEquals('POST', $response->getRedirectMethod());
        self::assertArrayHasKey('strRqXML', $data);

        $expected = $this->getDocument(file_get_contents(__DIR__.'/../fixtures/period.xml'));
        $actual = $this->getDocument($data['strRqXML']);

        self::assertEqualXMLStructure($expected, $actual);
    }

    public function testSetTestMode()
    {
        $options = $this->givenOptions('TRS0004', ['LANGUAGE' => 'ZH-TW']);
        $request = $this->getMockRequest();
        $request->shouldReceive('getTestMode')->andReturnTrue();
        $response = new PurchaseResponse($request, $options);

        self::assertEquals(
            'https://sslpayment.cathaybkdev.com.tw/EPOSService/Payment/OrderInitial.aspx',
            $response->getRedirectUrl()
        );
    }

    /**
     * @param $msgId
     * @param  array  $orderInfo
     * @return array
     */
    private function givenOptions($msgId, $orderInfo = [])
    {
        return [
            'CAVALUE' => uniqid('ca_value', true),
            'MSGID' => $msgId,
            'ORDERINFO' => array_merge([
                'STOREID' => uniqid('store_id', true),
                'ORDERNUMBER' => uniqid('order_number', true),
                'AMOUNT' => '10',
            ], $orderInfo),
        ];
    }

    /**
     * @param  string  $xml
     * @return DOMNode|null
     */
    private function getDocument($xml)
    {
        $document = new DOMDocument();
        $document->loadXML($xml);

        return $document->firstChild;
    }
}
