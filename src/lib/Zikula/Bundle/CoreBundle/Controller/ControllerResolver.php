<?php
/**
 * Copyright Zikula Foundation 2013 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
        if (is_subclass_of($class, 'Zikula_AbstractBase') || is_subclass_of($class, 'Zikula\Core\Controller\AbstractController')) {
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

            if ($currentBundle === false) {
                throw new \LogicException(sprintf('Unable to calculate the bundle from controller "%s".', $class));
            }

            if (is_subclass_of($class, 'Zikula_AbstractBase')) {
                $controller = new $class($this->container, $currentBundle);
            } else {
                $controller = new $class($currentBundle);
            }
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
