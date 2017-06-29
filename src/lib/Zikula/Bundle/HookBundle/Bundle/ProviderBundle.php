<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Bundle;

/**
 * @deprecated remove at Core-2.0
 * Provider Bundle for Hook Providers
 */
class ProviderBundle
{
    /**
     * Owner.
     *
     * @var string
     */
    private $owner;

    /**
     * Sub Owner.
     *
     * @var string
     */
    private $subOwner;

    /**
     * Hook handlers.
     *
     * @var array
     */
    private $hooks = [];

    /**
     * Title.
     *
     * @var string
     */
    private $title;

    /**
     * Area ID.
     *
     * @var string
     */
    private $area;

    /**
     * Category.
     *
     * @var string
     */
    private $category;

    /**
     * Constructor.
     *
     * @param string $owner    Owner
     * @param string $area     Area ID, this should be a unique string
     * @param string $category Area category
     * @param string $title    Title
     */
    public function __construct($owner, $area, $category, $title)
    {
        $this->owner = $owner;
        $this->area = $area;
        $this->category = $category;
        $this->title = $title;
    }

    /**
     * Get hookhandlers property.
     *
     * @return array
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * Get title property.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get area property.
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get category property.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get owner property.
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get subOwner property.
     *
     * @return string
     */
    public function getSubOwner()
    {
        return $this->subOwner;
    }

    /**
     * Set subOwner property.
     *
     * @param string $subOwner
     *
     * @return ProviderBundle
     */
    public function setSubOwner($subOwner)
    {
        $this->subOwner = $subOwner;

        return $this;
    }

    /**
     * Add a static class::method() handler to this hundle.
     *
     * @param string $hookType  Hook type
     * @param string $className Class Name
     * @param string $method    Static method name
     *
     * @return ProviderBundle
     */
    public function addStaticHandler($hookType, $className, $method)
    {
        return $this->addHandler($hookType, $className, $method);
    }

    /**
     * Add servicehandler as hook handler to this bundle.
     *
     * @param string $hookType  Hook type
     * @param string $className Class name
     * @param string $method    Method name
     * @param string $serviceId Service Id
     *
     * @return ProviderBundle
     */
    public function addServiceHandler($hookType, $className, $method, $serviceId)
    {
        return $this->addHandler($hookType, $className, $method, $serviceId);
    }

    /**
     * Add a hook handler with this bundle.
     *
     * @param string $hookType  Hook type
     * @param string $className Class
     * @param string $method    Method name
     * @param string $serviceId Service ID if this is NOT a static class method
     *
     * @return ProviderBundle
     */
    private function addHandler($hookType, $className, $method, $serviceId = null)
    {
        $this->hooks[$hookType] = [
            'hooktype' => $hookType,
            'classname' => $className,
            'method' => $method,
            'serviceid' => $serviceId,
        ];

        return $this;
    }
}
