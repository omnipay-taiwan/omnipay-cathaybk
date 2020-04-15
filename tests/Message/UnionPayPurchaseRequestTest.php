<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\UnionPayGateway;
use Omnipay\Tests\TestCase;

class UnionPayPurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        $this->gateway = new UnionPayGateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testGetData()
    {
        $parameters = $this->givenParameters();
        $request = new UnionPayPurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertSame($parameters['store_id'], $data['STOREID']);
        $this->assertSame($parameters['order_number'], $data['ORDERNUMBER']);
        $this->assertSame('10.00', $data['AMOUNT']);
        $this->assertArrayNotHasKey('LANGUAGE', $data);
    }

    public function testRedirect()
    {
        $response = $this->gateway->purchase($this->givenParameters())->send();

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
            'store_id' => uniqid('store_id'),
            'cub_key' => uniqid('cub_key'),
            'order_number' => uniqid('order_number'),
            'language' => 'zh-tw',
            'amount' => '10.00',
        ], $parameters);
    }
}
