<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Tests\Api;

use Zikula\ZAuthModule\Api\PasswordApi;

class PasswordApiTest extends \PHPUnit_Framework_TestCase
{
    const ALLOWED_CHARS_REGEXP = ';[0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~@#$%^*()_+-={}|\][];';

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

    /**
     * @covers PasswordApi::getHashedPassword()
     */
    public function testGetHashedPassword()
    {
        $hashedPass = $this->api->getHashedPassword('12345678'); // default = 8 = sha256
        $this->assertEquals(72, strlen($hashedPass));
        $this->assertRegExp(self::ALLOWED_CHARS_REGEXP, $hashedPass);
        $this->assertEquals(2, substr_count($hashedPass, '$'));

        $hashedPass = $this->api->getHashedPassword('H4ppy81rthd$y', 1); // 1 = md5
        $this->assertEquals(40, strlen($hashedPass));
        $this->assertRegExp(self::ALLOWED_CHARS_REGEXP, $hashedPass);
        $this->assertEquals(2, substr_count($hashedPass, '$'));

        $hashedPass = $this->api->getHashedPassword('mybirthdayplusabunchofchanracters%&*&^53', 5); // 5 = sha1
        $this->assertEquals(48, strlen($hashedPass));
        $this->assertRegExp(self::ALLOWED_CHARS_REGEXP, $hashedPass);
        $this->assertEquals(2, substr_count($hashedPass, '$'));
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException \InvalidArgumentException
     */
    public function testGetHashedPasswordOnEmpty()
    {
        $hashedPass = $this->api->getHashedPassword('12345678', '');
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException \InvalidArgumentException
     */
    public function testGetHashedPasswordOnNull()
    {
        $hashedPass = $this->api->getHashedPassword('12345678', null);
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException \InvalidArgumentException
     */
    public function testGetHashedPasswordOnString()
    {
        $hashedPass = $this->api->getHashedPassword('12345678', 'a');
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException \Symfony\Component\Debug\Exception\ContextErrorException
     */
    public function testGetHashedPasswordOnUndefined()
    {
        $hashedPass = $this->api->getHashedPassword('12345678', 2); // 2 is not a defined algorithm
    }

    /**
     * @covers PasswordApi::generatePassword()
     */
    public function testGeneratePassword()
    {
        $password = $this->api->generatePassword();
        $this->assertEquals(5, strlen($password));
        $this->assertNotRegExp('/[0oOl1iIj!|]/', $password);
    }

    /**
     * @covers PasswordApi::passwordsMatch()
     */
    public function testPasswordsMatch()
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->assertTrue($this->api->passwordsMatch('12345678', $hashedPass));
    }

    /**
     * @covers PasswordApi::passwordsMatch()
     * @expectedException \InvalidArgumentException
     */
    public function testPasswordsMatchExceptionOnEmpty()
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch('', $hashedPass);
    }

    /**
     * @covers PasswordApi::passwordsMatch()
     * @expectedException \InvalidArgumentException
     */
    public function testPasswordsMatchExceptionOnNull()
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch(null, $hashedPass);
    }
}
