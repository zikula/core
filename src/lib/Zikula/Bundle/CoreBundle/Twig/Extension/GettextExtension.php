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

use Symfony\Bridge\Twig\AppVariable;
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

    public function __construct(TranslatorInterface $translator, ZikulaHttpKernelInterface $kernel)
    {
        $this->translator = $translator;
        $this->kernel = $kernel;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('__', [$this, '__'], ['needs_context' => true]),
            new TwigFunction('_n', [$this, '_n'], ['needs_context' => true]),
            new TwigFunction('__f', [$this, '__f'], ['needs_context' => true]),
            new TwigFunction('_fn', [$this, '_fn'], ['needs_context' => true])
        ];
    }

    public function __(array $context, string $message, string $domain = null, string $locale = null): string
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->__(/** @Ignore */$message, $domain, $locale);
    }

    public function __f(array $context, string $message, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->__f(/** @Ignore */$message, $parameters, $domain, $locale);
    }

    public function _n(array $context, string $singular, string $plural, int $count, string $domain = null, string $locale = null): string
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->_n(/** @Ignore */$singular, $plural, $count, $domain, $locale);
    }

    public function _fn(array $context, string $singular, string $plural, int $count, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $domain = $domain ?? $this->determineTranslationDomainFromContext($context);

        return $this->translator->_fn(/** @Ignore */$singular, $plural, $count, $parameters, $domain, $locale);
    }

    private function determineTranslationDomainFromContext(array $context, string $default = 'zikula'): string
    {
        if (isset($context['domain'])) {
            return $context['domain'];
        }
        if (isset($context['app'])) {
            /** @var AppVariable $app */
            $app = $context['app'];
            $bundleName = $app->getRequest()->attributes->get('_zkBundle');
            if (!empty($bundleName) && $this->kernel->isBundle($bundleName)) {
                return $this->kernel->getBundle($bundleName)->getTranslationDomain();
            }

            $controller = $app->getRequest()->attributes->get('_controller');
            if (!empty($controller)) {
                $controllerParts = explode(':', $controller);
                if ($this->kernel->isBundle($controllerParts[0])) {
                    return $this->kernel->getBundle($controllerParts[0])->getTranslationDomain();
                }
            }
        }

        return $default;
    }
}
