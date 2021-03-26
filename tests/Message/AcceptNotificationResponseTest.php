<?php

namespace Omnipay\Cathaybk\Tests\Message;

use Omnipay\Cathaybk\Message\AcceptNotificationResponse;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Tests\TestCase;

class AcceptNotificationResponseTest extends TestCase
{
    public function testSuccess()
    {
        $returnUrl = 'https://foo.bar/return-url';
        $options = $this->generateXmlData($returnUrl);

        $response = new AcceptNotificationResponse($this->getMockRequest(), $options);
        $strXML = $response->getMessage();

        self::assertTrue($response->isSuccessful());
        self::assertSame($options['CUBXML']['ORDERINFO']['ORDERNUMBER'], $response->getTransactionId());
        self::assertSame($options['CUBXML']['AUTHINFO']['AUTHCODE'], $response->getTransactionReference());
        self::assertSame($options['CUBXML']['AUTHINFO']['AUTHSTATUS'], $response->getCode());
        self::assertSame(NotificationInterface::STATUS_COMPLETED, $response->getTransactionStatus());
        self::assertNotFalse(
            strpos($strXML, $options['RETURL']),
            'replay does not has '.$options['RETURL']
        );
        self::assertNotFalse(
            strpos($strXML, $options['CAVALUE']),
            'reply does not has '.$options['CAVALUE']
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
