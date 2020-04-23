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

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->initialize([
            'store_id' => $this->storeId = uniqid('store_id'),
            'cub_key' => $this->cubKey = uniqid('cub_key'),
        ]);
    }

    public function testPurchase()
    {
        $options = [
            'transaction_id' => uniqid('transaction_id'),
            'amount' => '10.00',
        ];

        $request = $this->gateway->purchase($options);

        $this->assertInstanceOf(PurchaseRequest::class, $request);
        $data = $request->getData();

        $this->assertArrayHasKey('AMOUNT', $data['ORDERINFO']);
        $this->assertArrayHasKey('ORDERNUMBER', $data['ORDERINFO']);
        $this->assertArrayHasKey('LANGUAGE', $data['ORDERINFO']);
    }

    public function testCompletePurchase()
    {
        $xmlData = $this->generateXmlData();

        $parameters = ['strOrderInfo' => Helper::array2xml($xmlData)];

        $request = $this->gateway->completePurchase($parameters);
        $response = $request->send();

        $this->assertInstanceOf(CompletePurchaseRequest::class, $request);
        $this->assertArrayHasKey('CUBXML', $request->getData());
        $this->assertEquals($xmlData['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
    }

    public function testAcceptNotification()
    {
        $options = [];
        $request = $this->gateway->acceptNotification($options);
        $this->assertInstanceOf(AcceptNotificationRequest::class, $request);
    }

    public function testFetchTransaction()
    {
        $options = [];
        $request = $this->gateway->fetchTransaction($options);
        $this->assertInstanceOf(FetchTransactionRequest::class, $request);
    }

    public function testRefund()
    {
        $options = [];
        $request = $this->gateway->refund($options);
        $this->assertInstanceOf(RefundRequest::class, $request);
    }

    public function testVoid()
    {
        $options = [];
        $request = $this->gateway->void($options);
        $this->assertInstanceOf(VoidRequest::class, $request);
    }

    /**
     * @return array
     */
    private function generateXmlData()
    {
        $parameters = ['CUBXML' => [
            'CAVALUE' => '',
            'ORDERINFO' => [
                'STOREID' => $this->storeId,
                'ORDERNUMBER' => uniqid('order_number'),
            ],
        ]];
        $parameters['CUBXML']['CAVALUE'] = Helper::caValue(array_merge(
            $parameters,
            ['STOREID' => $this->storeId, 'CUBKEY' => $this->cubKey]
        ), ['STOREID', 'ORDERNUMBER', 'CUBKEY']);

        return $parameters;
    }
}
