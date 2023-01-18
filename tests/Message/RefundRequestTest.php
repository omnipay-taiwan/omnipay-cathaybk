<?php

namespace Omnipay\Cathaybk\Tests\Message;

use GuzzleHttp\Psr7\Response;
use Omnipay\Cathaybk\Message\RefundRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Tests\TestCase;

class RefundRequestTest extends TestCase
{
    public function testGetData()
    {
        $options = $this->givenOptions();
        $caValue = Helper::caValue($options, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($options, 'ORD0003', [
                'STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0003', $data['MSGID']);
        self::assertEquals($options['STOREID'], $data['REFUNDORDERINFO']['STOREID']);
        self::assertEquals($options['ORDERNUMBER'], $data['REFUNDORDERINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['REFUNDORDERINFO']['AMOUNT']);

        return [$request->send(), $mockClient, $data];
    }

    /**
     * @depends testGetData
     *
     * @param $options
     */
    public function testResponse($options)
    {
        [$response, $mockClient, $data] = $options;
        $lastRequest = $mockClient->getLastRequest();

        self::assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl',
            (string) $lastRequest->getUri()
        );
        self::assertEquals(
            http_build_query(['strRqXML' => Helper::array2xml($data)]),
            (string) $lastRequest->getBody()
        );

        self::assertEquals($data['REFUNDORDERINFO']['AUTHCODE'], $response->getTransactionReference());
        self::assertEquals('0000', $response->getCode());
    }

    public function testGetDataByCancel()
    {
        $options = $this->givenOptions(['cancel' => true]);
        $caValue = Helper::caValue($options, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($options, 'ORD0004', [
                'STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0004', $data['MSGID']);
        self::assertEquals($options['STOREID'], $data['CANCELREFUNDINFO']['STOREID']);
        self::assertEquals($options['ORDERNUMBER'], $data['CANCELREFUNDINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['CANCELREFUNDINFO']['AMOUNT']);

        return [$request->send(), $mockClient, $data];
    }

    /**
     * @depends testGetDataByCancel
     *
     * @param $options
     */
    public function testResponseByCancel($options)
    {
        [$response, $mockClient, $data] = $options;
        $lastRequest = $mockClient->getLastRequest();

        self::assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl',
            (string) $lastRequest->getUri()
        );
        self::assertEquals(
            http_build_query(['strRqXML' => Helper::array2xml($data)]),
            (string) $lastRequest->getBody()
        );

        self::assertEquals($data['CANCELREFUNDINFO']['AUTHCODE'], $response->getTransactionReference());
        self::assertEquals('0000', $response->getCode());
        self::assertEquals(true, $response->isCancelled());
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
            'AMOUNT' => '10',
            'AUTHCODE' => uniqid('auth_code', true),
        ], $options);
    }

    /**
     * @param  array  $options
     * @param  string  $msgId
     * @param  array  $signKeys
     * @return string
     */
    private function generateResponseXML(array $options, $msgId, $signKeys)
    {
        $status = ['STATUS' => '0000'];
        $section = $msgId === 'ORD0003' ? 'REFUNDORDERINFO' : 'CANCELREFUNDINFO';

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => $msgId,
                'CAVALUE' => Helper::caValue(array_merge($options, $status), $signKeys),
                $section => array_merge([
                    'STOREID' => $options['STOREID'],
                    'ORDERNUMBER' => $options['ORDERNUMBER'],
                    'AMOUNT' => $options['AMOUNT'],
                    'AUTHCODE' => $options['AUTHCODE'],
                ], $status),
            ],
        ]);
    }
}
