<?php

namespace Omnipay\Cathaybk\Traits;

trait HasStoreParams
{
    /**
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setParameter('store_id', $storeId);
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->getParameter('store_id');
    }

    /**
     * @param string $cubKey
     * @return $this
     */
    public function setCubKey($cubKey)
    {
        return $this->setParameter('cub_key', $cubKey);
    }

    /**
     * @return string
     */
    public function getCubKey()
    {
        return $this->getParameter('cub_key');
    }
}
