<?php

namespace Omnipay\Cathaybk\Tests\Message;

use GuzzleHttp\Psr7\Response;
use Omnipay\Cathaybk\Message\CaptureRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Tests\TestCase;

class CaptureRequestTest extends TestCase
{
    public function testGetData()
    {
        $parameters = $this->givenParameters();
        $caValue = Helper::caValue($parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($parameters, 'ORD0005', [
                'STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0005', $data['MSGID']);
        self::assertEquals($parameters['STOREID'], $data['CAPTUREORDERINFO']['STOREID']);
        self::assertEquals($parameters['ORDERNUMBER'], $data['CAPTUREORDERINFO']['ORDERNUMBER']);
        self::assertEquals($parameters['AUTHCODE'], $data['CAPTUREORDERINFO']['AUTHCODE']);

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

        self::assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl',
            (string) $lastRequest->getUri()
        );
        self::assertEquals(
            http_build_query(['strRqXML' => Helper::array2xml($data)]),
            (string) $lastRequest->getBody()
        );
        self::assertEquals($data['CAPTUREORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        self::assertEquals('0000', $response->getCode());
    }

    public function testGetDataByCancel()
    {
        $parameters = $this->givenParameters(['cancel' => true]);
        $caValue = Helper::caValue($parameters, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($parameters, 'ORD0006', [
                'STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0006', $data['MSGID']);
        self::assertEquals($parameters['STOREID'], $data['CANCELCAPTUREINFO']['STOREID']);
        self::assertEquals($parameters['ORDERNUMBER'], $data['CANCELCAPTUREINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['CANCELCAPTUREINFO']['AMOUNT']);

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

        self::assertEquals(
            'https://sslpayment.uwccb.com.tw/EPOSService/CRDOrderService.asmx?wsdl',
            (string) $lastRequest->getUri()
        );
        self::assertEquals(
            http_build_query(['strRqXML' => Helper::array2xml($data)]),
            (string) $lastRequest->getBody()
        );

        self::assertEquals($data['CANCELCAPTUREINFO']['AUTHCODE'], $response->getTransactionReference());
        self::assertEquals('0000', $response->getCode());
        self::assertEquals(true, $response->isCancelled());
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
            'AUTHCODE' => uniqid('auth_code', true),
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
        $section = $msgId === 'ORD0005' ? 'CAPTUREORDERINFO' : 'CANCELCAPTUREINFO';

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => $msgId,
                'CAVALUE' => Helper::caValue(array_merge($parameters, $status), $signKeys),
                $section => [
                    'STOREID' => $parameters['STOREID'],
                    'ORDERNUMBER' => $parameters['ORDERNUMBER'],
                    'AMOUNT' => $parameters['AMOUNT'],
                    'AUTHCODE' => $parameters['AUTHCODE'],
                    'STATUS' => $status['STATUS'],
                ],
            ],
        ]);
    }
}
