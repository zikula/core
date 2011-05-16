<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Provider Bundle for Hook Providers
 */
class Zikula_HookManager_ProviderBundle
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
    private $hooks = array();

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
     * @param string $owner    Owner.
     * @param string $area     Area ID, this should be a unique string.
     * @param string $category Area category.
     * @param string $title    Title.
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
     * @param type $subOwner
     *
     * @return Zikula_HookManager_ProviderBundle
     */
    public function setSubOwner($subOwner)
    {
        $this->subOwner = $subOwner;
        return $this;
    }

    /**
     * Add a static class::method() handler to this hundle.
     *
     * @param string $hookType  Hook type.
     * @param string $className Class Name.
     * @param string $method    Static method name.
     *
     * @return Zikula_HookManager_ProviderBundle
     */
    public function addStaticHandler($hookType, $className, $method)
    {
        return $this->addHandler($hookType, $className, $method);
    }

    /**
     * Add servicehandler as hook handler to this bundle.
     *
     * @param string $hookType  Hook type.
     * @param string $className Class name.
     * @param string $method    Method name.
     * @param string $serviceId Service Id.
     *
     * @return Zikula_HookManager_ProviderBundle
     */
    public function addServiceHandler($hookType, $className, $method, $serviceId)
    {
        return $this->addHandler($hookType, $className, $method, $serviceId);
    }

    /**
     * Add a hook handler with this bundle.
     *
     * @param string  $hookType  Hook type.
     * @param string  $className Class.
     * @param string  $method    Method name.
     * @param string  $serviceId Service ID if this is NOT a static class method.
     *
     * @return Zikula_HookManager_ProviderBundle
     */
    private function addHandler($hookType, $className, $method, $serviceId=null)
    {
        $this->hooks[$hookType] = array(
                'hooktype' => $hookType,
                'classname' => $className,
                'method' => $method,
                'serviceid' => $serviceId,
                );

        return $this;
    }
}
