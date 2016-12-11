<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZAuthModule\Tests\Api;


use Zikula\ZAuthModule\Api\PasswordApi;


class PasswordApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PasswordApi
     */
    private $api;

    /**
     * CapabilityApiTest constructor.
     */
    public function setUp()
    {
        $this->api = new PasswordApi();
    }

    public function testGetHashedPassword()
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->assertEquals(72, strlen($hashedPass));
        $this->assertRegExp(';[0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~@#$%^*()_+-={}|\][];', $hashedPass);
        $this->assertEquals(2, substr_count($hashedPass, '$'));
    }

    public function testGeneratePassword()
    {
        $password = $this->api->generatePassword();
        $this->assertEquals(5, strlen($password));
        $this->assertNotRegExp('/[0oOl1iIj!|]/', $password);
    }

    public function testPasswordsMatch()
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->assertTrue($this->api->passwordsMatch('12345678', $hashedPass));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPasswordsMatchExceptionOnEmpty()
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch('', $hashedPass);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPasswordsMatchExceptionOnNull()
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch(null, $hashedPass);
    }
}
