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
        $parameters = $this->givenParameters();
        $signature = Helper::caValue(
            $parameters,
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY']
        );

        $request = new UnionPayPurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        self::assertEquals($signature, $data['CAVALUE']);
        self::assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        self::assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['ORDERINFO']['AMOUNT']);
        self::assertArrayNotHasKey('LANGUAGE', $data['ORDERINFO']);
    }

    public function testRedirect()
    {
        $gateway = new UnionPayGateway($this->getHttpClient(), $this->getHttpRequest());
        $response = $gateway->purchase($this->givenParameters())->send();

        self::assertInstanceOf(UnionPayPurchaseResponse::class, $response);
        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->isRedirect());
    }

    /**
     * @param array $parameters
     * @return array
     */
    private function givenParameters($parameters = [])
    {
        return array_merge([
            'STOREID' => uniqid('store_id', true),
            'CUBKEY' => uniqid('cub_key', true),
            'ORDERNUMBER' => strtoupper(uniqid('order_number', true)),
            'LANGUAGE' => 'zh-tw',
            'AMOUNT' => '10',
        ], $parameters);
    }
}
