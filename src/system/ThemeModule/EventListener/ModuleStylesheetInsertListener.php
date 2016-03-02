<?php
/*** Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Core\AbstractModule;
use Zikula\Core\Controller\AbstractController;

/**
 * Class ModuleStylesheetInsertListener
 * @package Zikula\ThemeModule\EventListener
 */
class ModuleStylesheetInsertListener implements EventSubscriberInterface
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
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
                /** @var AbstractModule $module */
                $module = $this->kernel->getModule($controller->getName());
                $module->addStylesheet();
            } catch (\InvalidArgumentException $e) {
                // The module doesn't contain the default stylesheet.
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(
                array('insertModuleStylesheet'),
            ),
        );
    }
}
