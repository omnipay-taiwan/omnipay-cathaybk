<?php

namespace Omnipay\Cathaybk\Tests\Message;

use GuzzleHttp\Psr7\Response;
use Omnipay\Cathaybk\Message\Helper;
use Omnipay\Cathaybk\Message\VoidRequest;
use Omnipay\Tests\TestCase;

class VoidRequestTest extends TestCase
{
    public function testGetData()
    {
        $parameters = $this->givenParameters();
        $caValue = Helper::caValue($parameters, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($parameters))
        );

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals($caValue, $data['CAVALUE']);
        $this->assertEquals('ORD0007', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['CANCELORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['CANCELORDERINFO']['ORDERNUMBER']);
        $this->assertEquals($parameters['AUTHCODE'], $data['CANCELORDERINFO']['AUTHCODE']);

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
        $this->assertEquals($data['CANCELORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        $this->assertEquals('0000', $response->getCode());
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
     * @return string
     */
    private function generateResponseXML(array $parameters)
    {
        $status = ['STATUS' => '0000'];

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => 'ORD0007',
                'CAVALUE' => Helper::caValue(
                    array_merge($parameters, $status),
                    ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY']
                ),
                'CANCELORDERINFO' => [
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
