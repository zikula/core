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

use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * GettextExtension class.
 */
class GettextExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * GettextExtension constructor.
     * @param TranslatorInterface $translator
     * @param KernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, KernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('__', [$this, '__'], ['needs_context' => true]),
            new \Twig_SimpleFunction('_n', [$this, '_n'], ['needs_context' => true]),
            new \Twig_SimpleFunction('__f', [$this, '__f'], ['needs_context' => true]),
            new \Twig_SimpleFunction('_fn', [$this, '_fn'], ['needs_context' => true]),
            new \Twig_SimpleFunction('__p', [$this, '__p'], ['needs_context' => true]),
            new \Twig_SimpleFunction('__fp', [$this, '__fp'], ['needs_context' => true]),
            new \Twig_SimpleFunction('_fnp', [$this, '_fnp'], ['needs_context' => true]),
            new \Twig_SimpleFunction('no__', [$this, 'no__']),
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikula_gettext';
    }

    /**
     * @see __()
     */
    public function __(array $context, $message, $domain = null, $locale = null)
    {
        $domain = $this->determineTranslationDomainFromContext($context);

        return $this->translator->__(/** @Ignore */$message, $domain, $locale);
    }

    /**
     * @see __f()
     */
    public function __f(array $context, $message, $params, $domain = null, $locale = null)
    {
        $domain = isset($domain) ? $domain : $this->determineTranslationDomainFromContext($context);

        return $this->translator->__f(/** @Ignore */$message, $params, $domain, $locale);
    }

    /**
     * @see _n()
     */
    public function _n(array $context, $singular, $plural, $count, $domain = null, $locale = null)
    {
        $domain = isset($domain) ? $domain : $this->determineTranslationDomainFromContext($context);

        return $this->translator->_n(/** @Ignore */$singular, $plural, $count, $domain, $locale);
    }

    /**
     * @see _fn()
     */
    public function _fn(array $context, $singular, $plural, $count, $params, $domain = null, $locale = null)
    {
        $domain = isset($domain) ? $domain : $this->determineTranslationDomainFromContext($context);

        return $this->translator->_fn(/** @Ignore */$singular, $plural, $count, $params, $domain, $locale);
    }

    /**
     * @see no__()
     */
    public function no__($msgid)
    {
        return $msgid;
    }

    /**
     * Translator context functions
     *
     * @todo Define how this should work
     * @link https://www.gnu.org/software/gettext/manual/html_node/Contexts.html
     */

    /**
     * @see __p()
     */
    public function __p(array $twigContext, $context, $message, $domain = null)
    {
        $domain = isset($domain) ? $domain : $this->determineTranslationDomainFromContext($twigContext);

        return \__p($context, $message, $domain);
    }

    /**
     * @see __fp()
     */
    public function __fp(array $twigContext, $context, $message, $params, $domain = null)
    {
        $domain = isset($domain) ? $domain : $this->determineTranslationDomainFromContext($twigContext);

        return \__fp($context, $message, $params, $domain);
    }

    /**
     * @see _fpn()
     */
    public function _fnp(array $twigContext, $context, $singular, $plural, $count, $params, $domain = null)
    {
        $domain = isset($domain) ? $domain : $this->determineTranslationDomainFromContext($twigContext);

        return \_fnp($context, $singular, $plural, $count, $params, $domain);
    }

    /**
     * @see _np()
     */
    public function _np(array $twigContext, $context, $singular, $plural, $count, $domain = null)
    {
        $domain = isset($domain) ? $domain : $this->determineTranslationDomainFromContext($twigContext);

        return \_np($context, $singular, $plural, $count, $domain);
    }

    /**
     * @param array $context
     * @param string $default
     * @return string
     */
    private function determineTranslationDomainFromContext(array $context, $default = 'zikula')
    {
        if (isset($context['domain'])) {
            return $context['domain'];
        }
        if (isset($context['app'])) {
            /** @var \Symfony\Bridge\Twig\AppVariable $app */
            $app = $context['app'];
            $bundleName = $app->getRequest()->attributes->get('_zkBundle');

            return $this->kernel->getBundle($bundleName)->getTranslationDomain();
        }

        return $default;
    }
}
