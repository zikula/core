<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Translator;

use Symfony\Component\Translation\TranslatorInterface as SymfonyTranslatorInterface;

/**
 * reference the "translator.default" service id and typehint against this interface.
 *
 * Interface TranslatorInterface
 * @package Zikula\Common\Translator
 */
interface TranslatorInterface extends SymfonyTranslatorInterface
{
    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __($msg, $domain = null, $locale = null);

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _n($m1, $m2, $n, $domain = null, $locale = null);

    /**
     * Format translations for modules.
     *
     * @param string $msg Message.
     * @param array $param Format parameters.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __f($msg, array $param, $domain = null, $locale = null);

    /**
     * Format plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param array $param Format parameters.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _fn($m1, $m2, $n, array $param, $domain = null, $locale = null);
}
