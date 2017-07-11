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
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\Helper\ServiceNameHelper;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\AbstractModule;
use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Class BlockFactoryApi
 *
 * This class provides an API for the instantiation of block classes.
 */
class BlockFactoryApi implements BlockFactoryApiInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BlockFactoryApi constructor.
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator.default');
    }

    /**
     * {@inheritdoc}
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
            throw new \RuntimeException($this->translator->__f('Block class %c does not exist.', ['%c' => $blockClassName]));
        }
        if (!is_subclass_of($blockClassName, BlockHandlerInterface::class)) {
            throw new \RuntimeException(sprintf('Block class %s must implement Zikula\BlocksModule\BlockHandlerInterface.', $blockClassName));
        }

        $serviceNameHelper = new ServiceNameHelper();
        $blockServiceName = $serviceNameHelper->generateServiceNameFromClassName($blockClassName);
        if ($this->container->has($blockServiceName)) {
            return $this->container->get($blockServiceName);
        }

        if (is_subclass_of($blockClassName, AbstractBlockHandler::class)) {
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
