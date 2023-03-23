<?php

namespace Omnipay\Cathaybk\Traits;

trait HasAmount
{
    public function getAmount()
    {
        return $this->getParameter('amount');
    }
}
