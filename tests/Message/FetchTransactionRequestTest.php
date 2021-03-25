<?php

namespace Omnipay\Cathaybk\Tests\Message;

use GuzzleHttp\Psr7\Response;
use Omnipay\Cathaybk\Message\FetchTransactionRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Tests\TestCase;

class FetchTransactionRequestTest extends TestCase
{
    public function testGetData()
    {
        $parameters = $this->givenParameters();
        $caValue = Helper::caValue($parameters, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($parameters))
        );

        $request = new FetchTransactionRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($parameters);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0001', $data['MSGID']);
        self::assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        self::assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['ORDERINFO']['AMOUNT']);

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

    /**
     * @param array $parameters
     * @return string
     */
    private function generateResponseXML(array $parameters)
    {
        $status = ['STATUS' => '0303'];

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => 'ORD0001',
                'CAVALUE' => Helper::caValue(
                    array_merge($parameters, $status),
                    ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY']
                ),
                'ORDERINFO' => [
                    'STOREID' => $parameters['STOREID'],
                    'ORDERNUMBER' => $parameters['ORDERNUMBER'],
                    'AMOUNT' => $parameters['AMOUNT'],
                    'STATUS' => $status['STATUS'],
                    'AUTHCODE' => '',
                    'AUTHTIME' => '',
                    'SETTLEAMOUNT' => '',
                    'SETTLETIME' => '',
                    'ORDERTIME' => '',
                ],
            ],
        ]);
    }
}
