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

namespace Zikula\PermissionsModule\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * Controller action permissions
 *
 * This annotation is used in a Controller Action Method in one of two ways.
 * 1. like so: @PermissionCheck("admin")
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
 *     Also allowed: @PermissionCheck("ACCESS_ADMIN")
 *
 * 2. You can also pass any valid permission schema (e.g. @PermissionCheck({"ZikulaCategoriesModule::category", "ID::5", "ACCESS_EDIT"})
 *     - note the use of curly brackets `{}` within for this type of value
 *     The listener will attempt to replace any variable with a route attribute value. For example if this is the annotation:
 *       @PermissionCheck({"ZikulaGroupsModule::", "$gid::", "ACCESS_EDIT"})
 *     Then, the listener will look for an 'gid' attribute in the request object and replace the variable name with its value
 *     when testing for permissions.
 *     You can also use `$_zkModule` as the Extension name if preferred, e.g. @PermissionCheck({"$_zkModule::", "$gid::", "ACCESS_EDIT"})
 *     You can also use access alias if preferred, e.g. @PermissionCheck({"$_zkModule::", "$gid::", "edit"})
 *
 * @see \Zikula\PermissionsModule\Listener\ControllerPermissionCheckAnnotationReaderListener
 */
class PermissionCheck extends Annotation
{
}
