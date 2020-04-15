<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasStoreParams;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;

abstract class AbstractPurchaseRequest extends AbstractRequest
{
    use HasStoreParams;

    /**
     * @param string $orderNumber
     * @return AbstractPurchaseRequest
     */
    public function setOrderNumber($orderNumber)
    {
        return $this->setTransactionId($orderNumber);
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->getTransactionId();
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('store_id', 'cub_key', 'amount');

        return [
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
            'ORDERNUMBER' => $this->getOrderNumber() ?: uniqid(),
            'AMOUNT' => $this->getAmount(),
        ];
    }
}
