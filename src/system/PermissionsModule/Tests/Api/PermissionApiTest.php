<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Tests\Api;

use Zikula\Common\Translator\IdentityTranslator;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Tests\Api\Fixtures\StubPermissionRepository;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;

class PermissionApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * for testing purposes only.
     */
    const RANDOM_USER_ID = 99;

    private $permRepo;

    private $user;

    private $userRepo;

    private $currentUserApi;

    /**
     * @var IdentityTranslator
     */
    private $translator;

    /**
     * VariableApiTest setUp.
     */
    public function setUp()
    {
        $this->permRepo = new StubPermissionRepository();
        $this->user = $this
            ->getMockBuilder('Zikula\UsersModule\Entity\UserEntity')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userRepo = $this
            ->getMockBuilder('Zikula\UsersModule\Entity\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->userRepo
            ->method('findByUids')
            ->with($this->anything())
            ->will($this->returnCallback(function (array $uids) {
                $groups = [];
                // getGroups returns [gid => $group, gid => $group, ...]
                if (in_array(self::RANDOM_USER_ID, $uids)) {
                    $groups = [GroupsConstant::GROUP_ID_USERS => []];
                } elseif (in_array(Constant::USER_ID_ADMIN, $uids)) {
                    $groups = [GroupsConstant::GROUP_ID_USERS => [], GroupsConstant::GROUP_ID_ADMIN => []];
                }
                $this->user
                    ->method('getGroups')
                    ->will($this->returnValue($groups));

                return [$this->user]; // must return an array of users.
            }));
        $this->currentUserApi = $this
            ->getMockBuilder(CurrentUserApiInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->translator = new IdentityTranslator();
    }

    /**
     * Call protected/private method of the api class.
     *
     * @param PermissionApi $api
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method
     * @return mixed Method return
     */
    private function invokeMethod($api, $methodName, array $parameters = [])
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
    public function testSetGroupPermsForUser($uid, $perms)
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->invokeMethod($api, 'setGroupPermsForUser', [$uid]);
        $this->assertEquals($perms, $api->getGroupPerms($uid));
    }

    /**
     * @covers PermissionApi::getSecurityLevel
     * @dataProvider secLevelProvider
     */
    public function testGetSecurityLevel($uid, $component, $instance, $expectedLevel)
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->invokeMethod($api, 'setGroupPermsForUser', [$uid]);
        $perms = $api->getGroupPerms($uid);
        $this->assertEquals($expectedLevel, $this->invokeMethod($api, 'getSecurityLevel', [$perms, $component, $instance]));
    }

    /**
     * @covers PermissionApi::hasPermission
     * @dataProvider uidProvider
     */
    public function testHasPermission($component, $instance, $level, $uid, $result)
    {
        $this->currentUserApi
            ->method('get')
            ->with($this->equalTo('uid'))
            ->will($this->returnCallback(function () use ($uid) {
                return isset($uid) ? $uid : Constant::USER_ID_ANONYMOUS;
            }));
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->assertEquals($result, $api->hasPermission($component, $instance, $level, $uid));
    }

    /**
     * @covers PermissionApi::accessLevelNames
     * @dataProvider accessLevelNamesProvider
     */
    public function testAccessLevelNames($expectedText, $level)
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $this->assertEquals($expectedText, $api->accessLevelNames($level));
    }

    /**
     * @covers PermissionApi::accessLevelNames()
     */
    public function testAccessLevelArray()
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $accessNames = [
            ACCESS_INVALID => $this->translator->__('Invalid'),
            ACCESS_NONE => $this->translator->__('No access'),
            ACCESS_OVERVIEW => $this->translator->__('Overview access'),
            ACCESS_READ => $this->translator->__('Read access'),
            ACCESS_COMMENT => $this->translator->__('Comment access'),
            ACCESS_MODERATE => $this->translator->__('Moderate access'),
            ACCESS_EDIT => $this->translator->__('Edit access'),
            ACCESS_ADD => $this->translator->__('Add access'),
            ACCESS_DELETE => $this->translator->__('Delete access'),
            ACCESS_ADMIN => $this->translator->__('Admin access'),
        ];
        $this->assertEquals($accessNames, $api->accessLevelNames());
    }

    /**
     * @covers PermissionApi::accessLevelNames()
     * @expectedException \InvalidArgumentException
     */
    public function testAccessLevelException()
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->currentUserApi, $this->translator);
        $api->accessLevelNames('foo');
    }

    public function permProvider()
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

    public function secLevelProvider()
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

    public function uidProvider()
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

    public function accessLevelNamesProvider()
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
