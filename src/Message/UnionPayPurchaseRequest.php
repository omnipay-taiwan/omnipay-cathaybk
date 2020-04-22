<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasOrderNumber;
use Omnipay\Cathaybk\Traits\HasSignCaValue;
use Omnipay\Cathaybk\Traits\HasStore;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

class UnionPayPurchaseRequest extends AbstractRequest
{
    use HasStore;
    use HasOrderNumber;
    use HasSignCaValue;

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('STOREID', 'CUBKEY', 'transactionId', 'amount');

        return $this->mergeCaValue([
            'ORDERINFO' => [
                'STOREID' => $this->getStoreId(),
                'ORDERNUMBER' => $this->getOrderNumber(),
                'AMOUNT' => (int) $this->getAmount(),
            ],
        ]);
    }

    /**
     * @param array $data
     * @return PurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new UnionPayPurchaseResponse($this, $data);
    }

    /**
     * @return array
     */
    protected function getSignKeys()
    {
        return ['STOREID', 'ORDERNUMBER', 'AMOUNT', 'CUBKEY'];
    }
}
