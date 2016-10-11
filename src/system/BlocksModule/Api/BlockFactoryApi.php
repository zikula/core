<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Api;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\BlocksModule\Helper\ServiceNameHelper;
use Zikula\Core\AbstractModule;
use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Class BlockFactoryApi
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
     * @param $blockClassName
     * @param AbstractModule $moduleBundle
     * @return BlockHandlerInterface
     */
    public function getInstance($blockClassName, AbstractModule $moduleBundle)
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
        if (!is_subclass_of($blockClassName, 'Zikula\BlocksModule\BlockHandlerInterface')) {
            throw new \RuntimeException(sprintf('Block class %s must implement Zikula\BlocksModule\BlockHandlerInterface.', $blockClassName));
        }

        $serviceNameHelper = new ServiceNameHelper();
        $blockServiceName = $serviceNameHelper->generateServiceNameFromClassName($blockClassName);
        if ($this->container->has($blockServiceName)) {
            return $this->container->get($blockServiceName);
        }

        if (is_subclass_of($blockClassName, 'Zikula\BlocksModule\AbstractBlockHandler')) {
            if (!($moduleBundle instanceof AbstractModule)) {
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
