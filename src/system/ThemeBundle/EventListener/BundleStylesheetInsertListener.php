<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeBundle\EventListener;

use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class BundleStylesheetInsertListener implements EventSubscriberInterface
{
    public function __construct(private readonly ZikulaHttpKernelInterface $kernel)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['insertBundleStylesheet'],
        ];
    }

    /**
     * Add the bundle stylesheet to the page assets.
     */
    public function insertBundleStylesheet(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        $controller = $controller[0];
        if ($controller instanceof AbstractController) {
            try {
                $controllerNameParts = explode('\\', $controller::class);
                $bundleName = $controllerNameParts[0] . $controllerNameParts[1];
                $module = $this->kernel->getModule($bundleName);
                $module->addStylesheet();
            } catch (InvalidArgumentException) {
                // bundle doesn't contain the default stylesheet
            }
        }
    }
}
