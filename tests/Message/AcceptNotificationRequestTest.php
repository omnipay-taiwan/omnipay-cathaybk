<?php

namespace Omnipay\Cathaybk\Tests\Message;

use Omnipay\Cathaybk\Message\AcceptNotificationRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Tests\TestCase;

class AcceptNotificationRequestTest extends TestCase
{
    /**
     * @throws InvalidRequestException
     */
    public function testGetData()
    {
        $storeId = uniqid('store_id', true);
        $cubKey = uniqid('cub_key', true);
        $returnUrl = 'https://foo.bar/return-url';
        $xmlData = $this->generateXmlData($storeId, $cubKey);
        $request = new AcceptNotificationRequest($this->getHttpClient(), $this->getHttpRequest());

        $options = [
            'STOREID' => $storeId,
            'CUBKEY' => $cubKey,
            'RETURL' => $returnUrl,
            'strRsXML' => $strRsXML = Helper::array2xml($xmlData),
        ];
        $request->initialize($options);

        $data = $request->getData();

        self::assertEquals(array_merge([
            'CAVALUE' => Helper::caValue(array_merge($options, [
                'DOMAIN' => parse_url($returnUrl, PHP_URL_HOST),
            ]), ['DOMAIN', 'CUBKEY']),
            'RETURL' => $returnUrl,
        ], $xmlData), $data);

        return [$request, $xmlData];
    }

    /**
     * @depends testGetData
     * @param $results
     */
    public function testNotification($results)
    {
        list($notification, $xmlData) = $results;

        self::assertEquals($xmlData['CUBXML']['ORDERINFO']['ORDERNUMBER'], $notification->getTransactionId());
        self::assertEquals($xmlData['CUBXML']['AUTHINFO']['AUTHCODE'], $notification->getTransactionReference());
        self::assertEquals(NotificationInterface::STATUS_COMPLETED, $notification->getTransactionStatus());
    }

    /**
     * @param string $storeId
     * @param string $cubKey
     * @return array
     */
    private function generateXmlData($storeId, $cubKey)
    {
        $options = [
            'CUBXML' => [
                'CAVALUE' => '',
                'ORDERINFO' => [
                    'STOREID' => $storeId,
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

        $options['CUBXML']['CAVALUE'] = Helper::caValue(
            array_merge(['STOREID' => $storeId, 'CUBKEY' => $cubKey], $options),
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY']
        );

        return $options;
    }
}
