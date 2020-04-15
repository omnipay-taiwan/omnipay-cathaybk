<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Gateway;
use Omnipay\Tests\TestCase;

class AcceptNotificationRequestTest extends TestCase
{
    public function setUp()
    {
        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testGetData()
    {
        $storeId = uniqid('store_id');
        $cubKey = uniqid('cub_key');
        $returnUrl = 'https://foo.bar/return-url';
        $parameters = [
            'CUBXML' => [
                'CAVALUE' => '',
                'ORDERINFO' => [
                    'STOREID' => $storeId,
                    'ORDERNUMBER' => uniqid('order_number'),
                    'AMOUNT' => '10.00',
                    'LANGUAGE' => 'ZH-TW',
                ],
                'AUTHINFO' => [
                    'AUTHSTATUS' => '0000',
                    'AUTHCODE' => uniqid('auth_code'),
                    'AUTHTIME' => date('YmdHis'),
                    'AUTHMSG' => '授權成功',
                    'CARDNO' => uniqid('card_no'),
                ],
            ],
        ];

        $parameters['CUBXML']['CAVALUE'] = Helper::signSignature(
            array_merge(['STOREID' => $storeId, 'CUBKEY' => $cubKey], $parameters),
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY']
        );

        $request = new AcceptNotificationRequest($this->getHttpClient(), $this->getHttpRequest());
        $request->initialize([
            'store_id' => $storeId,
            'cub_key' => $cubKey,
            'returnUrl' => $returnUrl,
            'strRsXML' => $strRsXML = Helper::array2xml($parameters),
        ]);

        $data = $request->getData();
        $this->assertEquals(array_merge([
            'STOREID' => $storeId,
            'CUBKEY' => $cubKey,
            'RETURL' => $returnUrl,
        ], $parameters), $data);
    }
}
