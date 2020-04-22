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

        $this->assertEquals($signature, $data['CAVALUE']);
        $this->assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        $this->assertEquals('10', $data['ORDERINFO']['AMOUNT']);
        $this->assertArrayNotHasKey('LANGUAGE', $data['ORDERINFO']);
    }

    public function testRedirect()
    {
        $gateway = new UnionPayGateway($this->getHttpClient(), $this->getHttpRequest());
        $response = $gateway->purchase($this->givenParameters())->send();

        $this->assertInstanceOf(UnionPayPurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
    }

    /**
     * @param array $parameters
     * @return array
     */
    private function givenParameters($parameters = [])
    {
        return array_merge([
            'STOREID' => uniqid('store_id'),
            'CUBKEY' => uniqid('cub_key'),
            'ORDERNUMBER' => strtoupper(uniqid('order_number')),
            'LANGUAGE' => 'zh-tw',
            'AMOUNT' => '10',
        ], $parameters);
    }
}
