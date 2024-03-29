<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\NotificationInterface;

class AcceptNotificationRequest extends AbstractRequest implements NotificationInterface
{
    use HasAssertCaValue;
    use HasSignCaValue;
    use HasStore;

    /**
     * @return array
     *
     * @throws InvalidRequestException
     * @throws InvalidResponseException
     */
    public function getData()
    {
        $this->validate('STOREID', 'CUBKEY', 'returnUrl');

        $returnValues = Helper::xml2array($this->httpRequest->request->get('strRsXML'));
        $this->assertCaValue($returnValues);
        $retUrl = $this->getReturnUrl();

        return array_merge([
            'CAVALUE' => $this->generateCaValue(['DOMAIN' => parse_url($retUrl, PHP_URL_HOST)]),
            'RETURL' => $retUrl,
        ], $returnValues);
    }

    /**
     * @param  array  $data
     * @return AcceptNotificationResponse
     */
    public function sendData($data)
    {
        return $this->response = new AcceptNotificationResponse($this, $data);
    }

    public function getTransactionId()
    {
        return $this->getNotification()->getTransactionId();
    }

    public function getTransactionReference()
    {
        return $this->getNotification()->getTransactionReference();
    }

    public function getTransactionStatus()
    {
        return $this->getNotification()->getTransactionStatus();
    }

    public function getMessage()
    {
        return $this->getNotification()->getMessage();
    }

    protected function getSignKeys()
    {
        return ['DOMAIN', 'CUBKEY'];
    }

    protected function getAssertKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY'];
    }

    /**
     * @return NotificationInterface
     */
    private function getNotification()
    {
        return ! $this->response ? $this->send() : $this->response;
    }
}
