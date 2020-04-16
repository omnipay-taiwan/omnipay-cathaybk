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
        $parameters = $this->givenParameters();
        $signature = Helper::signSignature(
            array_merge($parameters, ['LANGUAGE' => 'ZH-TW']), ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'LANGUAGE', 'CUBKEY']
        );

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($signature, $data['CAVALUE']);
        $this->assertEquals('TRS0004', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        $this->assertEquals('10', $data['ORDERINFO']['AMOUNT']);
        $this->assertEquals('ZH-TW', $data['ORDERINFO']['LANGUAGE']);
    }

    public function testGetPeriodNumberData()
    {
        $parameters = $this->givenParameters([
            'LANGUAGE' => 'EN-US',
            'PERIODNUMBER' => '2',
        ]);
        $signature = Helper::signSignature(
            $parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'PERIODNUMBER', 'LANGUAGE', 'CUBKEY']
        );

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($signature, $data['CAVALUE']);
        $this->assertEquals('TRS0005', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        $this->assertEquals('10', $data['ORDERINFO']['AMOUNT']);
        $this->assertEquals('2', $data['ORDERINFO']['PERIODNUMBER']);
        $this->assertEquals('EN-US', $data['ORDERINFO']['LANGUAGE']);
    }

    public function testRedirect()
    {
        $response = $this->gateway->purchase($this->givenParameters())->send();

        $this->assertInstanceOf(PurchaseResponse::class, $response);
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
            'AMOUNT' => '10',
        ], $parameters);
    }
}
