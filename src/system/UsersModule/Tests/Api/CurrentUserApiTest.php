<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Tests\Api;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserAttributeEntity;
use Zikula\UsersModule\Entity\UserEntity;

class CurrentUserApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private $session;

    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private $userRepo;

    /**
     * VariableApiTest setUp.
     */
    public function setUp()
    {
        $this->user = new UserEntity();
        $this->user->setUname('FooName');
        $this->user->setEmail('foo@foo.com');
        $this->user->setActivated(1);
        $this->user->setAttribute('legs', 2);

        $this->userRepo = $this
            ->getMockBuilder(UserRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userRepo
            ->method('find')
            ->with($this->logicalNot($this->isNull()))
            ->will($this->returnCallback(function ($uid) {
                $this->user->setUid($uid);

                return $this->user;
            }));
        $this->session = $this
            ->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->session
            ->method('start')->willReturn(true);
    }

    public function testIsLoggedIn()
    {
        $api = $this->getApi(42);
        $this->assertTrue($api->isLoggedIn());
        $this->assertEquals(42, $api->get('uid'));
        $this->assertEquals(42, $api->uid());
        $this->assertEquals('FooName', $api->get('uname'));
        $this->assertEquals('FooName', $api->uname());
        $this->assertEquals('foo@foo.com', $api->get('email'));
        $this->assertEquals('foo@foo.com', $api->email());
        $this->assertEquals(1, $api->get('activated'));
        $attributes = new ArrayCollection();
        $this->user->setUid(42);
        $attributes->set('legs', new UserAttributeEntity($this->user, 'legs', 2));
        $this->assertEquals($attributes, $api->get('attributes'));
        $this->assertNull($api->get('foo'));
        $this->assertNull($api->foo());
    }

    public function testIsNotLoggedIn()
    {
        $api = $this->getApi();
        $this->assertFalse($api->isLoggedIn());
        $this->assertNull($api->get('uid'));
        $this->assertNull($api->uid());
        $this->assertNull($api->get('uname'));
        $this->assertNull($api->uname());
    }

    private function getApi($uid = null)
    {
        $this->session->method('get')->with('uid')->willReturn($uid);

        return new CurrentUserApi($this->session, $this->userRepo);
    }
}
