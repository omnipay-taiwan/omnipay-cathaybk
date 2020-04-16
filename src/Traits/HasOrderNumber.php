<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Message\AbstractRequest;

trait HasOrderNumber
{
    /**
     * @param string $orderNumber
     * @return AbstractRequest
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
}
