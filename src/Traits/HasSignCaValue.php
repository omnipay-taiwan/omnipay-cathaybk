<?php

namespace Omnipay\Cathaybk\Traits;

use Omnipay\Cathaybk\Message\Helper;

trait HasSignCaValue
{
    /**
     * @param $data
     * @return array
     */
    protected function mergeCaValue($data)
    {
        return array_merge(['CAVALUE' => $this->generateCaValue($data),], $data);
    }

    protected function generateCaValue($data)
    {
        return Helper::caValue(array_merge($data, [
            'STOREID' => $this->getStoreId(),
            'CUBKEY' => $this->getCubKey(),
        ]), $this->getSignKeys());
    }

    abstract protected function getSignKeys();
}
