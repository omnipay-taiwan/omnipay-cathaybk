<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Gateway;
use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testGetData()
    {
        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters = [
            'store_id' => $storeId = uniqid('store_id'),
            'cub_key' => $cubKey = uniqid('cub_key'),
            'transaction_id' => $transactionId = uniqid('order_number'),
            'language' => 'zh-tw',
            'amount' => '10.00',
        ]);

        $data = $request->getData();
        $this->assertSame($storeId, $data['STOREID']);
        $this->assertSame($cubKey, $data['CUBKEY']);
        $this->assertSame($transactionId, $data['ORDERNUMBER']);
        $this->assertSame('10.00', $data['AMOUNT']);
        $this->assertSame('ZH-TW', $data['LANGUAGE']);
    }

    public function testRedirect()
    {
        $options = [
            'store_id' => uniqid('store_id'),
            'cub_key' => uniqid('cub_key'),
            'order_number' => $orderNumber = uniqid('order_number'),
            'language' => 'zh-tw',
            'amount' => '10.00',
        ];

        $response = $this->gateway->purchase($options)->send();

        $this->assertInstanceOf(PurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
    }
}
