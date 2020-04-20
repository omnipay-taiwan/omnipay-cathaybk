<?php

namespace Omnipay\Cathaybk\Traits;

trait HasCancel
{
    /**
     * @param bool $cancel
     * @return $this
     */
    public function setCancel($cancel)
    {
        return $this->setParameter('cancel', $cancel);
    }

    /**
     * @return $this
     */
    public function getCancel()
    {
        return $this->getParameter('cancel');
    }
}
