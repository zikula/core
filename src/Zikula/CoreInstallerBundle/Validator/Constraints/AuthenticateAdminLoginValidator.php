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

namespace Zikula\Bundle\CoreInstallerBundle\Validator\Constraints;

use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;

class AuthenticateAdminLoginValidator extends ConstraintValidator
{
    use TranslatorTrait;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * @var PasswordApiInterface
     */
    private $passwordApi;

    public function __construct(
        PermissionApiInterface $permissionApi,
        Connection $connection,
        TranslatorInterface $translator,
        PasswordApiInterface $passwordApi
    ) {
        $this->permissionApi = $permissionApi;
        $this->databaseConnection = $connection;
        $this->setTranslator($translator);
        $this->passwordApi = $passwordApi;
    }

    public function validate($object, Constraint $constraint)
    {
        try {
            $user = $this->databaseConnection->fetchAssoc('
                SELECT uid, pass
                FROM zauth_authentication_mapping
                WHERE uname = ?
            ', [$object['username']]);
        } catch (Exception $exception) {
            $this->context->buildViolation($this->trans('Error! There was a problem with the database connection.'))
                ->addViolation()
            ;
        }

        if (empty($user) || $user['uid'] <= 1 || !$this->passwordApi->passwordsMatch($object['password'], $user['pass'])) {
            $this->context
                ->buildViolation($this->trans('Error! Could not login with provided credentials. Please try again.'))
                ->addViolation()
            ;
        } else {
            $granted = $this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, (int) $user['uid']);
            if (!$granted) {
                $this->context
                    ->buildViolation($this->trans('Error! You logged in to an account without Admin permissions'))
                    ->addViolation()
                ;
            }
        }
    }
}
