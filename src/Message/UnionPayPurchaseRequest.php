<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Common\Message\ResponseInterface;

class UnionPayPurchaseRequest extends AbstractPurchaseRequest
{
    /**
     * @param mixed $data
     * @return PurchaseResponse|ResponseInterface
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
