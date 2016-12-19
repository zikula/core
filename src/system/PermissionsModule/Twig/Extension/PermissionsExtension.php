<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PermissionsExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        $functions = [
            new \Twig_SimpleFunction('hasPermission', [$this, 'hasPermission']),
        ];

        return $functions;
    }

    /**
     * @param string $component
     * @param string $instance
     * @param string $level
     * @return bool
     */
    public function hasPermission($component, $instance, $level)
    {
        if (empty($component) || empty($instance) || empty($level)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $result = $this->container->get('zikula_permissions_module.api.permission')->hasPermission($component, $instance, constant($level));

        return (bool) $result;
    }
}
