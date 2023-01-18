<?php

namespace Omnipay\Cathaybk\Traits;

trait HasStore
{
    /**
     * @param  string  $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setParameter('STOREID', $storeId);
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->getParameter('STOREID');
    }

    /**
     * @param  string  $cubKey
     * @return $this
     */
    public function setCubKey($cubKey)
    {
        return $this->setParameter('CUBKEY', $cubKey);
    }

    /**
     * @return string
     */
    public function getCubKey()
    {
        return $this->getParameter('CUBKEY');
    }
}
