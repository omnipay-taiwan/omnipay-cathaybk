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
        $signature = Helper::signSignature(
            $parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY']
        );

        $request = new UnionPayRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($parameters['STOREID'], $data['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['ORDERNUMBER']);
        $this->assertEquals('10', $data['AMOUNT']);
        $this->assertEquals($signature, $data['CAVALUE']);
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
            'STOREID' => uniqid('store_id'),
            'CUBKEY' => uniqid('cub_key'),
            'ORDERNUMBER' => strtoupper(uniqid('order_number')),
            'LANGUAGE' => 'zh-tw',
            'AMOUNT' => '10',
        ], $parameters);
    }
}
