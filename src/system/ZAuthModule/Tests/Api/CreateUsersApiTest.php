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

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Api\ApiInterface\CreateUsersApiInterface;
use Zikula\ZAuthModule\Api\CreateUsersApi;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class CreateUsersApiTest extends KernelTestCase
{
    /**
     * @var CreateUsersApiInterface
     */
    private $api;

    protected function setUp(): void
    {
        // load test env vars
        $dotenv = new Dotenv();
        $dotenv->load('.env.test');

        self::bootKernel();
        $container = self::$container;
        $this->api = $container->get(CreateUsersApi::class);
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
        $this->assertEquals('This value is too short. It should have 8 characters or more.', $errors[1]->getMessage());
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
        $userGroup = self::$container->get(GroupRepositoryInterface::class)->find(GroupsConstant::GROUP_ID_USERS);

        $users = $this->api->getCreatedUsers();
        $hash = array_key_first($users);
        /** @var UserEntity $newUser */
        $newUser = $users[$hash];
        $this->assertEquals('foo', $newUser->getUname());
        $this->assertEquals('foo@bar.com', $newUser->getEmail());
        $this->assertEquals(1, $newUser->getActivated());
        $this->assertEquals(new ArrayCollection([$userGroup]), $newUser->getGroups());
        $this->assertEquals(null, $newUser->getUid());

        $mappings = $this->api->getCreatedMappings();
        $this->assertArrayHasKey($hash, $mappings);
        /** @var AuthenticationMappingEntity $newMapping */
        $newMapping = $mappings[$hash];
        $this->assertEquals('foo', $newMapping->getUname());
        $this->assertEquals('foo@bar.com', $newMapping->getEmail());
        $passwordEncoder = self::$container->get(EncoderFactoryInterface::class)->getEncoder(AuthenticationMappingEntity::class);
        $this->assertEquals(true, $passwordEncoder->isPasswordValid($newMapping->getPass(), '12345678', null));
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
            ['This value is too short. It should have 8 characters or more.', ['uname' => 'foo', 'pass' => '123', 'email' => 'foo@bar.com']],
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
}
