<?php

namespace Omnipay\Cathaybk\Traits;

trait HasAuthCode
{
    /**
     * @return $this
     */
    public function setAuthCode($authCode)
    {
        return $this->setTransactionReference($authCode);
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->getTransactionReference();
    }
}
