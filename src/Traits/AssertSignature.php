<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Message\Helper;
use Omnipay\Common\Exception\InvalidRequestException;

trait AssertSignature
{
    /**
     * @param $data
     * @param $keys
     * @throws InvalidRequestException
     */
    protected function assertSignature($data, $keys)
    {
        $signature = Helper::signSignature(array_merge([
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
        ], $data), $keys);

        if ($signature !== $data['CUBXML']['CAVALUE']) {
            throw new InvalidRequestException();
        }
    }
}
