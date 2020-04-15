<?php

namespace Omnipay\Cathaybk\Traits;

trait HasLangParams
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
        return $this->getParameter('language');
    }
}
