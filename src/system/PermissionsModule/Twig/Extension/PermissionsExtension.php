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

namespace Zikula\PermissionsModule\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class PermissionsExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * PermissionsExtension constructor.
     *
     * @param TranslatorInterface $translator
     * @param PermissionApiInterface $permissionApi
     */
    public function __construct(TranslatorInterface $translator, PermissionApiInterface $permissionApi)
    {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('hasPermission', [$this, 'hasPermission']),
        ];
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
            throw new \InvalidArgumentException($this->translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->permissionApi->hasPermission($component, $instance, constant($level));
    }
}
