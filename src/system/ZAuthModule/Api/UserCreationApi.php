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

namespace Zikula\ZAuthModule\Api;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Api\ApiInterface\UserCreationApiInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\ZAuthConstant;

class UserCreationApi implements UserCreationApiInterface
{
    /**
     * array of created users
     *
     * @var UserEntity[]
     */
    private $users = [];

    /**
     * array of created ZAuth mappings
     *
     * @var AuthenticationMappingEntity[]
     */
    private $mappings = [];

    /**
     * @var Constraints\Collection
     */
    private $constraint;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var GroupEntity[]
     */
    private $groups;

    public function __construct(
        ValidatorInterface $validator,
        CurrentUserApiInterface $currentUserApi,
        EncoderFactoryInterface $encoderFactory,
        ManagerRegistry $managerRegistry,
        VariableApiInterface $variableApi,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->validator = $validator;
        $this->currentUserApi = $currentUserApi;
        $this->encoderFactory = $encoderFactory;
        $this->managerRegistry = $managerRegistry;
        $this->variableApi = $variableApi;
        $this->groups = $groupRepository->findAllAndIndexBy('gid');
        $this->constraint = new Constraints\Collection(['fields' => [
            'uname' => new ValidUname(),
            'pass' => new ValidPassword(),
            'email' => new ValidEmail(),
            'activated' => new Constraints\Optional([new Constraints\Choice([null, 0, 1, '', '0', '1'])]),
            'sendmail' => new Constraints\Optional([new Constraints\Choice([null, 0, 1, '', '0', '1'])]),
            'groups' => new Constraints\Optional([new Constraints\Type('string'), new Constraints\Regex(['pattern' => '%^[0-9\|]+$%'])])
        ]]);
    }

    /**
     * @param array $userArray
     *
     * @return bool|string
     */
    public function isValidUserData(array $userArray)
    {
        $errors = $this->validator->validate($userArray, $this->constraint);
        if (0 !== count($errors)) {
            return $errors[0]->getMessage();
        }

        return true;
    }

    public function isValidUserDataArray(array $userArrays)
    {
        $violations = new ConstraintViolationList();
        foreach ($userArrays as $userArray) {
            $errors = $this->validator->validate($userArray, $this->constraint);
            if (0 !== count($errors)) {
                $violations->addAll($errors);
            }
        }

        return 0 === $violations->count() ? true : $violations;
    }

    public function createUser(array $userArray): void
    {
        $groups = !empty($userArray['groups']) ? explode('|', $userArray['groups']) : [GroupsConstant::GROUP_ID_USERS];
        $password = $userArray['pass'];
        unset($userArray['pass'], $userArray['sendmail'], $userArray['groups']);
        $userArray['activated'] = isset($userArray['activated']) ? empty($userArray['activated']) ? UsersConstant::ACTIVATED_PENDING_REG : 1 : 1;

        $userEntity = new UserEntity();
        $userEntity->merge($userArray);
        $nowUTC = new \DateTime('now', new \DateTimeZone('UTC'));
        $userEntity->setRegistrationDate($nowUTC);
        $userEntity->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, ZAuthConstant::AUTHENTICATION_METHOD_EITHER);
        if (1 === $userEntity->getActivated()) {
            $userEntity->setApprovedDate($nowUTC);
            $currentUser = $this->currentUserApi->get('uid') ?? UsersConstant::USER_ID_ADMIN;
            $userEntity->setApprovedBy($currentUser);
        }
        foreach ($groups as $group) {
            if (isset($this->groups[$group])) {
                $userEntity->addGroup($this->groups[$group]);
                $this->groups[$group]->addUser($userEntity);
            }
        }
        $hash = hash('crc32', $userEntity->getUname() . $userEntity->getEmail());
        $this->users[$hash] = $userEntity;

        $this->createMapping($userEntity, $password, $hash);
    }

    private function createMapping(UserEntity $userEntity, string $pass, string $hash): void
    {
        $mapping = new AuthenticationMappingEntity();
        $mapping->setUname($userEntity->getUname());
        $mapping->setEmail($userEntity->getEmail());
        $mapping->setPass($this->encoderFactory->getEncoder(AuthenticationMappingEntity::class)->encodePassword($pass, null));
        $mapping->setMethod(ZAuthConstant::AUTHENTICATION_METHOD_EITHER);
        $userMustVerify = $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED);
        $mapping->setVerifiedEmail(!$userMustVerify);
        $this->mappings[$hash] = $mapping;
    }

    public function createUsers(array $users): array
    {
        $errors = [];
        foreach ($users as $row => $user) {
            if (true === $this->isValidUserData($user)) {
                $this->createUser($user);
            } else {
                $errors[] = sprintf('Row %d with email `%s` and uname `%s` is invalid and was rejected.', $row, $user['email'] ?? 'unset', $user['uname'] ?? 'unset');
            }
        }

        return $errors;
    }

    public function persist(): void
    {
        foreach ($this->users as $userEntity) {
            $this->managerRegistry->getManager()->persist($userEntity);
        }
        $this->managerRegistry->getManager()->flush();
        foreach ($this->mappings as $hash => $mapping) {
            $mapping->setUid($this->users[$hash]->getUid());
            $this->managerRegistry->getManager()->persist($mapping);
        }
        $this->managerRegistry->getManager()->flush();
    }

    public function getCreatedUsers(): array
    {
        return $this->users;
    }

    public function getCreatedMappings(): array
    {
        return $this->mappings;
    }

    public function clearCreated(): void
    {
        $this->users = [];
        $this->mappings = [];
    }
}
