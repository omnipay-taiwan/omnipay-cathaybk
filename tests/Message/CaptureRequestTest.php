<?php

namespace Omnipay\Cathaybk\Tests\Message;

use GuzzleHttp\Psr7\Response;
use Omnipay\Cathaybk\Message\CaptureRequest;
use Omnipay\Cathaybk\Message\Helper;
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
                'STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY'
            ]))
        );

        $request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($caValue, $data['CAVALUE']);
        $this->assertEquals('ORD0005', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['CAPTUREORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['CAPTUREORDERINFO']['ORDERNUMBER']);
        $this->assertEquals($parameters['AUTHCODE'], $data['CAPTUREORDERINFO']['AUTHCODE']);

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
        $this->assertEquals($data['CAPTUREORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        $this->assertEquals('0000', $response->getCode());
    }

    public function testGetDataByCancel()
    {
        $parameters = $this->givenParameters(['cancel' => true]);
        $caValue = Helper::caValue($parameters, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($parameters, 'ORD0006', [
                'STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY'
            ]))
        );

        $request = new CaptureRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($caValue, $data['CAVALUE']);
        $this->assertEquals('ORD0006', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['CAPTUREORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['CAPTUREORDERINFO']['ORDERNUMBER']);
        $this->assertEquals('10', $data['CAPTUREORDERINFO']['AMOUNT']);

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

        $this->assertEquals($data['CAPTUREORDERINFO']['AUTHCODE'], $response->getTransactionReference());
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

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => $msgId,
                'CAVALUE' => Helper::caValue(array_merge($parameters, $status), $signKeys),
                'CAPTUREORDERINFO' => [
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
