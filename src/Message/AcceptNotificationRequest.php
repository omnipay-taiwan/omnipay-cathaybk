<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\NotificationInterface;

class AcceptNotificationRequest extends AbstractRequest implements NotificationInterface
{
    use HasStore;
    use HasAssertCaValue;
    use HasSignCaValue;

    /**
     * @var array
     */
    private $data;

    /**
     * @param $returnUrl
     * @return AcceptNotificationRequest
     */
    public function setRetUrl($returnUrl)
    {
        return $this->setReturnUrl($returnUrl);
    }

    /**
     * @return string
     */
    public function getRetUrl()
    {
        return $this->getReturnUrl();
    }

    /**
     * @param $strRsXML
     * @return AcceptNotificationRequest
     */
    public function setStrRsXML($strRsXML)
    {
        return $this->setParameter('strRsXML', $strRsXML);
    }

    /**
     * @return string
     */
    public function getStrRsXML()
    {
        return $this->getParameter('strRsXML');
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        if (! empty($this->data)) {
            return $this->data;
        }

        $this->validate('STOREID', 'CUBKEY', 'strRsXML', 'returnUrl');

        $returnValues = Helper::xml2array($this->getStrRsXML());

        $this->assertCaValue($returnValues);

        $retUrl = $this->getReturnUrl();

        return $this->data = array_merge([
            'CAVALUE' => $this->generateCaValue(['DOMAIN' => parse_url($retUrl, PHP_URL_HOST)]),
            'RETURL' => $retUrl,
        ], $returnValues);
    }

    /**
     * @param array $data
     * @return AcceptNotificationResponse
     */
    public function sendData($data)
    {
        return $this->response = new AcceptNotificationResponse($this, $data);
    }

    /**
     * Gateway Reference.
     *
     * @return string A reference provided by the gateway to represent this transaction
     * @throws InvalidRequestException
     */
    public function getTransactionReference()
    {
        $data = $this->getData();

        return $data['CUBXML']['AUTHINFO']['AUTHCODE'];
    }

    /**
     * @return string
     * @throws InvalidRequestException
     */
    public function getTransactionStatus()
    {
        $data = $this->getData();

        return $data['CUBXML']['AUTHINFO']['AUTHSTATUS'] === '0000'
            ? self::STATUS_COMPLETED : self::STATUS_FAILED;
    }

    /**
     * @return string
     * @throws InvalidRequestException
     */
    public function getMessage()
    {
        $data = $this->getData();

        return $data['CUBXML']['AUTHINFO']['AUTHMSG'];
    }

    protected function getSignKeys()
    {
        return ['DOMAIN', 'CUBKEY'];
    }

    protected function getAssertKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY'];
    }
}
