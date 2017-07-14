<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Translator;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;

/**
 * Translator
 */
class Translator extends BaseTranslator implements WarmableInterface, TranslatorInterface
{
    /**
     * @var ServiceLocator
     */
    protected $serviceLocator;

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
     * Constructor.
     * Available options:
     * * cache_dir: The cache directory (or null to disable caching)
     * * debug: Whether to enable debugging or not (false by default)
     * * resource_files: List of translation resources available grouped by locale.
     *
     * @param ServiceLocator $serviceLocator
     * @param MessageSelector $selector
     * @param string $defaultLocale
     * @param array $loaderIds
     * @param array $options
     * @throws \InvalidArgumentException
     */
    public function __construct(ServiceLocator $serviceLocator, MessageSelector $selector = null, $defaultLocale, $loaderIds = [], array $options = [])
    {
        $this->serviceLocator = $serviceLocator;
        $this->loaderIds = $loaderIds;
        // check option names
        if ($diff = array_diff(array_keys($options), array_keys($this->options))) {
            throw new \InvalidArgumentException(sprintf('The Translator does not support the following options: \'%s\'.', implode('\', \'', $diff)));
        }

        $this->domain = 'zikula';
        $this->options = array_merge($this->options, $options);
        $this->resourceLocales = array_keys($this->options['resource_files']);
        if (null !== $this->options['cache_dir'] && $this->options['debug']) {
            $this->loadResources();
        }

        parent::__construct($defaultLocale, $selector, $this->options['cache_dir'], $this->options['debug']);
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
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
    protected function initializeCatalogue($locale)
    {
        $this->initialize();
        parent::initializeCatalogue($locale);
    }

    /**
     * Initialize translator
     */
    protected function initialize()
    {
        $this->loadResources();
        foreach ($this->loaderIds as $id => $aliases) {
            foreach ($aliases as $alias) {
                $this->addLoader($alias, $this->serviceLocator->get($id));
            }
        }
    }

    /**
     * Load zikula resource files
     */
    private function loadResources()
    {
        foreach ($this->options['resource_files'] as $locale => $files) {
            foreach ($files as $key => $file) {
                $c = substr_count($file, ".");

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

    /**
     * Set the translation domain.
     *
     * @param string $domain
     *            Gettext domain
     * @return void
     */
    public function setDomain($domain = null)
    {
        $this->domain = $domain;
    }

    /**
     * Get translation domain.
     *
     * @return string $this->domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     *
     * @api
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $domain = null === $domain ? $this->domain : $domain;
        $locale = null === $locale ? $this->getLocale() : $locale;

        return parent::trans($id, $parameters, $domain, $locale);
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param int         $number     The number to use to find the indice of the message
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $domain = null === $domain ? $this->domain : $domain;
        $locale = null === $locale ? $this->getLocale() : $locale;

        return parent::transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __($msg, $domain = null, $locale = null)
    {
        return $this->trans($msg, [], $domain, $locale);
    }

    /**
     * Plural translations for modules.
     *
     * @param string $m1 Singular
     * @param string $m2 Plural
     * @param integer $n Count
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _n($m1, $m2, $n, $domain = null, $locale = null)
    {
        $message = $this->chooseMessage($m1, $m2, $n, $domain);

        return $this->transChoice($message, $n, ['%count%' => $n], $domain, $locale);
    }

    /**
     * Format translations for modules.
     *
     * @param string $msg Message
     * @param array $param Format parameters
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __f($msg, array $param, $domain = null, $locale = null)
    {
        return $this->trans($msg, $param, $domain, $locale);
    }

    /**
     * Format plural translations for modules.
     *
     * @param string $m1 Singular
     * @param string $m2 Plural
     * @param integer $n Count
     * @param array $param Format parameters
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _fn($m1, $m2, $n, array $param, $domain = null, $locale = null)
    {
        $message = $this->chooseMessage($m1, $m2, $n, $domain);
        $param['%count%'] = $n;

        return $this->transChoice($message, $n, $param, $domain, $locale);
    }

    /**
     * Choose message if no translation catalogue
     *
     * @param string $m1 Singular
     * @param string $m2 Plural
     * @param integer $n Count
     * @param string|null $domain
     * @return string
     */
    private function chooseMessage($m1, $m2, $n, $domain = null)
    {
        $message = $m2;
        if (($this->getLocale() == 'en') || ($domain == 'en')) {
            $domains = $this->getCatalogue($this->getLocale())->getDomains();
            if (!in_array($this->domain, $domains)) {
                $message = ($n == 1) ? $m1 : $m2;
            }
        }

        return $message;
    }
}
