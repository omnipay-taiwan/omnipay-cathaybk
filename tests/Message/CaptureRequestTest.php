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
        $options = $this->givenOptions();
        $caValue = Helper::caValue($options, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($options, 'ORD0005', [
                'STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0005', $data['MSGID']);
        self::assertEquals($options['STOREID'], $data['CAPTUREORDERINFO']['STOREID']);
        self::assertEquals($options['ORDERNUMBER'], $data['CAPTUREORDERINFO']['ORDERNUMBER']);
        self::assertEquals($options['AUTHCODE'], $data['CAPTUREORDERINFO']['AUTHCODE']);

        return [$request->send(), $mockClient, $data];
    }

    /**
     * @depends testGetData
     * @param $options
     */
    public function testResponse($options)
    {
        list($response, $mockClient, $data) = $options;
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
        $options = $this->givenOptions(['cancel' => true]);
        $caValue = Helper::caValue($options, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($options, 'ORD0006', [
                'STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY',
            ]))
        );

        $request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0006', $data['MSGID']);
        self::assertEquals($options['STOREID'], $data['CANCELCAPTUREINFO']['STOREID']);
        self::assertEquals($options['ORDERNUMBER'], $data['CANCELCAPTUREINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['CANCELCAPTUREINFO']['AMOUNT']);

        return [$request->send(), $mockClient, $data];
    }

    /**
     * @depends testGetDataByCancel
     * @param $options
     */
    public function testResponseByCancel($options)
    {
        list($response, $mockClient, $data) = $options;
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
     * @param array $options
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
     * @param array $options
     * @param string $msgId
     * @param array $signKeys
     * @return string
     */
    private function generateResponseXML(array $options, $msgId, $signKeys)
    {
        $status = ['STATUS' => '0000'];
        $section = $msgId === 'ORD0005' ? 'CAPTUREORDERINFO' : 'CANCELCAPTUREINFO';

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => $msgId,
                'CAVALUE' => Helper::caValue(array_merge($options, $status), $signKeys),
                $section => [
                    'STOREID' => $options['STOREID'],
                    'ORDERNUMBER' => $options['ORDERNUMBER'],
                    'AMOUNT' => $options['AMOUNT'],
                    'AUTHCODE' => $options['AUTHCODE'],
                    'STATUS' => $status['STATUS'],
                ],
            ],
        ]);
    }
}
