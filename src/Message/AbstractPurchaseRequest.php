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
        return $this->assignSignature($this->prepareData());
    }

    /**
     * @return array
     * @throws InvalidRequestException
     */
    protected function prepareData()
    {
        $this->validate('store_id', 'cub_key', 'amount');

        return [
            'STOREID' => $this->getStoreId(),
            'ORDERNUMBER' => $this->getOrderNumber() ?: uniqid(),
            'AMOUNT' => $this->getAmount(),
        ];
    }

    /**
     * @return array
     */
    abstract protected function getSignatureKeys();

    /**
     * @param $data
     * @return array
     */
    private function assignSignature($data)
    {
        return array_merge($data, [
            'CAVALUE' => Helper::signSignature(array_merge($data, [
                'STOREID' => $this->getStoreId(),
                'CUBKEY' => $this->getCubKey(),
            ]), $this->getSignatureKeys()),
        ]);
    }
}
