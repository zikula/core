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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
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

    /**
     * AuthenticateAdminLoginValidator constructor.
     * @param PermissionApiInterface $permissionApi
     * @param Connection $connection
     * @param TranslatorInterface $translator
     * @param PasswordApiInterface $passwordApi
     */
    public function __construct(PermissionApiInterface $permissionApi, Connection $connection, TranslatorInterface $translator, PasswordApiInterface $passwordApi)
    {
        $this->permissionApi = $permissionApi;
        $this->databaseConnection = $connection;
        $this->setTranslator($translator);
        $this->passwordApi = $passwordApi;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function validate($object, Constraint $constraint)
    {
        try {
            $schemaManager = $this->databaseConnection->getSchemaManager();
            if (true == $schemaManager->tablesExist(['zauth_authentication_mapping'])) {
                $user = $this->databaseConnection->fetchAssoc('SELECT uid, pass FROM zauth_authentication_mapping WHERE uname= ?', [$object['username']]);
            }
            if (empty($user)) {
                // core-1.4.3 method failed, try older method
                // @deprecated use zauth_authentication_mapping for Core-2.0
                $user = $this->databaseConnection->fetchAssoc('SELECT uid, pass FROM users WHERE uname= ?', [$object['username']]);
            }
        } catch (\Exception $e) {
            $this->context->buildViolation($this->__('Error! There was a problem with the database connection.'))
                ->addViolation()
            ;
        }

        if (empty($user) || ($user['uid'] <= 1) || (!$this->passwordApi->passwordsMatch($object['password'], $user['pass']))) {
            $this->context->buildViolation($this->__('Error! Could not login with provided credentials. Please try again.'))
                ->addViolation();
        } else {
            $granted = $this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, $user['uid']);
            if (!$granted) {
                $this->context->buildViolation($this->__('Error! You logged in to an account without Admin permissions'))
                    ->addViolation();
            }
        }
    }
}
