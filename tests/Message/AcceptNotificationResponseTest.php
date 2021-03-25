<?php

namespace Omnipay\Cathaybk\Tests\Message;

use Omnipay\Cathaybk\Message\AcceptNotificationResponse;
use Omnipay\Tests\TestCase;

class AcceptNotificationResponseTest extends TestCase
{
    public function testSuccess()
    {
        $returnUrl = 'https://foo.bar/return-url';
        $parameters = $this->generateXmlData($returnUrl);

        $response = new AcceptNotificationResponse($this->getMockRequest(), $parameters);
        $replyResponse = $response->getReplyResponse();

        self::assertTrue($response->isSuccessful());
        self::assertSame($parameters['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        self::assertSame($parameters['CUBXML']['AUTHINFO']['AUTHCODE'], $response->getTransactionReference());
        self::assertSame($parameters['CUBXML']['AUTHINFO']['AUTHSTATUS'], $response->getCode());
        self::assertSame($parameters['CUBXML']['AUTHINFO']['AUTHMSG'], $response->getMessage());
        self::assertNotFalse(
            strpos($replyResponse->getContent(), $parameters['RETURL']),
            'replay does not has '.$parameters['RETURL']
        );
        self::assertNotFalse(
            strpos($replyResponse->getContent(), $parameters['CAVALUE']),
            'reply does not has '.$parameters['CAVALUE']
        );
    }

    /**
     * @param string $returnUrl
     * @return array
     */
    private function generateXmlData($returnUrl)
    {
        return [
            'CAVALUE' => uniqid('ca_value', true),
            'RETURL' => $returnUrl,
            'CUBXML' => [
                'CAVALUE' => uniqid('ca_value', true),
                'ORDERINFO' => [
                    'STOREID' => uniqid('store_id', true),
                    'ORDERNUMBER' => uniqid('order_number', true),
                    'AMOUNT' => '10',
                    'LANGUAGE' => 'ZH-TW',
                ],
                'AUTHINFO' => [
                    'AUTHSTATUS' => '0000',
                    'AUTHCODE' => uniqid('auth_code', true),
                    'AUTHTIME' => date('YmdHis'),
                    'AUTHMSG' => '授權成功',
                    'CARDNO' => uniqid('card_no', true),
                ],
            ],
        ];
    }
}
