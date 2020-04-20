<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

class AcceptNotificationRequest extends AbstractRequest
{
    use HasStore;
    use HasAssertCaValue;
    use HasSignCaValue;

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
        $this->validate('strRsXML', 'returnUrl');

        $returnValues = Helper::xml2array($this->getStrRsXML());

        $this->assertCaValue($returnValues);

        $retUrl = $this->getReturnUrl();

        return array_merge([
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

    protected function getSignKeys()
    {
        return ['DOMAIN', 'CUBKEY'];
    }

    protected function getAssertKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY',];
    }
}
