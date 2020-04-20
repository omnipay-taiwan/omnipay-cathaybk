<?php

namespace Omnipay\Cathaybk\Tests;

use Omnipay\Cathaybk\Message\AcceptNotificationRequest;
use Omnipay\Cathaybk\Message\CompletePurchaseRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Cathaybk\Message\UnionPayPurchaseRequest;
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

        $this->assertInstanceOf(UnionPayPurchaseRequest::class, $request);
        $data = $request->getData();

        $this->assertArrayHasKey('AMOUNT', $data['ORDERINFO']);
        $this->assertArrayHasKey('ORDERNUMBER', $data['ORDERINFO']);
        $this->assertArrayNotHasKey('LANGUAGE', $data['ORDERINFO']);
    }

    public function testCompletePurchase()
    {
        $parameters = ['CUBXML' => [
            'CAVALUE' => '',
            'ORDERINFO' => [
                'STOREID' => $this->storeId,
                'ORDERNUMBER' => uniqid('order_number'),
            ],
        ]];
        $parameters['CUBXML']['CAVALUE'] = Helper::caValue(array_merge(
            $parameters, ['STOREID' => $this->storeId, 'CUBKEY' => $this->cubKey]
        ), ['STOREID', 'ORDERNUMBER', 'CUBKEY']);

        $options = ['strOrderInfo' => Helper::array2xml($parameters)];

        $request = $this->gateway->completePurchase($options);
        $response = $request->send();

        $this->assertInstanceOf(CompletePurchaseRequest::class, $request);
        $this->assertArrayHasKey('CUBXML', $request->getData());
        $this->assertEquals($parameters['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
    }

    public function testAcceptNotification()
    {
        $options = [];
        $request = $this->gateway->acceptNotification($options);
        $this->assertInstanceOf(AcceptNotificationRequest::class, $request);
    }
}
