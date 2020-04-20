<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Message\Helper;
use Omnipay\Common\Exception\InvalidRequestException;

trait HasAssertCaValue
{
    /**
     * @param $data
     * @throws InvalidRequestException
     */
    protected function assertCaValue($data)
    {
        $caValue = Helper::caValue(array_merge([
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
        ], $data), $this->getAssertKeys());

        if ($caValue !== $data['CUBXML']['CAVALUE']) {
            throw new InvalidRequestException();
        }
    }

    /**
     * @return array
     */
    abstract protected function getAssertKeys();
}
