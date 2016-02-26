<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Api;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\BlocksModule\Helper\ServiceNameHelper;
use Zikula\Core\AbstractModule;
use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Class BlockFactoryApi
 * @package Zikula\BlocksModule\Api
 *
 * This class provides an API for the instantiation of block classes.
 */
class BlockFactoryApi
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * BlockFactoryApi constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Factory method to create an instance of a block given its name and the providing module instance.
     *  Supports either Zikula\BlocksModule\BlockHandlerInterface or
     *  Zikula_Controller_AbstractBlock (to be removed).
     *
     * @todo at Core-2.0 remove BC support for Zikula_Controller_AbstractBlock
     * @todo remove `null` default value for $moduleBundle at Core-2.0 and check for null below
     * @param $blockClassName
     * @param AbstractModule|null $moduleBundle
     * @return \Zikula_Controller_AbstractBlock|BlockHandlerInterface
     */
    public function getInstance($blockClassName, AbstractModule $moduleBundle = null)
    {
        if (strpos($blockClassName, '.')) {
            // probably a service name
            if ($this->container->has($blockClassName)) {
                $service = $this->container->get($blockClassName);
                if ($service instanceof BlockHandlerInterface) {
                    return $service;
                }
            }
        }

        if (!class_exists($blockClassName)) {
            throw new \RuntimeException(sprintf('Classname %s does not exist.', $blockClassName));
        }
        if (!is_subclass_of($blockClassName, 'Zikula\BlocksModule\BlockHandlerInterface') && !is_subclass_of($blockClassName, 'Zikula_Controller_AbstractBlock')) {
            throw new \RuntimeException(sprintf('Block class %s must implement Zikula\BlocksModule\BlockHandlerInterface or be a subclass of Zikula_Controller_AbstractBlock.', $blockClassName));
        }

        $serviceNameHelper = new ServiceNameHelper();
        $blockServiceName = $serviceNameHelper->generateServiceNameFromClassName($blockClassName);
        if ($this->container->has($blockServiceName)) {
            return $this->container->get($blockServiceName);
        }

        if (is_subclass_of($blockClassName, 'Zikula_Controller_AbstractBlock')) {
            $blockInstance = new $blockClassName($this->container, $moduleBundle);
            $blockInstance->init();
        } elseif (is_subclass_of($blockClassName, 'Zikula\BlocksModule\AbstractBlockHandler')) {
            if ((null === $moduleBundle) || (!($moduleBundle instanceof AbstractModule))) {
                throw new \LogicException('$moduleBundle must be instance of AbstractModule and not null.');
            }
            $blockInstance = new $blockClassName($moduleBundle);
        } else {
            $blockInstance = new $blockClassName();
        }

        if ($blockInstance instanceof ContainerAwareInterface) {
            $blockInstance->setContainer($this->container);
        }

        $this->container->set($blockServiceName, $blockInstance);

        return $blockInstance;
    }
}
