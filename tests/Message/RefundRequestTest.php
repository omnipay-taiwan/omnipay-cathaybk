<?php

namespace Omnipay\Cathaybk\Tests\Message;

use GuzzleHttp\Psr7\Response;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Cathaybk\Message\RefundRequest;
use Omnipay\Tests\TestCase;

class RefundRequestTest extends TestCase
{
    public function testGetData()
    {
        $parameters = $this->givenParameters();
        $caValue = Helper::caValue($parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($parameters, 'ORD0003', [
                'STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($caValue, $data['CAVALUE']);
        $this->assertEquals('ORD0003', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['REFUNDORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['REFUNDORDERINFO']['ORDERNUMBER']);
        $this->assertEquals('10', $data['REFUNDORDERINFO']['AMOUNT']);

        return [$request->send(), $mockClient, $data];
    }

    /**
     * @depends testGetData
     * @param $parameters
     */
    public function testResponse($parameters)
    {
        list($response, $mockClient, $data) = $parameters;
        $lastRequest = $mockClient->getLastRequest();

        $this->assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl',
            (string) $lastRequest->getUri()
        );
        $this->assertEquals(
            http_build_query(['strRqXML' => Helper::array2xml($data)]),
            $lastRequest->getBody()->getContents()
        );

        $this->assertEquals($data['REFUNDORDERINFO']['AUTHCODE'], $response->getTransactionReference());
        $this->assertEquals('0000', $response->getCode());
    }

    public function testGetDataByCancel()
    {
        $parameters = $this->givenParameters(['cancel' => true]);
        $caValue = Helper::caValue($parameters, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($parameters, 'ORD0004', [
                'STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($caValue, $data['CAVALUE']);
        $this->assertEquals('ORD0004', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['CANCELREFUNDINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['CANCELREFUNDINFO']['ORDERNUMBER']);
        $this->assertEquals('10', $data['CANCELREFUNDINFO']['AMOUNT']);

        return [$request->send(), $mockClient, $data];
    }

    /**
     * @depends testGetDataByCancel
     * @param $parameters
     */
    public function testResponseByCancel($parameters)
    {
        list($response, $mockClient, $data) = $parameters;
        $lastRequest = $mockClient->getLastRequest();

        $this->assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl',
            (string) $lastRequest->getUri()
        );
        $this->assertEquals(
            http_build_query(['strRqXML' => Helper::array2xml($data)]),
            $lastRequest->getBody()->getContents()
        );

        $this->assertEquals($data['CANCELREFUNDINFO']['AUTHCODE'], $response->getTransactionReference());
        $this->assertEquals('0000', $response->getCode());
        $this->assertEquals(true, $response->isCancelled());
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
            'AUTHCODE' => uniqid('auth_code'),
        ], $parameters);
    }

    /**
     * @param array $parameters
     * @param string $msgId
     * @param array $signKeys
     * @return string
     */
    private function generateResponseXML(array $parameters, $msgId, $signKeys)
    {
        $status = ['STATUS' => '0000'];
        $section = $msgId === 'ORD0003' ? 'REFUNDORDERINFO' : 'CANCELREFUNDINFO';

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => $msgId,
                'CAVALUE' => Helper::caValue(array_merge($parameters, $status), $signKeys),
                $section => array_merge([
                    'STOREID' => $parameters['STOREID'],
                    'ORDERNUMBER' => $parameters['ORDERNUMBER'],
                    'AMOUNT' => $parameters['AMOUNT'],
                    'AUTHCODE' => $parameters['AUTHCODE'],
                ], $status),
            ],
        ]);
    }
}
