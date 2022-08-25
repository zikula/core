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

namespace Zikula\PermissionsBundle\Annotation;

use Attribute;

/**
 * Controller action permissions
 *
 * This annotation is used in a controller method OR class in one of two ways.
 *
 * 1. Like so: #[PermissionCheck('admin')]
 *     Possible alias values are:
 *       'admin'
 *       'delete'
 *       'add'
 *       'edit'
 *       'moderate'
 *       'comment'
 *       'read'
 *       'overview'
 *     In the above cases,
 *       the component will be like 'AcmeFooModule::'
 *       the instance will be '::'
 *       the level will be the corresponding ACCESS_* constant (e.g. ACCESS_ADMIN)
 *     Also allowed: #[PermissionCheck('ACCESS_ADMIN')]
 *
 * 2. You can also pass any valid permission schema (e.g. #[PermissionCheck(['ZikulaCategoriesBundle::category', 'ID::5', 'ACCESS_EDIT'])]
 *     The listener will attempt to replace any variable with a route attribute value. For example if this is the annotation:
 *       #[PermissionCheck(['ZikulaGroupsBundle::', '$gid::', 'ACCESS_EDIT'])]
 *     Then, the listener will look for an 'gid' attribute in the Request object and replace the variable name with its value
 *     when testing for permissions.
 *     You can also use `$_zkModule` as the Extension name if preferred, e.g. #[PermissionCheck(['$_zkModule::', '$gid::', 'ACCESS_EDIT'])]
 *     You can also use the access alias if preferred, e.g. #[PermissionCheck(['$_zkModule::', '$gid::', 'edit'])]
 *
 * Please note: You cannot use #[PermissionCheck()] in *both* the class and the method. This will produce an Exception.
 *
 * @see \Zikula\PermissionsBundle\EventListener\ControllerPermissionCheckAnnotationReaderListener
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class PermissionCheck
{
    public function __construct(public string|array $value)
    {
    }
}
