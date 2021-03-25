<?php

namespace Omnipay\Cathaybk\Tests;

use Omnipay\Cathaybk\Message\AcceptNotificationRequest;
use Omnipay\Cathaybk\Message\CompletePurchaseRequest;
use Omnipay\Cathaybk\Message\UnionPayPurchaseRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Cathaybk\UnionPayGateway;
use Omnipay\Tests\GatewayTestCase;

class UnionPayGatewayTest extends GatewayTestCase
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

        $this->gateway = new UnionPayGateway($this->getHttpClient(), $this->getHttpRequest());
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

        self::assertInstanceOf(UnionPayPurchaseRequest::class, $request);
        $data = $request->getData();

        self::assertArrayHasKey('AMOUNT', $data['ORDERINFO']);
        self::assertArrayHasKey('ORDERNUMBER', $data['ORDERINFO']);
        self::assertArrayNotHasKey('LANGUAGE', $data['ORDERINFO']);
    }

    public function testCompletePurchase()
    {
        $parameters = ['CUBXML' => [
            'CAVALUE' => '',
            'ORDERINFO' => [
                'STOREID' => $this->storeId,
                'ORDERNUMBER' => uniqid('order_number', true),
            ],
        ]];
        $parameters['CUBXML']['CAVALUE'] = Helper::caValue(array_merge(
            $parameters,
            ['STOREID' => $this->storeId, 'CUBKEY' => $this->cubKey]
        ), ['STOREID', 'ORDERNUMBER', 'CUBKEY']);

        $options = ['strOrderInfo' => Helper::array2xml($parameters)];

        $request = $this->gateway->completePurchase($options);
        $response = $request->send();

        self::assertInstanceOf(CompletePurchaseRequest::class, $request);
        self::assertArrayHasKey('CUBXML', $request->getData());
        self::assertEquals($parameters['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
    }

    public function testAcceptNotification()
    {
        $options = [];
        $request = $this->gateway->acceptNotification($options);
        self::assertInstanceOf(AcceptNotificationRequest::class, $request);
    }
}
