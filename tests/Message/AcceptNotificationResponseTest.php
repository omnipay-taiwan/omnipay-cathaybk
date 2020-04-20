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

        $this->assertTrue($response->isSuccessful());
        $this->assertSame($parameters['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        $this->assertSame($parameters['CUBXML']['AUTHINFO']['AUTHCODE'], $response->getTransactionReference());
        $this->assertSame($parameters['CUBXML']['AUTHINFO']['AUTHSTATUS'], $response->getCode());
        $this->assertSame($parameters['CUBXML']['AUTHINFO']['AUTHMSG'], $response->getMessage());
        $this->assertNotFalse(
            strpos($replyResponse->getContent(), $parameters['RETURL']),
            'replay does not has ' . $parameters['RETURL']
        );
        $this->assertNotFalse(
            strpos($replyResponse->getContent(), $parameters['CAVALUE']),
            'reply does not has ' . $parameters['CAVALUE']
        );
    }

    /**
     * @param string $returnUrl
     * @return array
     */
    private function generateXmlData(string $returnUrl)
    {
        return [
            'CAVALUE' => uniqid('ca_value'),
            'RETURL' => $returnUrl,
            'CUBXML' => [
                'CAVALUE' => uniqid('ca_value'),
                'ORDERINFO' => [
                    'STOREID' => uniqid('store_id'),
                    'ORDERNUMBER' => uniqid('order_number'),
                    'AMOUNT' => '10',
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
    }
}
