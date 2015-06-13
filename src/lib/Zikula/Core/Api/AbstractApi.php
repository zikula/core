<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 * 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Translate
 *             Please see the NOTICE file distributed with this source code for further
 *             information regarding copyright and licensing.
 */

namespace Zikula\Core\Api;

use Zikula\Core\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractApi
{
    /**
     * Bundle Name.
     *
     * @var string
     */
    protected $name;
    /**
     * @var \Zikula\Common\Translator\Translator
     */
    protected $translator;
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * Constructor.
     *
     * @param AbstractBundle $bundle
     *            An AbstractBundle instance
     */
    public function __construct(AbstractBundle $bundle)
    {
        $this->name = $bundle->getName();
        $this->container = $bundle->getContainer();
        $this->translator = $this->get('translator');
        $this->translator->setDomain($bundle->getTranslationDomain());
        $this->boot($bundle);
    }

    /**
     * boot
     * 
     * @param AbstractBundle $bundle            
     */
    public function boot(AbstractBundle $bundle)
    {
        // load optional bootstrap
        $bootstrap = $bundle->getPath() . "/bootstrap.php";
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
        // load any plugins
        // @todo adjust this when Namespaced plugins are implemented
        \PluginUtil::loadPlugins($bundle->getPath() . "/plugins", "ModulePlugin_{$this->name}");
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __($msg, $domain = null, $locale = null)
    {
        return $this->translator->__($msg, $domain, $locale);
    }

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
    public function _n($m1, $m2, $n, $domain = null, $locale = null)
    {
        return $this->translator->_n($m1, $m2, $n, $domain, $locale);
    }

    /**
     * Format translations for modules.
     *
     * @param string $msg Message.
     * @param string|array $param Format parameters.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function __f($msg, $param, $domain = null, $locale = null)
    {
        return $this->translator->__f($msg, $param, $domain, $locale);
    }

    /**
     * Format plural translations for modules.
     *
     * @param string $m1 Singular.
     * @param string $m2 Plural.
     * @param integer $n Count.
     * @param string|array $param Format parameters.
     * @param null $domain
     * @param null $locale
     * @return string
     */
    public function _fn($m1, $m2, $n, $param, $domain = null, $locale = null)
    {
        return $this->translator->_fn($m1, $m2, $n, $param, $domain, $locale);
    }

    /**
     * convenience method to get container services
     *
     * @param $service
     * @return object
     */
    public function get($service)
    {
        return $this->container->get($service);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}