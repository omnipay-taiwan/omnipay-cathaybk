<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasLanguage;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

class CompletePurchaseRequest extends AbstractRequest
{
    use HasStore;
    use HasLanguage;
    use HasAssertCaValue;

    /**
     * @param $strOrderInfo
     * @return CompletePurchaseRequest
     */
    public function setStrOrderInfo($strOrderInfo)
    {
        return $this->setParameter('strOrderInfo', $strOrderInfo);
    }

    /**
     * @return string
     */
    public function getStrOrderInfo()
    {
        return $this->getParameter('strOrderInfo');
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('strOrderInfo');
        $orderInfo = Helper::xml2array($this->getStrOrderInfo());
        $this->assertCaValue($orderInfo, ['STOREID', 'ORDERNUMBER', 'CUBKEY']);

        return $orderInfo;
    }

    /**
     * @param mixed $data
     * @return CompletePurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }
}
