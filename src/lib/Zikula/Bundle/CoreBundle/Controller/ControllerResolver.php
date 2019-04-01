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

namespace Zikula\Bundle\CoreBundle\Controller;

use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver as BaseControllerResolver;
use Zikula\Bundle\CoreInstallerBundle\Controller\AbstractController as InstallerController;
use Zikula\Core\Controller\AbstractController;

/**
 * Class ControllerResolver.
 */
class ControllerResolver extends BaseControllerResolver
{
    protected function createController($controller)
    {
        if (false === mb_strpos($controller, '::')) {
            return parent::createController($controller);
        }

        list($class, $method) = explode('::', $controller, 2);
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        // Own logic
        if (is_subclass_of($class, InstallerController::class)) {
            $controller = new $class($this->container);
        } elseif (is_subclass_of($class, AbstractController::class)) {
            $controller = $this->container->get($class);
            if (method_exists($controller, 'setContainer')) {
                $controller->setContainer($this->container);
            }
        } else {
            $controller = $this->instantiateController($class);
        }
        // End own logic

        return [$controller, $method];
    }
}
