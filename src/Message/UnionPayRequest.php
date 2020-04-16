<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Common\Message\ResponseInterface;

class UnionPayRequest extends AbstractRequest
{
    /**
     * @param mixed $data
     * @return PurchasePurchaseResponse|ResponseInterface
     */
    public function sendData($data)
    {
        return $this->response = new UnionPayPurchaseResponse($this, $data);
    }

    /**
     * @return array
     */
    protected function getSignatureKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY'];
    }
}
