<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Cathaybk\Traits\HasAssertCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractRequest;

class CompletePurchaseRequest extends AbstractRequest
{
    use HasAssertCaValue;
    use HasStore;

    /**
     * @return array
     *
     * @throws InvalidResponseException
     */
    public function getData()
    {
        $orderInfo = Helper::xml2array($this->httpRequest->request->get('strOrderInfo'));
        $this->assertCaValue($orderInfo);

        return $orderInfo;
    }

    /**
     * @param  mixed  $data
     * @return CompletePurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }

    /**
     * @return string[]
     */
    protected function getAssertKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'CUBKEY'];
    }
}
