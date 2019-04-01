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

namespace Zikula\Common\Translator;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator as BaseTranslator;
use Zikula\Bundle\CoreBundle\Translation\SymfonyLoader\MockPotFileLoader;

/**
 * Translator
 */
class Translator extends BaseTranslator implements WarmableInterface, TranslatorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var array
     */
    protected $loaderIds;

    /**
     * @var array
     */
    protected $options = [
        'cache_dir' => null,
        'debug' => false,
        'resource_files' => []
    ];

    /**
     * @var array
     */
    private $resourceLocales;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ContainerInterface $container,
        MessageFormatterInterface $formatter = null,
        string $defaultLocale = 'en',
        array $loaderIds = [],
        array $options = []
    ) {
        $this->container = $container;
        $this->loaderIds = $loaderIds;
        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new InvalidArgumentException(sprintf('The Translator does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->domain = 'zikula';
        $this->options = array_merge($this->options, $options);
        $this->resourceLocales = array_keys($this->options['resource_files']);
        if (null !== $this->options['cache_dir'] && $this->options['debug']) {
            $this->loadResources();
        }

        parent::__construct($defaultLocale, $formatter, $this->options['cache_dir'], $this->options['debug']);
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir): void
    {
        // skip warmUp when translator doesn't use cache
        if (null === $this->options['cache_dir']) {
            return;
        }
        foreach ($this->resourceLocales as $locale) {
            $this->loadCatalogue($locale);
        }
    }

    /**
     * @param string $locale
     */
    protected function initializeCatalogue($locale): void
    {
        $this->initialize();
        parent::initializeCatalogue($locale);
    }

    /**
     * Initialize translator
     */
    protected function initialize(): void
    {
        $this->loadResources();

        // TODO remove again
        if (empty($this->loaderIds)) {
            $this->loaderIds = [
                'dummy' => [
                    'po' => PoFileLoader::class,
                    'pot' => MockPotFileLoader::class,
                    'xlf' => XliffFileLoader::class
                ]
            ];
        }
        foreach ($this->loaderIds as $id => $aliases) {
            foreach ($aliases as $alias => $loaderClass) {
                $this->addLoader($alias, new $loaderClass());
            }
        }

//         foreach ($this->loaderIds as $id => $aliases) {
//             foreach ($aliases as $alias) {
//                 $this->addLoader($alias, $this->container->get($id));
//             }
//         }
    }

    /**
     * Load zikula resource files
     */
    private function loadResources(): void
    {
        foreach ($this->options['resource_files'] as $locale => $files) {
            foreach ($files as $key => $file) {
                $c = mb_substr_count($file, '.');

                if ($c < 2) {
                    // filename is domain.format
                    list($domain, $format) = explode('.', basename($file), 2);
                } else {
                    // filename is domain.locale.format
                    list($domain, $locale, $format) = explode('.', basename($file), 3);
                }

                $this->addResource($format, $file, $locale, $domain);
                unset($this->options['resource_files'][$locale][$key]);
            }
        }
    }

    public function setDomain(string $domain = null): void
    {
        $this->domain = $domain;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->domain;
        $locale = $locale ?? $this->getLocale();

        return parent::trans($id, $parameters, $domain, $locale);
    }

    /**
     * @deprecated
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $domain = $domain ?? $this->domain;
        $locale = $locale ?? $this->getLocale();

        return parent::trans($id, ['%count%' => $number] + $parameters, $domain, $locale);
    }

    public function __(string $msg, string $domain = null, string $locale = null): string
    {
        return $this->trans($msg, [], $domain, $locale);
    }

    public function _n(string $m1, string $m2, int $number, string $domain = null, string $locale = null): string
    {
        $message = $this->chooseMessage($m1, $m2, $number, $domain);

        return $this->trans($message, ['%count%' => $number], $domain, $locale);
    }

    public function __f(string $msg, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->trans($msg, $parameters, $domain, $locale);
    }

    public function _fn(string $m1, string $m2, int $number, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $message = $this->chooseMessage($m1, $m2, $number, $domain);

        return $this->trans($message, ['%count%' => $number] + $parameters, $domain, $locale);
    }

    /**
     * Choose message if no translation catalogue.
     */
    private function chooseMessage(string $m1, string $m2, int $number, string $domain = null): string
    {
        $message = $m2;
        if ('en' === $domain || 'en' === $this->getLocale()) {
            $domains = $this->getCatalogue($this->getLocale())->getDomains();
            if (!in_array($this->domain, $domains, true)) {
                $message = 1 === $number ? $m1 : $m2;
            }
        }

        return $message;
    }
}
