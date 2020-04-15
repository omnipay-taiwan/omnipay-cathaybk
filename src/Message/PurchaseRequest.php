<?php

namespace Omnipay\Cathaybk\Message;

use Omnipay\Cathaybk\Traits\HasLangParams;
use Omnipay\Cathaybk\Traits\HasStoreParams;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

class PurchaseRequest extends AbstractRequest
{
    use HasStoreParams;
    use HasLangParams;

    /**
     * @param string $orderNumber
     * @return PurchaseRequest
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
        $this->validate('store_id', 'cub_key', 'amount', 'language');

        return [
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
            'ORDERNUMBER' => $this->getOrderNumber() ?: uniqid(),
            'AMOUNT' => $this->getAmount(),
            'LANGUAGE' => strtoupper($this->getLanguage()),
        ];
    }

    /**
     * @param mixed $data
     * @return PurchaseResponse|ResponseInterface
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
