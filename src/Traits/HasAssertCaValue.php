<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Message\Helper;
use Omnipay\Common\Exception\InvalidRequestException;

trait HasAssertCaValue
{
    /**
     * @param $data
     * @param $keys
     * @throws InvalidRequestException
     */
    protected function assertCaValue($data, $keys)
    {
        $signature = Helper::caValue(array_merge([
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
        ], $data), $keys);

        if ($signature !== $data['CUBXML']['CAVALUE']) {
            throw new InvalidRequestException();
        }
    }
}
