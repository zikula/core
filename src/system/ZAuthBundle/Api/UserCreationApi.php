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

namespace Zikula\ZAuthBundle\Api;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\GroupsBundle\GroupsConstant;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\UsersConstant;
use Zikula\UsersBundle\Validator\Constraints\ValidEmail;
use Zikula\UsersBundle\Validator\Constraints\ValidUname;
use Zikula\ZAuthBundle\Api\ApiInterface\UserCreationApiInterface;
use Zikula\ZAuthBundle\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthBundle\Validator\Constraints\ValidPassword;
use Zikula\ZAuthBundle\ZAuthConstant;

class UserCreationApi implements UserCreationApiInterface
{
    /**
     * array of created users
     *
     * @var UserEntity[]
     */
    private array $users = [];

    /**
     * array of created ZAuth mappings
     *
     * @var AuthenticationMappingEntity[]
     */
    private array $mappings = [];

    /**
     * @var Constraints\Collection
     */
    private $constraint;

    /**
     * @var GroupEntity[]
     */
    private array $groups = [];

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly EncoderFactoryInterface $encoderFactory,
        private readonly ManagerRegistry $managerRegistry,
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly bool $mailVerificationRequired
    ) {
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
        if (empty($this->groups)) {
            $this->groups = $this->groupRepository->findAllAndIndexBy('gid');
        }
        $groups = !empty($userArray['groups']) ? explode('|', $userArray['groups']) : [GroupsConstant::GROUP_ID_USERS];
        $password = $userArray['pass'];
        unset($userArray['pass'], $userArray['sendmail'], $userArray['groups']);
        $userArray['activated'] = isset($userArray['activated']) ? empty($userArray['activated']) ? UsersConstant::ACTIVATED_PENDING_REG : 1 : 1;

        $userEntity = new UserEntity();
        foreach ($userArray as $fieldName => $fieldValue) {
            $setter = 'set' . ucfirst($fieldName);
            $userEntity->{$setter}($fieldValue);
        }
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
        $mapping->setVerifiedEmail(!$this->mailVerificationRequired);
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
