<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * GettextExtension class.
 */
class GettextExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * GettextExtension constructor.
     * @param TranslatorInterface $translator
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function __construct(TranslatorInterface $translator, ZikulaHttpKernelInterface $kernel)
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
            new TwigFunction('__', [$this, '__'], ['needs_context' => true]),
            new TwigFunction('_n', [$this, '_n'], ['needs_context' => true]),
            new TwigFunction('__f', [$this, '__f'], ['needs_context' => true]),
            new TwigFunction('_fn', [$this, '_fn'], ['needs_context' => true]),
        ];
    }

    /**
     * @see __()
     */
    public function __(array $context, $message, $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->__(/** @Ignore */$message, $domain, $locale);
    }

    /**
     * @see __f()
     */
    public function __f(array $context, $message, $params, $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->__f(/** @Ignore */$message, $params, $domain, $locale);
    }

    /**
     * @see _n()
     */
    public function _n(array $context, $singular, $plural, $count, $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->_n(/** @Ignore */$singular, $plural, $count, $domain, $locale);
    }

    /**
     * @see _fn()
     */
    public function _fn(array $context, $singular, $plural, $count, $params, $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->_fn(/** @Ignore */$singular, $plural, $count, $params, $domain, $locale);
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
            if (!empty($bundleName) && $this->kernel->isBundle($bundleName)) {
                return $this->kernel->getBundle($bundleName)->getTranslationDomain();
            }

            $controller = $app->getRequest()->attributes->get('_controller');
            if (!empty($controller)) {
                $controllerParts = preg_split('/:/', $controller);
                if ($this->kernel->isBundle($controllerParts[0])) {
                    return $this->kernel->getBundle($controllerParts[0])->getTranslationDomain();
                }
            }
        }

        return $default;
    }
}
