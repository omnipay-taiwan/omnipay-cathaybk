<?php

namespace Omnipay\Cathaybk\Traits;

trait HasAuthCode
{
    /**
     * @param $authCode
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
