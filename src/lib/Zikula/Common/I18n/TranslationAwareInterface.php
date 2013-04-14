<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dordrak
 * Date: 14/04/13
 * Time: 13:43
 * To change this template use File | Settings | File Templates.
 */

namespace Zikula\Common\I18n;

interface TranslationAwareInterface
{
    /**
     * @param TranslatableInterface $translator
     */
    public function setTranslator(TranslatableInterface $translator);
}