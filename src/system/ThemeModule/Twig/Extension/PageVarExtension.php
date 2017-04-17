<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Twig\Extension;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ThemeModule\Engine\ParameterBag;

class PageVarExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBag
     */
    private $pageVars;

    /**
     * PageVarExtension constructor.
     * @param TranslatorInterface $translator
     * @param ParameterBag $pageVars
     */
    public function __construct(
        TranslatorInterface $translator,
        ParameterBag $pageVars
    ) {
        $this->translator = $translator;
        $this->pageVars = $pageVars;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pageSetVar', [$this, 'pageSetVar']),
            new \Twig_SimpleFunction('pageGetVar', [$this, 'pageGetVar']),
        ];
    }

    /**
     * Zikula imposes no restriction on page variable names.
     * Typical usage is to set `title` `meta.charset` `lang` etc.
     * array values are set using `.` in the `$name` string (e.g. `meta.charset`)
     * @param string $name
     * @param string $value
     */
    public function pageSetVar($name, $value)
    {
        if (empty($name) || empty($value)) {
            throw new \InvalidArgumentException($this->translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $this->pageVars->set($name, $value);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function pageGetVar($name, $default = null)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException($this->translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->pageVars->get($name, $default);
    }
}
