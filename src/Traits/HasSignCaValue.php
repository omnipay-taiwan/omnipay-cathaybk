<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Message\Helper;

trait HasSignCaValue
{
    /**
     * @param $data
     * @return array
     */
    protected function signCaValue($data)
    {
        return array_merge([
            'CAVALUE' => Helper::signSignature(array_merge($data, [
                'STOREID' => $this->getStoreId(),
                'CUBKEY' => $this->getCubKey(),
            ]), $this->getSignKeys()),
        ], $data);
    }

    abstract protected function getSignKeys();
}
