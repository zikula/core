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

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Tests\Api\Fixtures\StubPermissionRepository;

class PermissionApiTest extends \PHPUnit_Framework_TestCase
{
    private $permRepo;

    private $user;

    private $userRepo;

    private $session;

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
            ->will($this->returnCallback(function (array $uids) /*use ($user)*/ {
                $groups = [PermissionApi::UNREGISTERED_USER_GROUP => []];
                if (in_array(1, $uids)) { // guest
                    $groups = [1 => []]; // gid => $group
                } elseif (in_array(2, $uids)) { // admin
                    $groups = [1 => [], 2 => []]; // gid => $group
                }
                $this->user
                    ->method('getGroups')
                    ->will($this->returnValue($groups));

                return [$this->user]; // must return an array of users.
            }));
        $this->session = $this
            ->getMockBuilder(SessionInterface::class)
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
     * @covers       PermissionApi::setGroupPermsForUser
     * @dataProvider permProvider
     */
    public function testSetGroupPermsForUser($uid, $perms)
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->session, $this->translator);
        $this->invokeMethod($api, 'setGroupPermsForUser', [$uid]);
        $this->assertEquals($perms, $api->getGroupPerms($uid));
    }

    /**
     * @covers       PermissionApi::getSecurityLevel
     * @dataProvider secLevelProvider
     */
    public function testGetSecurityLevel($uid, $component, $instance, $expectedLevel)
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->session, $this->translator);
        $this->invokeMethod($api, 'setGroupPermsForUser', [$uid]);
        $perms = $api->getGroupPerms($uid);
        $this->assertEquals($expectedLevel, $this->invokeMethod($api, 'getSecurityLevel', [$perms, $component, $instance]));
    }

    /**
     * @covers       PermissionApi::hasPermission
     * @dataProvider uidProvider
     */
    public function testHasPermission($component, $instance, $level, $uid, $result)
    {
        $this->session
            ->method('get')
            ->with($this->equalTo('uid'))
            ->will($this->returnCallback(function () use ($uid) {
                return isset($uid) ? $uid : false; // when no uid is available, Zikula_Session::get() returns `false`
            }));
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->session, $this->translator);
        $this->assertEquals($result, $api->hasPermission($component, $instance, $level, $uid));
    }

    /**
     * @covers       PermissionApi::accessLevelNames
     * @dataProvider accessLevelNamesProvider
     */
    public function testAccessLevelNames($expectedText, $level)
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->session, $this->translator);
        $this->assertEquals($expectedText, $api->accessLevelNames($level));
    }

    /**
     * @covers      PermissionApi::accessLevelNames()
     */
    public function testAccessLevelArray()
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->session, $this->translator);
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
     * @covers      PermissionApi::accessLevelNames()
     * @expectedException \InvalidArgumentException
     */
    public function testAccessLevelException()
    {
        $api = new PermissionApi($this->permRepo, $this->userRepo, $this->session, $this->translator);
        $api->accessLevelNames('foo');
    }

    public function permProvider()
    {
        return [
            [2/* SITE ADMIN */, [
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
            [PermissionApi::UNREGISTERED_USER, [
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
            [99/* Random UID */, [
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
            [1/* GUEST */, [
                ['component' => 'ExtendedMenublock:.*:.*',
                    'instance' => '1:1:.*',
                    'level' => ACCESS_NONE],
                ['component' => '.*',
                    'instance' => '.*',
                    'level' => ACCESS_COMMENT],
            ]],
        ];
    }

    public function secLevelProvider()
    {
        return [
            [2, '.*', '.*', ACCESS_ADMIN],
            [1, '.*', '.*', ACCESS_COMMENT],
            [PermissionApi::UNREGISTERED_USER, '.*', '.*', ACCESS_READ],

            [2, 'ExtendedMenublock::', '1:1:', ACCESS_ADMIN],
            [1, 'ExtendedMenublock::', '1:1:', ACCESS_NONE],
            [PermissionApi::UNREGISTERED_USER, 'ExtendedMenublock::', '1:1:', ACCESS_NONE],

            [2, 'ExtendedMenublock::', '1:2:', ACCESS_ADMIN],
            [1, 'ExtendedMenublock::', '1:2:', ACCESS_COMMENT],
            [PermissionApi::UNREGISTERED_USER, 'ExtendedMenublock::', '1:2:', ACCESS_NONE],
        ];
    }

    public function uidProvider()
    {
        return [
            ['.*', '.*', ACCESS_OVERVIEW, 2, true],
            ['.*', '.*', ACCESS_READ, 2, true],
            ['.*', '.*', ACCESS_COMMENT, 2, true],
            ['.*', '.*', ACCESS_MODERATE, 2, true],
            ['.*', '.*', ACCESS_EDIT, 2, true],
            ['.*', '.*', ACCESS_ADD, 2, true],
            ['.*', '.*', ACCESS_DELETE, 2, true],
            ['.*', '.*', ACCESS_ADMIN, 2, true],

            ['.*', '.*', ACCESS_OVERVIEW, 1, true],
            ['.*', '.*', ACCESS_READ, 1, true],
            ['.*', '.*', ACCESS_COMMENT, 1, true],
            ['.*', '.*', ACCESS_MODERATE, 1, false],
            ['.*', '.*', ACCESS_EDIT, 1, false],
            ['.*', '.*', ACCESS_ADD, 1, false],
            ['.*', '.*', ACCESS_DELETE, 1, false],
            ['.*', '.*', ACCESS_ADMIN, 1, false],

            ['.*', '.*', ACCESS_OVERVIEW, null, true],
            ['.*', '.*', ACCESS_READ, null, true],
            ['.*', '.*', ACCESS_COMMENT, null, false],
            ['.*', '.*', ACCESS_MODERATE, null, false],
            ['.*', '.*', ACCESS_EDIT, null, false],
            ['.*', '.*', ACCESS_ADD, null, false],
            ['.*', '.*', ACCESS_DELETE, null, false],
            ['.*', '.*', ACCESS_ADMIN, null, false],

            ['.*', '.*', ACCESS_OVERVIEW, 99, true],
            ['.*', '.*', ACCESS_READ, 99, true],
            ['.*', '.*', ACCESS_COMMENT, 99, false],
            ['.*', '.*', ACCESS_MODERATE, 99, false],
            ['.*', '.*', ACCESS_EDIT, 99, false],
            ['.*', '.*', ACCESS_ADD, 99, false],
            ['.*', '.*', ACCESS_DELETE, 99, false],
            ['.*', '.*', ACCESS_ADMIN, 99, false],

            ['ExtendedMenublock::', '1:1:', ACCESS_OVERVIEW, 2, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_READ, 2, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_COMMENT, 2, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_MODERATE, 2, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_EDIT, 2, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADD, 2, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_DELETE, 2, true],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADMIN, 2, true],

            ['ExtendedMenublock::', '1:1:', ACCESS_OVERVIEW, 1, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_READ, 1, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_COMMENT, 1, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_MODERATE, 1, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_EDIT, 1, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADD, 1, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_DELETE, 1, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADMIN, 1, false],

            ['ExtendedMenublock::', '1:1:', ACCESS_OVERVIEW, null, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_READ, null, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_COMMENT, null, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_MODERATE, null, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_EDIT, null, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADD, null, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_DELETE, null, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADMIN, null, false],

            ['ExtendedMenublock::', '1:1:', ACCESS_OVERVIEW, 99, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_READ, 99, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_COMMENT, 99, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_MODERATE, 99, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_EDIT, 99, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADD, 99, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_DELETE, 99, false],
            ['ExtendedMenublock::', '1:1:', ACCESS_ADMIN, 99, false],

            ['ExtendedMenublock::', '1:2:', ACCESS_OVERVIEW, 2, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_READ, 2, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_COMMENT, 2, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_MODERATE, 2, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_EDIT, 2, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADD, 2, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_DELETE, 2, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADMIN, 2, true],

            ['ExtendedMenublock::', '1:2:', ACCESS_OVERVIEW, 1, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_READ, 1, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_COMMENT, 1, true],
            ['ExtendedMenublock::', '1:2:', ACCESS_MODERATE, 1, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_EDIT, 1, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADD, 1, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_DELETE, 1, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADMIN, 1, false],

            ['ExtendedMenublock::', '1:2:', ACCESS_OVERVIEW, null, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_READ, null, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_COMMENT, null, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_MODERATE, null, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_EDIT, null, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADD, null, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_DELETE, null, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADMIN, null, false],

            ['ExtendedMenublock::', '1:2:', ACCESS_OVERVIEW, 99, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_READ, 99, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_COMMENT, 99, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_MODERATE, 99, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_EDIT, 99, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADD, 99, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_DELETE, 99, false],
            ['ExtendedMenublock::', '1:2:', ACCESS_ADMIN, 99, false],

            ['ExtendedMenublock::', '1:3:', ACCESS_OVERVIEW, 2, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_READ, 2, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_COMMENT, 2, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_MODERATE, 2, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_EDIT, 2, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADD, 2, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_DELETE, 2, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADMIN, 2, true],

            ['ExtendedMenublock::', '1:3:', ACCESS_OVERVIEW, 1, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_READ, 1, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_COMMENT, 1, true],
            ['ExtendedMenublock::', '1:3:', ACCESS_MODERATE, 1, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_EDIT, 1, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADD, 1, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_DELETE, 1, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADMIN, 1, false],

            ['ExtendedMenublock::', '1:3:', ACCESS_OVERVIEW, null, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_READ, null, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_COMMENT, null, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_MODERATE, null, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_EDIT, null, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADD, null, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_DELETE, null, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADMIN, null, false],

            ['ExtendedMenublock::', '1:3:', ACCESS_OVERVIEW, 99, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_READ, 99, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_COMMENT, 99, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_MODERATE, 99, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_EDIT, 99, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADD, 99, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_DELETE, 99, false],
            ['ExtendedMenublock::', '1:3:', ACCESS_ADMIN, 99, false],
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
