<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Form;

use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Zikula\Common\Translator\TranslatorTrait;

class AbstractType extends BaseAbstractType
{
    use TranslatorTrait;

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
