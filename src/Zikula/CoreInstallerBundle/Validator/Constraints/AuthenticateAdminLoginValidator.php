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
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;

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
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var PasswordApiInterface
     */
    private $passwordApi;

    public function __construct(
        PermissionApiInterface $permissionApi,
        Connection $connection,
        TranslatorInterface $translator,
        EncoderFactoryInterface $encoderFactory,
        PasswordApiInterface $passwordApi
    ) {
        $this->permissionApi = $permissionApi;
        $this->databaseConnection = $connection;
        $this->setTranslator($translator);
        $this->encoderFactory = $encoderFactory;
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
                ->addViolation();
        }

        $passwordEncoder = $this->encoderFactory->getEncoder(AuthenticationMappingEntity::class);

        if (empty($user) || $user['uid'] <= 1) { // || !$passwordEncoder->isPasswordValid($user['pass'], $object['password'], null)) {
            $this->context
                ->buildViolation($this->trans('Error! Could not login because the user could not be found. Please try again.'))
                ->addViolation();
        } else {
            $validPassword = false;
            if ($this->passwordApi->passwordsMatch($object['password'], $user['pass'])) {
                // old way - remove in Core-4.0.0
                $validPassword = true;
                // convert old encoding to new
                $this->setPassword((int) $user['uid'], $object['password']);
            } elseif (
                // new way
                $passwordEncoder->isPasswordValid($user['pass'], $object['password'], null)) {
                $validPassword = true;
                if ($passwordEncoder->needsRehash($user['pass'])) { // check to update hash to newer algo
                    $this->setPassword((int) $user['uid'], $object['password']);
                }
            }
            if (!$validPassword) {
                $this->context
                    ->buildViolation($this->trans('Error! Could not login with the provided credentials. Please try again.'))
                    ->addViolation();
            }

            $granted = $this->permissionApi->hasPermission('.*', '.*', ACCESS_ADMIN, (int) $user['uid']);
            if (!$granted) {
                $this->context
                    ->buildViolation($this->trans('Error! You logged in to an account without Admin permissions'))
                    ->addViolation();
            }
        }
    }

    private function setPassword(int $uid, string $unHashedPassword)
    {
        $passwordEncoder = $this->encoderFactory->getEncoder(AuthenticationMappingEntity::class);
        $hashedPassword = $passwordEncoder->encodePassword($unHashedPassword, null);
        $this->databaseConnection->executeUpdate('UPDATE zauth_authentication_mapping SET pass = ? WHERE uid = ?', [
            $hashedPassword,
            $uid
        ]);
    }
}
