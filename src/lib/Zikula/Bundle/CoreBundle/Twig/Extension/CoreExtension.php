<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\Intl\Intl;
use Zikula\Bundle\CoreBundle\Twig;
use Zikula\Common\Translator\TranslatorInterface;

class CoreExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CoreExtension constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new Twig\TokenParser\SwitchTokenParser()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = [
            new \Twig_SimpleFunction('lang', [$this, 'lang']),
            new \Twig_SimpleFunction('langdirection', [$this, 'langDirection']),
            new \Twig_SimpleFunction('array_unset', [$this, 'arrayUnset']),
            new \Twig_SimpleFunction('modAvailable', [$this, 'modAvailable']),
            new \Twig_SimpleFunction('callFunc', [$this, 'callFunc']),
        ];

        return $functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('languageName', [$this, 'languageName']),
            new \Twig_SimpleFilter('yesNo', [$this, 'yesNo']),
            new \Twig_SimpleFilter('php', [$this, 'applyPhp']),
            new \Twig_SimpleFilter('protectMail', [$this, 'protectMailAddress'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Function to get the site's language.
     *
     * Available parameters:
     *     - fs:  safe for filesystem.
     * @return string The language
     * @deprecated remove at Core-2.0 use app.request.locale
     */
    public function lang($fs = false)
    {
        $result = ($fs ? \ZLanguage::transformFS(\ZLanguage::getLanguageCode()) : \ZLanguage::getLanguageCode());

        return $result;
    }

    /**
     * Function to get the language direction
     *
     * @return string   the language direction
     * @deprecated remove at Core-2.0 use dir="auto"
     */
    public function langDirection()
    {
        return \ZLanguage::getDirection();
    }

    /**
     * Delete a key of an array
     *
     * @param array  $array Source array
     * @param string $key   The key to remove
     *
     * @return array
     */
    public function arrayUnset($array, $key)
    {
        unset($array[$key]);

        return $array;
    }

    /**
     * @param string $code
     * @return string
     */
    public function languageName($code)
    {
        return Intl::getLanguageBundle()->getLanguageName($code);
    }

    /**
     * @param $string
     * @return string
     */
    public function yesNo($string)
    {
        if ($string != '0' && $string != '1') {
            return $string;
        }

        return (bool)$string ? $this->translator->__('Yes') : $this->translator->__('No');
    }

    /**
     * Apply an existing function (e.g. php's `md5`) to a string.
     *
     * @param $string
     * @param $func
     * @return mixed
     */
    public function applyPhp($string, $func)
    {
        if (function_exists($func)) {
            return $func($string);
        }

        return $string;
    }

    /**
     * Protect a given mail address by finding the text 'x@y' and replacing
     * it with HTML entities. This provides protection against email harvesters.
     *
     * @param string
     * @return string
     */
    public function protectMailAddress($string)
    {
        $string = preg_replace_callback(
            '/(.)@(.)/s',
            function ($m) {
                return "&#" . sprintf("%03d", ord($m[1])) . ";&#064;&#" .sprintf("%03d", ord($m[2])) . ";";
            },
            $string
        );

        return $string;
    }

    /**
     * @deprecated remove at Core-2.0
     * @param string $modname
     * @param bool $force
     * @return bool
     */
    public function modAvailable($modname, $force = false)
    {
        $result = \ModUtil::available($modname, $force);

        return (bool)$result;
    }

    /**
     * Call a php callable with parameters.
     * @param callable $callable
     * @param array $params
     * @return mixed
     */
    public function callFunc(callable $callable, array $params = [])
    {
        if (function_exists($callable)) {
            return call_user_func_array($callable, $params);
        }

        throw new \InvalidArgumentException($this->translator->__('Function does not exist or is not callable.'));
    }
}
