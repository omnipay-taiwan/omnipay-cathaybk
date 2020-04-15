<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Tests\TestCase;

class AcceptNotificationResponseTest extends TestCase
{
    public function testSuccess()
    {
        $storeId = uniqid('store-id');
        $cubKey = uniqid('cub_key');
        $returnUrl = 'https://foo.bar/return-url';
        $parameters = [
            'STOREID' => $storeId,
            'CUBKEY' => $cubKey,
            'RETURL' => $returnUrl,
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
            array_merge($parameters, ['STOREID' => $parameters['STOREID'], 'CUBKEY' => $parameters['CUBKEY']]),
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY']
        );

        $signature = Helper::signSignature($parameters, ['RETURL', 'CUBKEY']);

        $response = new AcceptNotificationResponse($this->getMockRequest(), $parameters);
        $replyResponse = $response->getReplyResponse();

        $this->assertTrue($response->isSuccessful());
        $this->assertSame($parameters['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        $this->assertSame($parameters['CUBXML']['AUTHINFO']['AUTHSTATUS'], $response->getCode());
        $this->assertSame($parameters['CUBXML']['AUTHINFO']['AUTHMSG'], $response->getMessage());
        $this->assertNotFalse(strpos($replyResponse->getContent(), $parameters['RETURL']), 'replay does not has '.$parameters['RETURL']);
        $this->assertNotFalse(strpos($replyResponse->getContent(), $signature), 'reply does not has '.$signature);
    }
}
