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
        return $this->setParameter('language', $language);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return strtoupper($this->getParameter('language') ?: 'ZH-TW');
    }
}