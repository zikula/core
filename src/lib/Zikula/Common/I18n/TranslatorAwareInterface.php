<?php

namespace Zikula\Common\I18n;

interface TranslatorAwareInterface
{
    /**
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator);
}