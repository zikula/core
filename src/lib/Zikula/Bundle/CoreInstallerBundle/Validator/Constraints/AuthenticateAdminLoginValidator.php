<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Validator\Constraints;

use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zikula\PermissionsModule\Api\PermissionApi;

class AuthenticateAdminLoginValidator extends ConstraintValidator
{
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @deprecated remove at Core-2.0
     * @var string
     */
    private $installedCoreVersion;

    /**
     * AuthenticateAdminLoginValidator constructor.
     * @param PermissionApi $permissionApi
     * @param Connection $connection
     * @param string $coreVersion @deprecated
     */
    public function __construct(PermissionApi $permissionApi, Connection $connection, $installedCoreVersion = '1.4.2')
    {
        $this->permissionApi = $permissionApi;
        $this->databaseConnection = $connection;
        $this->installedCoreVersion = $installedCoreVersion;
    }

    public function validate($object, Constraint $constraint)
    {
        try {
            $table = version_compare($this->installedCoreVersion, '1.4.3', '>=') ? 'zauth_authentication_mapping' : 'users'; // @deprecated use zauth_authentication_mapping for Core-2.0
            $user = $this->databaseConnection->fetchAssoc('SELECT uid, pass FROM ' . $table . ' WHERE uname= ?', [$object['username']]);

            if (empty($user) || ($user['uid'] <= 1) || (!\UserUtil::passwordsMatch($object['password'], $user['pass']))) { // @todo
                $this->context->buildViolation(__('Error! Could not login with provided credentials. Please try again.'))
                    ->addViolation()
                ;
            }
            $granted = $this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, $user['uid']);
            if (!$granted) {
                $this->context->buildViolation(__('Error! You logged in to an account without Admin permissions'))
                    ->addViolation();
            }
        } catch (\Exception $e) {
            $this->context->buildViolation(__('Error! There was a problem logging in.'))
                ->addViolation()
            ;
        }
    }
}
