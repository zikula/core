<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\Controller\AbstractController;

/**
 * Class ModuleStylesheetInsertListener
 */
class ModuleStylesheetInsertListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * ModuleStylesheetInsertListener constructor.
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Add the module stylesheet to the page assets.
     * @param FilterControllerEvent $event
     * @throws \Twig_Error_Loader
     */
    public function insertModuleStylesheet(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $controller = $event->getController()[0];
        if ($controller instanceof AbstractController) {
            try {
                $module = $this->kernel->getModule($controller->getName());
                $module->addStylesheet();
            } catch (\InvalidArgumentException $e) {
                // The module doesn't contain the default stylesheet.
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['insertModuleStylesheet']
            ]
        ];
    }
}
