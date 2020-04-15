<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\AssertSignature;
use Omnipay\Cathaybk\Traits\HasStoreParams;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

class AcceptNotificationRequest extends AbstractRequest
{
    use HasStoreParams;
    use AssertSignature;

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

        $this->assertSignature($rs, [
            'STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY',
        ]);

        $retUrl = $this->getReturnUrl();
        $keys = ['RETURL', 'CUBKEY'];

        return array_merge([
            'CAVALUE' => Helper::signSignature(['RETURL' => $retUrl, 'CUBKEY' => $this->getCubKey()], $keys),
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
}
