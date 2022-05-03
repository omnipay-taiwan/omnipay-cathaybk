<?php

namespace Omnipay\Cathaybk\Tests;

use Omnipay\Cathaybk\Gateway;
use Omnipay\Cathaybk\Message\AcceptNotificationRequest;
use Omnipay\Cathaybk\Message\CompletePurchaseRequest;
use Omnipay\Cathaybk\Message\FetchTransactionRequest;
use Omnipay\Cathaybk\Message\PurchaseRequest;
use Omnipay\Cathaybk\Message\RefundRequest;
use Omnipay\Cathaybk\Message\VoidRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var string
     */
    private $storeId;
    /**
     * @var string
     */
    private $cubKey;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->initialize([
            'store_id' => $this->storeId = uniqid('store_id', true),
            'cub_key' => $this->cubKey = uniqid('cub_key', true),
        ]);
    }

    public function testPurchase()
    {
        $options = [
            'transaction_id' => uniqid('transaction_id', true),
            'amount' => '10.00',
        ];

        $request = $this->gateway->purchase($options);

        self::assertInstanceOf(PurchaseRequest::class, $request);
        $data = $request->getData();

        self::assertArrayHasKey('AMOUNT', $data['ORDERINFO']);
        self::assertArrayHasKey('ORDERNUMBER', $data['ORDERINFO']);
        self::assertArrayHasKey('LANGUAGE', $data['ORDERINFO']);
    }

    public function testGetAcceptNotificationWhenCompletePurchaseHasStrRsXML()
    {
        $options = ['strRsXML' => 'foo'];
        $request = $this->gateway->completePurchase($options);
        self::assertInstanceOf(AcceptNotificationRequest::class, $request);
    }

    public function testCompletePurchase()
    {
        $xmlData = $this->generateXmlData();

        $options = ['strOrderInfo' => Helper::array2xml($xmlData)];

        $request = $this->gateway->completePurchase($options);
        $response = $request->send();

        self::assertInstanceOf(CompletePurchaseRequest::class, $request);
        self::assertArrayHasKey('CUBXML', $request->getData());
        self::assertEquals($xmlData['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
    }

    public function testAcceptNotification()
    {
        $options = [];
        $request = $this->gateway->acceptNotification($options);
        self::assertInstanceOf(AcceptNotificationRequest::class, $request);
    }

    public function testFetchTransaction()
    {
        $options = [];
        $request = $this->gateway->fetchTransaction($options);
        self::assertInstanceOf(FetchTransactionRequest::class, $request);
    }

    public function testRefund()
    {
        $options = [];
        $request = $this->gateway->refund($options);
        self::assertInstanceOf(RefundRequest::class, $request);
    }

    public function testVoid()
    {
        $options = [];
        $request = $this->gateway->void($options);
        self::assertInstanceOf(VoidRequest::class, $request);
    }

    /**
     * @return array
     */
    private function generateXmlData()
    {
        $options = ['CUBXML' => [
            'CAVALUE' => '',
            'ORDERINFO' => [
                'STOREID' => $this->storeId,
                'ORDERNUMBER' => uniqid('order_number', true),
            ],
        ]];
        $options['CUBXML']['CAVALUE'] = Helper::caValue(array_merge(
            $options,
            ['STOREID' => $this->storeId, 'CUBKEY' => $this->cubKey]
        ), ['STOREID', 'ORDERNUMBER', 'CUBKEY']);

        return $options;
    }
}
