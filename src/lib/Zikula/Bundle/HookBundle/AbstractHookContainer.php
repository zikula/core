<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle;

use Zikula\Bundle\HookBundle\Bundle\SubscriberBundle;
use Zikula\Bundle\HookBundle\Bundle\ProviderBundle;
use Zikula\Common\Translator\TranslatorTrait;

abstract class AbstractHookContainer
{
    use TranslatorTrait;

    /**
     * Hook subscriber bundles.
     *
     * @var array Indexed array of SubscriberBundle
     */
    protected $subscriberBundles = [];

    /**
     * Hook provider bundles.
     *
     * @var array Indexed array of ProviderBundle
     */
    protected $providerBundles = [];

    public function __construct($translator)
    {
        $this->setTranslator($translator);
        $this->setupHookBundles();

        return $this;
    }

    abstract protected function setupHookBundles();

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Register a hook subscriber bundle.
     *
     * @param SubscriberBundle $bundle HookBundle.
     *
     * @return AbstractHookContainer
     */
    public function registerHookSubscriberBundle(SubscriberBundle $bundle)
    {
        if (array_key_exists($bundle->getArea(), $this->subscriberBundles)) {
            throw new \InvalidArgumentException(sprintf('Area %s is already registered', $bundle->getArea()));
        }

        $this->subscriberBundles[$bundle->getArea()] = $bundle;

        return $this;
    }

    /**
     * Register a hook subscriber bundle.
     *
     * @param ProviderBundle $bundle HookProviderBundle.
     *
     * @return AbstractHookContainer
     */
    public function registerHookProviderBundle(ProviderBundle $bundle)
    {
        if (array_key_exists($bundle->getArea(), $this->providerBundles)) {
            throw new \InvalidArgumentException(sprintf('Area %s is already registered', $bundle->getArea()));
        }

        $this->providerBundles[$bundle->getArea()] = $bundle;

        return $this;
    }

    /**
     * Returns array of hook subscriber bundles.
     *
     * Usually this will only be one.
     *
     * @return array Of SubscriberBundle
     */
    public function getHookSubscriberBundles()
    {
        return $this->subscriberBundles;
    }

    /**
     * Returns array of hook bundles.
     *
     * Usually this will only be one.
     *
     * @return array Of ProviderBundle
     */
    public function getHookProviderBundles()
    {
        return $this->providerBundles;
    }

    /**
     * Get hook subscriber bundle for a given area.
     *
     * @param string $area Area.
     *
     * @throws \InvalidArgumentException If the area specified is not registered.
     *
     * @return SubscriberBundle
     */
    public function getHookSubscriberBundle($area)
    {
        if (!array_key_exists($area, $this->subscriberBundles)) {
            throw new \InvalidArgumentException(__f('Hook subscriber area %s does not exist', $area));
        }

        return $this->subscriberBundles[$area];
    }

    /**
     * Get hook provider bundle for a given area.
     *
     * @param string $area Area.
     *
     * @throws \InvalidArgumentException If the area specified is not registered.
     *
     * @return ProviderBundle
     */
    public function getHookProviderBundle($area)
    {
        if (!array_key_exists($area, $this->providerBundles)) {
            throw new \InvalidArgumentException(__f('Hook provider area %s does not exist', $area));
        }

        return $this->providerBundles[$area];
    }
}
