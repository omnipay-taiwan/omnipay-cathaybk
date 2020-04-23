<?php

namespace Omnipay\Cathaybk\Tests\Message;

use Omnipay\Cathaybk\Message\AcceptNotificationRequest;
use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Tests\TestCase;

class AcceptNotificationRequestTest extends TestCase
{
    public function testGetData()
    {
        $storeId = uniqid('store_id');
        $cubKey = uniqid('cub_key');
        $returnUrl = 'https://foo.bar/return-url';
        $xmlData = $this->generateXmlData($storeId, $cubKey);
        $request = new AcceptNotificationRequest($this->getHttpClient(), $this->getHttpRequest());

        $parameters = [
            'STOREID' => $storeId,
            'CUBKEY' => $cubKey,
            'RETURL' => $returnUrl,
            'strRsXML' => $strRsXML = Helper::array2xml($xmlData),
        ];
        $request->initialize($parameters);

        $data = $request->getData();

        $this->assertEquals(array_merge([
            'CAVALUE' => Helper::caValue(array_merge($parameters, [
                'DOMAIN' => parse_url($returnUrl, PHP_URL_HOST),
            ]), ['DOMAIN', 'CUBKEY']),
            'RETURL' => $returnUrl,
        ], $xmlData), $data);

        $this->assertEquals($xmlData['CUBXML']['AUTHINFO']['AUTHCODE'], $request->getTransactionReference());
        $this->assertEquals(NotificationInterface::STATUS_COMPLETED, $request->getTransactionStatus());
        $this->assertEquals($xmlData['CUBXML']['AUTHINFO']['AUTHMSG'], $request->getMessage());
    }

    /**
     * @param string $storeId
     * @param string $cubKey
     * @return array
     */
    private function generateXmlData(string $storeId, string $cubKey)
    {
        $parameters = [
            'CUBXML' => [
                'CAVALUE' => '',
                'ORDERINFO' => [
                    'STOREID' => $storeId,
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

        $parameters['CUBXML']['CAVALUE'] = Helper::caValue(
            array_merge(['STOREID' => $storeId, 'CUBKEY' => $cubKey], $parameters),
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY']
        );

        return $parameters;
    }
}
