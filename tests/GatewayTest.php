<?php

namespace Omnipay\Cathaybk;

use Omnipay\Cathaybk\Message\AcceptNotificationRequest;
use Omnipay\Cathaybk\Message\CompletePurchaseRequest;
use Omnipay\Cathaybk\Message\Helper;
use Omnipay\Cathaybk\Message\PurchaseRequest;
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
    /**
     * @var Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->initialize([
            'store_id' => $this->storeId = uniqid('store_id'),
            'cub_key' => $this->cubKey = uniqid('cub_key'),
        ]);
    }

    public function testCapture()
    {
        $options = [
            'transaction_id' => uniqid('transaction_id'),
            'amount' => '10.00',
        ];

        $request = $this->gateway->purchase($options);

        $this->assertInstanceOf(PurchaseRequest::class, $request);
        $this->assertArrayHasKey('AMOUNT', $request->getData());
        $this->assertArrayHasKey('ORDERNUMBER', $request->getData());
    }

    public function testCaptureComplete()
    {
        $parameters = ['CUBXML' => [
            'CAVALUE' => '',
            'ORDERINFO' => [
                'STOREID' => $this->storeId,
                'ORDERNUMBER' => uniqid('order_number'),
            ],
        ]];
        $parameters['CUBXML']['CAVALUE'] = Helper::signSignature(array_merge(
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
