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
use Zikula\PermissionsModule\Api\PermissionApi;

class AuthenticateAdminLoginValidator extends ConstraintValidator
{
    use TranslatorTrait;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var Connection
     */
    private $databaseConnection;

    /**
     * AuthenticateAdminLoginValidator constructor.
     * @param PermissionApi $permissionApi
     * @param Connection $connection
     * @param TranslatorInterface $translator
     */
    public function __construct(PermissionApi $permissionApi, Connection $connection, TranslatorInterface $translator)
    {
        $this->permissionApi = $permissionApi;
        $this->databaseConnection = $connection;
        $this->setTranslator($translator);
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    public function validate($object, Constraint $constraint)
    {
        try {
            $user = $this->databaseConnection->fetchAssoc('SELECT uid, pass FROM zauth_authentication_mapping WHERE uname= ?', [$object['username']]);
        } catch (\Exception $e) {
            $this->context->buildViolation($this->__('Error! There was a problem with the database connection.'))
                ->addViolation()
            ;
        }

        if (empty($user) || ($user['uid'] <= 1) || (!\UserUtil::passwordsMatch($object['password'], $user['pass']))) { // @todo
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
