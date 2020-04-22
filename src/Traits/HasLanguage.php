<?php

namespace Omnipay\Cathaybk\Traits;

trait HasLanguage
{
    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        return $this->setParameter('LANGUAGE', $language);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return strtoupper($this->getParameter('LANGUAGE') ?: 'ZH-TW');
    }
}
