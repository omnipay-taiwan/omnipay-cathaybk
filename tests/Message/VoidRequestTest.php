<?php

namespace Omnipay\Cathaybk\Tests\Message;

use GuzzleHttp\Psr7\Response;
use Omnipay\Cathaybk\Message\VoidRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Tests\TestCase;

class VoidRequestTest extends TestCase
{
    public function testGetData()
    {
        $options = $this->givenOptions();
        $caValue = Helper::caValue($options, ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'CUBKEY']);

        $mockClient = $this->getMockClient();
        $mockClient->addResponse(
            new Response(200, [], $this->generateResponseXML($options))
        );

        $request = new VoidRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals($caValue, $data['CAVALUE']);
        self::assertEquals('ORD0007', $data['MSGID']);
        self::assertEquals($options['STOREID'], $data['CANCELORDERINFO']['STOREID']);
        self::assertEquals($options['ORDERNUMBER'], $data['CANCELORDERINFO']['ORDERNUMBER']);
        self::assertEquals($options['AUTHCODE'], $data['CANCELORDERINFO']['AUTHCODE']);

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
        self::assertEquals($data['CANCELORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        self::assertEquals('0000', $response->getCode());
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
     * @return string
     */
    private function generateResponseXML(array $options)
    {
        $status = ['STATUS' => '0000'];

        return Helper::array2xml([
            'CUBXML' => [
                'MSGID' => 'ORD0007',
                'CAVALUE' => Helper::caValue(
                    array_merge($options, $status),
                    ['STOREID', 'ORDERNUMBER', 'AUTHCODE', 'STATUS', 'CUBKEY']
                ),
                'CANCELORDERINFO' => [
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
