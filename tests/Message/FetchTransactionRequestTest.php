<?php

namespace Omnipay\Cathaybk\Message;

use GuzzleHttp\Psr7\Response;
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

        $this->assertEquals($caValue, $data['CAVALUE']);
        $this->assertEquals('ORD0001', $data['MSGID']);
        $this->assertEquals($parameters['STOREID'], $data['ORDERINFO']['STOREID']);
        $this->assertEquals($parameters['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        $this->assertEquals('10', $data['ORDERINFO']['AMOUNT']);

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
