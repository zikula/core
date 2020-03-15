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

namespace Zikula\ZAuthModule\Tests\Api;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Api\ApiInterface\UserCreationApiInterface;
use Zikula\ZAuthModule\Api\UserCreationApi;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class UserCreationApiTest extends KernelTestCase
{
    /**
     * @var UserCreationApiInterface
     */
    private $api;

    protected function setUp(): void
    {
        // load test env vars
        $dotenv = new Dotenv();
        $dotenv->load('.env.test');

        self::bootKernel();
        $container = self::$container;
        $validator = $container->get('validator');

        $currentUserApi = $this->createMock(CurrentUserApiInterface::class);
        $currentUserApi->method('get')->willReturn(UsersConstant::USER_ID_ADMIN);
        $encoder = $this->createPasswordEncoder();
        $encoderFactory = $this->createEncoderFactory($encoder);
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $variableApi = $this->createMock(VariableApiInterface::class);
        $variableApi->method('get')->willReturn(ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED);
        $groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $groupRepository->method('findAllAndIndexBy')->willReturn($this->createGroups());
        $this->api = new UserCreationApi(
            $validator,
            $currentUserApi,
            $encoderFactory,
            $managerRegistry,
            $variableApi,
            $groupRepository
        );
    }

    /**
     * @dataProvider isValidUserDataProvider
     */
    public function testIsValidUserData($expected, array $user): void
    {
        $this->assertEquals($expected, $this->api->isValidUserData($user));
    }

    public function testIsValidUserDataArray(): void
    {
        $users = $this->getValidUsersArray();
        $errors = $this->api->isValidUserDataArray($users);
        $this->assertTrue($errors);

        $users = $this->getInvalidUsersArray();
        $errors = $this->api->isValidUserDataArray($users);
        $this->assertNotTrue($errors);
        $this->assertInstanceOf(ConstraintViolationList::class, $errors);
        $this->assertEquals(4, $errors->count());
        $this->assertEquals('This value is not a valid email address.', $errors[0]->getMessage());
        $this->assertEquals('This value is too short. It should have 5 characters or more.', $errors[1]->getMessage());
        $this->assertEquals('The value you selected is not a valid choice.', $errors[2]->getMessage());
        $this->assertEquals('This value is not valid.', $errors[3]->getMessage());
    }

    public function testCreateUser(): void
    {
        $this->api->createUser([
            'uname' => 'foo',
            'email' => 'foo@bar.com',
            'pass' => '12345678'
        ]);

        $users = $this->api->getCreatedUsers();
        $hash = array_key_first($users);
        /** @var UserEntity $newUser */
        $newUser = $users[$hash];
        $this->assertEquals('foo', $newUser->getUname());
        $this->assertEquals('foo@bar.com', $newUser->getEmail());
        $this->assertEquals(1, $newUser->getActivated());
        $this->assertEquals(1, $newUser->getGroups()->count());
        $this->assertEquals(null, $newUser->getUid());

        $mappings = $this->api->getCreatedMappings();
        $this->assertArrayHasKey($hash, $mappings);
        /** @var AuthenticationMappingEntity $newMapping */
        $newMapping = $mappings[$hash];
        $this->assertEquals('foo', $newMapping->getUname());
        $this->assertEquals('foo@bar.com', $newMapping->getEmail());
        $this->assertEquals('thisIsAnEncodedPassword!', $newMapping->getPass());
        $this->assertEquals(ZAuthConstant::AUTHENTICATION_METHOD_EITHER, $newMapping->getMethod());
        $this->assertNotEquals(ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED, $newMapping->isVerifiedEmail());
    }

    public function testCreateUsers(): void
    {
        $users = $this->getValidUsersArray();
        $errors = $this->api->createUsers($users);
        $this->assertCount(0, $errors);
        $this->assertCount(10, $this->api->getCreatedUsers());
        $this->assertCount(10, $this->api->getCreatedMappings());

        $this->api->clearCreated();
        $users = $this->getInvalidUsersArray();
        $errors = $this->api->createUsers($users);
        $this->assertCount(4, $errors);
        $this->assertCount(6, $this->api->getCreatedUsers());
        $this->assertCount(6, $this->api->getCreatedMappings());
        $this->assertEquals('Row 0 with email `foo0@bar` and uname `foo0` is invalid and was rejected.', $errors[0]);
        $this->assertEquals('Row 2 with email `foo2@bar.com` and uname `foo2` is invalid and was rejected.', $errors[1]);
        $this->assertEquals('Row 3 with email `foo3@bar.com` and uname `foo3` is invalid and was rejected.', $errors[2]);
        $this->assertEquals('Row 9 with email `foo9@bar.com` and uname `foo9` is invalid and was rejected.', $errors[3]);
    }

    public function isValidUserDataProvider()
    {
        return [
            ['This field is missing.', []],
            ['This field is missing.', ['uname' => 'foo', 'pass' => '12345678']],
            ['This field is missing.', ['uname' => 'foo', 'pass' => '12345678', 'bar' => 'foo']],
            ['This field is missing.', ['uname' => 'foo', 'email' => 'foo@bar.com']],
            ['This value is not a valid email address.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo']],
            ['This value should not be blank.', ['uname' => '', 'pass' => '12345678', 'email' => 'foo@bar.com']],
            ['This value is too short. It should have 5 characters or more.', ['uname' => 'foo', 'pass' => '123', 'email' => 'foo@bar.com']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com']],

            ['The value you selected is not a valid choice.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => 2]],
            ['The value you selected is not a valid choice.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => '2']],
            ['This value should be of type numeric.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => 'foo']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => '1']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => 0]],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => 1]],
            ['The value you selected is not a valid choice.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'sendmail' => 2]],
            ['The value you selected is not a valid choice.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'sendmail' => '2']],
            ['This value should be of type numeric.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'sendmail' => 'foo']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'sendmail' => '1']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'sendmail' => 0]],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'sendmail' => 1]],
            ['This value should be of type string.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'groups' => 1]],
            ['This value is not valid.', ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'groups' => 'users']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'groups' => '1']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'groups' => '1|2|3']],

            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => '1', 'sendmail' => '1', 'groups' => '1']],
            [true, ['uname' => 'foo', 'pass' => '12345678', 'email' => 'foo@bar.com', 'activated' => 1, 'sendmail' => 1, 'groups' => '1|2|12345']],
        ];
    }

    private function getValidUsersArray(): array
    {
        return [
            ['uname' => 'foo0', 'pass' => '12345678', 'email' => 'foo0@bar.com'],
            ['uname' => 'foo1', 'pass' => '12345678', 'email' => 'foo1@bar.com', 'activated' => '1'],
            ['uname' => 'foo2', 'pass' => '12345678', 'email' => 'foo2@bar.com', 'activated' => 0],
            ['uname' => 'foo3', 'pass' => '12345678', 'email' => 'foo3@bar.com', 'activated' => 1],
            ['uname' => 'foo4', 'pass' => '12345678', 'email' => 'foo4@bar.com', 'sendmail' => '1'],
            ['uname' => 'foo5', 'pass' => '12345678', 'email' => 'foo5@bar.com', 'sendmail' => 1],
            ['uname' => 'foo6', 'pass' => '12345678', 'email' => 'foo6@bar.com', 'groups' => '1|57'],
            ['uname' => 'foo7', 'pass' => '12345678', 'email' => 'foo7@bar.com', 'groups' => '1|2'],
            ['uname' => 'foo8', 'pass' => '12345678', 'email' => 'foo8@bar.com', 'activated' => '1', 'sendmail' => '1', 'groups' => '1'],
            ['uname' => 'foo9', 'pass' => '12345678', 'email' => 'foo9@bar.com', 'activated' => 1, 'sendmail' => 1, 'groups' => '1|2|3'],
        ];
    }

    private function getInvalidUsersArray(): array
    {
        return [
            ['uname' => 'foo0', 'pass' => '12345678', 'email' => 'foo0@bar'], // invalid
            ['uname' => 'foo1', 'pass' => '12345678', 'email' => 'foo1@bar.com', 'activated' => '1'],
            ['uname' => 'foo2', 'pass' => '123', 'email' => 'foo2@bar.com', 'activated' => 0], // invalid
            ['uname' => 'foo3', 'pass' => '12345678', 'email' => 'foo3@bar.com', 'activated' => 9], // invalid
            ['uname' => 'foo4', 'pass' => '12345678', 'email' => 'foo4@bar.com', 'sendmail' => '1'],
            ['uname' => 'foo5', 'pass' => '12345678', 'email' => 'foo5@bar.com', 'sendmail' => 1],
            ['uname' => 'foo6', 'pass' => '12345678', 'email' => 'foo6@bar.com', 'groups' => '1|57'],
            ['uname' => 'foo7', 'pass' => '12345678', 'email' => 'foo7@bar.com', 'groups' => '1|2'],
            ['uname' => 'foo8', 'pass' => '12345678', 'email' => 'foo8@bar.com', 'activated' => '1', 'sendmail' => '1', 'groups' => '1'],
            ['uname' => 'foo9', 'pass' => '12345678', 'email' => 'foo9@bar.com', 'activated' => 1, 'sendmail' => 1, 'groups' => 'users'], // invalid
        ];
    }


    protected function createPasswordEncoder($isPasswordValid = true)
    {
        $mock = $this->getMockBuilder(PasswordEncoderInterface::class)->getMock();
        $mock->method('encodePassword')->willReturn('thisIsAnEncodedPassword!');

        return $mock;
    }

    protected function createEncoderFactory($encoder = null)
    {
        $mock = $this->getMockBuilder(EncoderFactoryInterface::class)->getMock();

        $mock
            ->expects($this->any())
            ->method('getEncoder')
            ->willReturn($encoder)
        ;

        return $mock;
    }

    protected function createGroups(): array
    {
        $records = [
            [
                'gid' => GroupsConstant::GROUP_ID_USERS,
                'name' => 'Users',
                'description' => 'By default, all users are made members of this group.'
            ],
            [
                'gid' => GroupsConstant::GROUP_ID_ADMIN,
                'name' => 'Administrators',
                'description' => 'Group of administrators of this site.',
            ]
        ];

        $groups = [];
        foreach ($records as $record) {
            $group = new GroupEntity();
            $group->setGid($record['gid']);
            $group->setName($record['name']);
            $group->setDescription($record['description']);
            $groups[$record['gid']] = $group;
        }

        return $groups;
    }
}
