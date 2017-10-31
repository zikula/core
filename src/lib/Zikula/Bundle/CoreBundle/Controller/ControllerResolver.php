<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class ControllerResolver.
 */
class ControllerResolver extends BaseControllerResolver
{
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 == $count) {
                // controller in the a:b:c notation then
                $controller = $this->parser->parse($controller);
            } elseif (1 == $count) {
                // controller in the service:method notation
                list($service, $method) = explode(':', $controller, 2);

                return [$this->container->get($service), $method];
            } else {
                throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        // Own logic
        if (is_subclass_of($class, 'Zikula\Core\Controller\AbstractController')) {
            $kernel = $this->container->get("kernel");
            $bundleNamespace = substr($class, 0, strpos($class, '\Controller'));
            $bundles = $kernel->getBundles();

            $currentBundle = false;
            foreach ($bundles as $bundle) {
                if ($bundle->getNamespace() == $bundleNamespace) {
                    $currentBundle = $bundle;
                    break;
                }
            }

            if (false === $currentBundle) {
                throw new \LogicException(sprintf('Unable to calculate the bundle from controller "%s".', $class));
            }

            $controller = new $class($currentBundle);
        } else {
            $controller = new $class();
        }
        // End own logic

        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }

        return [$controller, $method];
    }
}
