<?php

namespace Omnipay\Cathaybk\Traits;

trait HasOrderNumber
{
    /**
     * @param  string  $orderNumber
     * @return $this
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
