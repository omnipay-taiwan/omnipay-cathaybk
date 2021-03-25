<?php

namespace Omnipay\Cathaybk\Tests\Message;

use Omnipay\Cathaybk\Gateway;
use Omnipay\Cathaybk\Message\PurchaseRequest;
use Omnipay\Cathaybk\Message\PurchaseResponse;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function testGetData()
    {
        $parameters = $this->givenParameters();
        $signature = Helper::caValue(
            array_merge($parameters, ['LANGUAGE' => 'ZH-TW']),
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'LANGUAGE', 'CUBKEY']
        );

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        self::assertEquals($signature, $data['CAVALUE']);
        self::assertEquals('TRS0004', $data['MSGID']);
        self::assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        self::assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['ORDERINFO']['AMOUNT']);
        self::assertEquals('ZH-TW', $data['ORDERINFO']['LANGUAGE']);
    }

    public function testGetPeriodNumberData()
    {
        $parameters = $this->givenParameters([
            'LANGUAGE' => 'EN-US',
            'PERIODNUMBER' => '2',
        ]);
        $signature = Helper::caValue(
            $parameters,
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'PERIODNUMBER', 'LANGUAGE', 'CUBKEY']
        );

        $request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        self::assertEquals($signature, $data['CAVALUE']);
        self::assertEquals('TRS0005', $data['MSGID']);
        self::assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        self::assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['ORDERINFO']['AMOUNT']);
        self::assertEquals('2', $data['ORDERINFO']['PERIODNUMBER']);
        self::assertEquals('EN-US', $data['ORDERINFO']['LANGUAGE']);
    }

    public function testRedirect()
    {
        $gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $response = $gateway->purchase($this->givenParameters())->send();

        self::assertInstanceOf(PurchaseResponse::class, $response);
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
            'AMOUNT' => '10',
        ], $parameters);
    }
}
