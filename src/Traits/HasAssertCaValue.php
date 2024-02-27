<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Support\Helper;
use Omnipay\Common\Exception\InvalidResponseException;

trait HasAssertCaValue
{
    /**
     * @throws InvalidResponseException
     */
    protected function assertCaValue($data)
    {
        $caValue = Helper::caValue(array_merge([
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
        ], $data), $this->getAssertKeys());

        if (! hash_equals($data['CUBXML']['CAVALUE'], $caValue)) {
            throw new InvalidResponseException();
        }
    }

    /**
     * @return array
     */
    abstract protected function getAssertKeys();
}
