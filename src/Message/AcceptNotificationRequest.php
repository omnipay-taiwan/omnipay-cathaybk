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

        return $this->assertSignature(
            array_merge([
                'STOREID' => $this->getStoreId(),
                'CUBKEY' => $this->getCubKey(),
                'RETURL' => $this->getReturnUrl(),
            ], Helper::xml2array($this->getStrRsXML())),
            ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'AUTHSTATUS', 'AUTHCODE', 'CUBKEY']
        );
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
