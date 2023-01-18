<?php

namespace Omnipay\Cathaybk\Tests\Message;

use Omnipay\Cathaybk\Message\UnionPayPurchaseRequest;
use Omnipay\Cathaybk\Message\UnionPayPurchaseResponse;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Cathaybk\UnionPayGateway;
use Omnipay\Tests\TestCase;

class UnionPayPurchaseRequestTest extends TestCase
{
    public function testGetData()
    {
        $options = $this->givenOptions();
        $signature = Helper::caValue(
            $options,
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY']
        );

        $request = new UnionPayPurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals($signature, $data['CAVALUE']);
        self::assertEquals($options['STOREID'], $data['ORDERINFO']['STOREID']);
        self::assertEquals($options['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['ORDERINFO']['AMOUNT']);
        self::assertArrayNotHasKey('LANGUAGE', $data['ORDERINFO']);
    }

    public function testRedirect()
    {
        $gateway = new UnionPayGateway($this->getHttpClient(), $this->getHttpRequest());
        $response = $gateway->purchase($this->givenOptions())->send();

        self::assertInstanceOf(UnionPayPurchaseResponse::class, $response);
        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isRedirect());
    }

    /**
     * @param  array  $options
     * @return array
     */
    private function givenOptions($options = [])
    {
        return array_merge([
            'STOREID' => uniqid('store_id', true),
            'CUBKEY' => uniqid('cub_key', true),
            'ORDERNUMBER' => strtoupper(uniqid('order_number', true)),
            'LANGUAGE' => 'zh-tw',
            'AMOUNT' => '10',
        ], $options);
    }
}
