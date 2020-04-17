<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

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

        $rs = Helper::xml2array($this->getStrRsXML());

        $this->assertCaValue($rs, [
            'STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY',
        ]);

        $retUrl = $this->getReturnUrl();

        return array_merge([
            'CAVALUE' => $this->generateCaValue(['DOMAIN' => parse_url($retUrl, PHP_URL_HOST)]),
            'RETURL' => $retUrl,
        ], $rs);
    }

    /**
     * @param mixed $data
     * @return AcceptNotificationResponse|ResponseInterface
     */
    public function sendData($data)
    {
        return $this->response = new AcceptNotificationResponse($this, $data);
    }

    protected function getSignKeys()
    {
        return ['DOMAIN', 'CUBKEY'];
    }
}
