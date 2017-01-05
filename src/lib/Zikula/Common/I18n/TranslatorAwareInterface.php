<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\I18n;

/**
 * Interface TranslatorAwareInterface
 * @deprecated remove at Core-2.0
 */
interface TranslatorAwareInterface
{
    /**
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator);
}
