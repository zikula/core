<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class ModuleStylesheetInsertListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['insertModuleStylesheet']
            ]
        ];
    }

    /**
     * Add the module stylesheet to the page assets.
     */
    public function insertModuleStylesheet(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $controller = $event->getController()[0];
        if ($controller instanceof AbstractController) {
            try {
                $module = $this->kernel->getModule($controller->getName());
                $module->addStylesheet();
            } catch (InvalidArgumentException $exception) {
                // The module doesn't contain the default stylesheet.
            }
        }
    }
}
