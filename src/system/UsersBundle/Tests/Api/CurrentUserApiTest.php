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

namespace Zikula\UsersBundle\Tests\Api;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Api\CurrentUserApi;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Entity\UserAttribute;
use Zikula\UsersBundle\Tests\Api\Fixtures\MockUserRepository;
use Zikula\UsersBundle\UsersConstant;

class CurrentUserApiTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $session;

    /**
     * @var MockUserRepository
     */
    private $userRepo;

    protected function setUp(): void
    {
        $this->userRepo = new MockUserRepository();
        $this->session = $this
            ->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->session
            ->method('start')->willReturn(true);
    }

    public function testIsLoggedIn(): void
    {
        $api = $this->getApi(42);
        $this->assertTrue($api->isLoggedIn());
        $this->assertEquals(42, $api->get('uid'));
        $this->assertEquals(42, $api->uid());
        $this->assertEquals('FooName', $api->get('uname'));
        $this->assertEquals('FooName', $api->uname());
        $this->assertEquals('foo@foo.com', $api->get('email'));
        $this->assertEquals('foo@foo.com', $api->email());
        $this->assertEquals(UsersConstant::ACTIVATED_ACTIVE, $api->get('activated'));

        $attributes = new ArrayCollection();
        /** @var User $user */
        $user = $this->userRepo->find(42);
        $attributes->set('legs', new UserAttribute($user, 'legs', 2));
        $this->assertEquals($attributes, $api->get('attributes'));
        $this->assertEmpty($api->get('foo'));
        $this->assertNull($api->foo());
    }

    public function testIsNotLoggedInNull(): void
    {
        $api = $this->getApi();
        $this->assertFalse($api->isLoggedIn());
        $this->assertNull($api->get('uid'));
        $this->assertNull($api->uid());
        $this->assertNull($api->get('uname'));
        $this->assertNull($api->uname());
    }

    public function testIsNotLoggedIn(): void
    {
        $api = $this->getApi(UsersConstant::USER_ID_ANONYMOUS);
        $this->assertFalse($api->isLoggedIn());
        $this->assertEquals(UsersConstant::USER_ID_ANONYMOUS, $api->get('uid'));
        $this->assertEquals(UsersConstant::USER_ID_ANONYMOUS, $api->uid());
        $this->assertEquals('guest', $api->get('uname'));
        $this->assertEquals('guest', $api->uname());
    }

    private function getApi($userId = null): CurrentUserApiInterface
    {
        $this->session->method('get')->with('uid')->willReturn($userId);

        $request = new Request();
        $request->setSession($this->session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new CurrentUserApi($requestStack, $this->userRepo);
    }
}
