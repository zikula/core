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
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\UserAttributeEntity;
use Zikula\UsersModule\Tests\Api\Fixtures\MockUserRepository;

class CurrentUserApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker
     */
    private $session;

    /**
     * @var MockUserRepository
     */
    private $userRepo;

    /**
     * CurrentUserApiTest setUp.
     */
    public function setUp()
    {
        $this->userRepo = new MockUserRepository();
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
        $this->assertEquals(Constant::ACTIVATED_ACTIVE, $api->get('activated'));
        $attributes = new ArrayCollection();
        $user = $this->userRepo->find(42);
        $attributes->set('legs', new UserAttributeEntity($user, 'legs', 2));
        $this->assertEquals($attributes, $api->get('attributes'));
        $this->assertNull($api->get('foo'));
        $this->assertNull($api->foo());
    }

    public function testIsNotLoggedInNull()
    {
        $api = $this->getApi();
        $this->assertFalse($api->isLoggedIn());
        $this->assertNull($api->get('uid'));
        $this->assertNull($api->uid());
        $this->assertNull($api->get('uname'));
        $this->assertNull($api->uname());
    }

    public function testIsNotLoggedIn()
    {
        $api = $this->getApi(Constant::USER_ID_ANONYMOUS);
        $this->assertFalse($api->isLoggedIn());
        $this->assertEquals(Constant::USER_ID_ANONYMOUS, $api->get('uid'));
        $this->assertEquals(Constant::USER_ID_ANONYMOUS, $api->uid());
        $this->assertEquals('guest', $api->get('uname'));
        $this->assertEquals('guest', $api->uname());
    }

    private function getApi($uid = null)
    {
        $this->session->method('get')->with('uid')->willReturn($uid);

        return new CurrentUserApi($this->session, $this->userRepo);
    }
}
