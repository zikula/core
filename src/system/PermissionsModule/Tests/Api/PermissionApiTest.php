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

namespace Zikula\PermissionsModule\Tests\Api;

use PHPUnit\Framework\Error\Notice;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Constant;

class PermissionApiTest extends AbstractPermissionTestCase
{
    /**
     * Call protected/private method of the api class.
     *
     * @return mixed Method return
     * @throws \ReflectionException
     */
    private function invokeMethod(PermissionApiInterface $api, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($api));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($api, $parameters);
    }

    /**
     * @covers PermissionApi::setGroupPermsForUser
     * @dataProvider permProvider
     */
    public function testSetGroupPermsForUser(int $userId, array $perms): void
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->invokeMethod($api, 'setGroupPermsForUser', [$userId]);
        $this->assertEquals($perms, $api->getGroupPerms($userId));
    }

    /**
     * @covers PermissionApi::getSecurityLevel
     * @dataProvider secLevelProvider
     */
    public function testGetSecurityLevel(int $userId, string $component, string $instance, int $expectedLevel): void
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->invokeMethod($api, 'setGroupPermsForUser', [$userId]);
        $perms = $api->getGroupPerms($userId);
        $this->assertEquals($expectedLevel, $this->invokeMethod($api, 'getSecurityLevel', [$perms, $component, $instance]));
    }

    /**
     * @covers PermissionApi::hasPermission
     * @dataProvider uidProvider
     */
    public function testHasPermission(string $component, string $instance, int $level, int $userId, bool $result): void
    {
        $this->currentUserApi
            ->method('get')
            ->with($this->equalTo('uid'))
            ->willReturnCallback(static function () use ($userId) {
                return $userId ?? Constant::USER_ID_ANONYMOUS;
            });
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->assertEquals($result, $api->hasPermission($component, $instance, $level, $userId));
    }

    /**
     * @covers PermissionApi::accessLevelNames
     * @dataProvider accessLevelNamesProvider
     */
    public function testAccessLevelNames(string $expectedText, int $level): void
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->assertEquals($expectedText, $api->accessLevelNames($level));
    }

    /**
     * @covers PermissionApi::accessLevelNames()
     */
    public function testAccessLevelArray(): void
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $accessNames = [
            ACCESS_INVALID => $this->translator->trans('Invalid'),
            ACCESS_NONE => $this->translator->trans('No access'),
            ACCESS_OVERVIEW => $this->translator->trans('Overview access'),
            ACCESS_READ => $this->translator->trans('Read access'),
            ACCESS_COMMENT => $this->translator->trans('Comment access'),
            ACCESS_MODERATE => $this->translator->trans('Moderate access'),
            ACCESS_EDIT => $this->translator->trans('Edit access'),
            ACCESS_ADD => $this->translator->trans('Add access'),
            ACCESS_DELETE => $this->translator->trans('Delete access'),
            ACCESS_ADMIN => $this->translator->trans('Admin access'),
        ];
        $this->assertEquals($accessNames, $api->accessLevelNames());
    }

    /**
     * @covers PermissionApi::accessLevelNames()
     */
    public function testAccessLevelException(): void
    {
        $this->expectException(Notice::class);
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $api->accessLevelNames(99);
    }

    public function permProvider(): array
    {
        return [
            [Constant::USER_ID_ADMIN, [
                ['component' => '.*',
                    'instance' => '.*',
                    'level' => ACCESS_ADMIN],
                ['component' => 'ExtendedMenublock:.*:.*',
                    'instance' => '1:1:.*',
                    'level' => ACCESS_NONE],
                ['component' => '.*',
                    'instance' => '.*',
                    'level' => ACCESS_COMMENT],
            ]],
            [self::RANDOM_USER_ID, [
                ['component' => 'ExtendedMenublock:.*:.*',
                    'instance' => '1:1:.*',
                    'level' => ACCESS_NONE],
                ['component' => '.*',
                    'instance' => '.*',
                    'level' => ACCESS_COMMENT],
            ]],
            [Constant::USER_ID_ANONYMOUS, [
                ['component' => 'ExtendedMenublock:.*:.*',
                    'instance' => '1:1:.*',
                    'level' => ACCESS_NONE],
                ['component' => 'ExtendedMenublock:.*:.*',
                    'instance' => '1:(1|2|3):.*',
                    'level' => ACCESS_NONE],
                ['component' => '.*',
                    'instance' => '.*',
                    'level' => ACCESS_READ],
            ]],
        ];
    }

    public function secLevelProvider(): array
    {
        return [
            [Constant::USER_ID_ADMIN, '.*', '.*', ACCESS_ADMIN],
            [Constant::USER_ID_ANONYMOUS, '.*', '.*', ACCESS_READ],

            [Constant::USER_ID_ADMIN, 'ExtendedMenublock::', '1:1:', ACCESS_ADMIN],
            [Constant::USER_ID_ANONYMOUS, 'ExtendedMenublock::', '1:1:', ACCESS_NONE],

            [Constant::USER_ID_ADMIN, 'ExtendedMenublock::', '1:2:', ACCESS_ADMIN],
            [Constant::USER_ID_ANONYMOUS, 'ExtendedMenublock::', '1:2:', ACCESS_NONE],
        ];
    }

    public function uidProvider(): array
    {
        return [
            ['.*', '.*', ACCESS_OVERVIEW, Constant::USER_ID_ADMIN, true], // #0
            ['.*', '.*', ACCESS_READ, Constant::USER_ID_ADMIN, true],
            ['.*', '.*', ACCESS_COMMENT, Constant::USER_ID_ADMIN, true],
            ['.*', '.*', ACCESS_MODERATE, Constant::USER_ID_ADMIN, true],
            ['.*', '.*', ACCESS_EDIT, Constant::USER_ID_ADMIN, true],
            ['.*', '.*', ACCESS_ADD, Constant::USER_ID_ADMIN, true],
            ['.*', '.*', ACCESS_DELETE, Constant::USER_ID_ADMIN, true],
            ['.*', '.*', ACCESS_ADMIN, Constant::USER_ID_ADMIN, true],

            ['.*', '.*', ACCESS_OVERVIEW, Constant::USER_ID_ANONYMOUS, true], // #8
            ['.*', '.*', ACCESS_READ, Constant::USER_ID_ANONYMOUS, true],
            ['.*', '.*', ACCESS_COMMENT, Constant::USER_ID_ANONYMOUS, false],
            ['.*', '.*', ACCESS_MODERATE, Constant::USER_ID_ANONYMOUS, false],
            ['.*', '.*', ACCESS_EDIT, Constant::USER_ID_ANONYMOUS, false],
            ['.*', '.*', ACCESS_ADD, Constant::USER_ID_ANONYMOUS, false],
            ['.*', '.*', ACCESS_DELETE, Constant::USER_ID_ANONYMOUS, false],
            ['.*', '.*', ACCESS_ADMIN, Constant::USER_ID_ANONYMOUS, false],

            ['.*', '.*', ACCESS_OVERVIEW, self::RANDOM_USER_ID, true], // #16
            ['.*', '.*', ACCESS_READ, self::RANDOM_USER_ID, true],
            ['.*', '.*', ACCESS_COMMENT, self::RANDOM_USER_ID, true],
            ['.*', '.*', ACCESS_MODERATE, self::RANDOM_USER_ID, false],
            ['.*', '.*', ACCESS_EDIT, self::RANDOM_USER_ID, false],
            ['.*', '.*', ACCESS_ADD, self::RANDOM_USER_ID, false],
            ['.*', '.*', ACCESS_DELETE, self::RANDOM_USER_ID, false],
            ['.*', '.*', ACCESS_ADMIN, self::RANDOM_USER_ID, false],

            ['ExtendedMenublock::', '1:1:', ACCESS_OVERVIEW, Constant::USER_ID_ADMIN, true], // #24
            ['ExtendedMenublock::', '1:1:', ACCESS_READ, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_COMMENT, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_MODERATE, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_EDIT, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADD, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_DELETE, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADMIN, Constant::USER_ID_ADMIN, true],

            ['ExtendedMenublock::', '1:1:', ACCESS_OVERVIEW, Constant::USER_ID_ANONYMOUS, false], // #32
            ['ExtendedMenublock::', '1:1:', ACCESS_READ, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_COMMENT, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_MODERATE, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_EDIT, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADD, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_DELETE, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADMIN, Constant::USER_ID_ANONYMOUS, false],

            ['ExtendedMenublock::', '1:1:', ACCESS_OVERVIEW, self::RANDOM_USER_ID, false], // #40
            ['ExtendedMenublock::', '1:1:', ACCESS_READ, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_COMMENT, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_MODERATE, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_EDIT, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADD, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_DELETE, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADMIN, self::RANDOM_USER_ID, false],

            ['ExtendedMenublock::', '1:2:', ACCESS_OVERVIEW, Constant::USER_ID_ADMIN, true], // #48
            ['ExtendedMenublock::', '1:2:', ACCESS_READ, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_COMMENT, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_MODERATE, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_EDIT, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADD, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_DELETE, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADMIN, Constant::USER_ID_ADMIN, true],

            ['ExtendedMenublock::', '1:2:', ACCESS_OVERVIEW, Constant::USER_ID_ANONYMOUS, false], // #56
            ['ExtendedMenublock::', '1:2:', ACCESS_READ, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_COMMENT, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_MODERATE, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_EDIT, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADD, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_DELETE, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADMIN, Constant::USER_ID_ANONYMOUS, false],

            ['ExtendedMenublock::', '1:2:', ACCESS_OVERVIEW, self::RANDOM_USER_ID, true], // #64
            ['ExtendedMenublock::', '1:2:', ACCESS_READ, self::RANDOM_USER_ID, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_COMMENT, self::RANDOM_USER_ID, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_MODERATE, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_EDIT, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADD, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_DELETE, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADMIN, self::RANDOM_USER_ID, false],

            ['ExtendedMenublock::', '1:3:', ACCESS_OVERVIEW, Constant::USER_ID_ADMIN, true], // #72
            ['ExtendedMenublock::', '1:3:', ACCESS_READ, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_COMMENT, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_MODERATE, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_EDIT, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADD, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_DELETE, Constant::USER_ID_ADMIN, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADMIN, Constant::USER_ID_ADMIN, true],

            ['ExtendedMenublock::', '1:3:', ACCESS_OVERVIEW, Constant::USER_ID_ANONYMOUS, false], // #80
            ['ExtendedMenublock::', '1:3:', ACCESS_READ, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_COMMENT, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_MODERATE, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_EDIT, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADD, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_DELETE, Constant::USER_ID_ANONYMOUS, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADMIN, Constant::USER_ID_ANONYMOUS, false],

            ['ExtendedMenublock::', '1:3:', ACCESS_OVERVIEW, self::RANDOM_USER_ID, true], // #88
            ['ExtendedMenublock::', '1:3:', ACCESS_READ, self::RANDOM_USER_ID, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_COMMENT, self::RANDOM_USER_ID, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_MODERATE, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_EDIT, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADD, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_DELETE, self::RANDOM_USER_ID, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADMIN, self::RANDOM_USER_ID, false],
        ];
    }

    public function accessLevelNamesProvider(): array
    {
        return [
            ['Invalid', ACCESS_INVALID],
            ['No access', ACCESS_NONE],
            ['Overview access', ACCESS_OVERVIEW],
            ['Read access', ACCESS_READ],
            ['Comment access', ACCESS_COMMENT],
            ['Moderate access', ACCESS_MODERATE],
            ['Edit access', ACCESS_EDIT],
            ['Add access', ACCESS_ADD],
            ['Delete access', ACCESS_DELETE],
            ['Admin access', ACCESS_ADMIN],
        ];
    }
}
