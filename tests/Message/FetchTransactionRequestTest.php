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
        $options = $this->givenOptions();
        $caValue = Helper::caValue($options, ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($options))
        );

        $request = new FetchTransactionRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0001', $data['MSGID']);
        self::assertEquals($options['STOREID'], $data['ORDERINFO']['STOREID']);
        self::assertEquals($options['ORDERNUMBER'], $data['ORDERINFO']['ORDERNUMBER']);
        self::assertEquals('10', $data['ORDERINFO']['AMOUNT']);

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
        ], $options);
    }

    /**
     * @param  array  $options
     * @return string
     */
    private function generateResponseXML(array $options)
    {
        $status = ['STATUS' => '0303'];

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => 'ORD0001',
                'CAVALUE' => Helper::caValue(
                    array_merge($options, $status),
                    ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'STATUS', 'CUBKEY']
                ),
                'ORDERINFO' => [
                    'STOREID' => $options['STOREID'],
                    'ORDERNUMBER' => $options['ORDERNUMBER'],
                    'AMOUNT' => $options['AMOUNT'],
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
