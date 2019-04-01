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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Api\PasswordApi;

class PasswordApiTest extends TestCase
{
    private const ALLOWED_CHARS_REGEXP = ';[0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~@#$%^*()_+-={}|\][];';

    /**
     * @var PasswordApiInterface
     */
    private $api;

    protected function setUp(): void
    {
        $this->api = new PasswordApi();
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     */
    public function testGetHashedPassword(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678'); // default = 8 = sha256
        $this->assertEquals(72, mb_strlen($hashedPass));
        $this->assertRegExp(self::ALLOWED_CHARS_REGEXP, $hashedPass);
        $this->assertEquals(2, mb_substr_count($hashedPass, '$'));

        $hashedPass = $this->api->getHashedPassword('H4ppy81rthd$y', 1); // 1 = md5
        $this->assertEquals(40, mb_strlen($hashedPass));
        $this->assertRegExp(self::ALLOWED_CHARS_REGEXP, $hashedPass);
        $this->assertEquals(2, mb_substr_count($hashedPass, '$'));

        $hashedPass = $this->api->getHashedPassword('mybirthdayplusabunchofchanracters%&*&^53', 5); // 5 = sha1
        $this->assertEquals(48, mb_strlen($hashedPass));
        $this->assertRegExp(self::ALLOWED_CHARS_REGEXP, $hashedPass);
        $this->assertEquals(2, mb_substr_count($hashedPass, '$'));
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException InvalidArgumentException
     */
    public function testGetHashedPasswordOnEmpty(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678', '');
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException InvalidArgumentException
     */
    public function testGetHashedPasswordOnNull(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678', null);
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException InvalidArgumentException
     */
    public function testGetHashedPasswordOnString(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678', 'a');
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     * @expectedException InvalidArgumentException
     */
    public function testGetHashedPasswordOnUndefined(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678', 2); // 2 is not a defined algorithm
    }

    /**
     * @covers PasswordApi::generatePassword()
     */
    public function testGeneratePassword(): void
    {
        $password = $this->api->generatePassword();
        $this->assertEquals(5, mb_strlen($password));
        $this->assertNotRegExp('/[0oOl1iIj!|]/', $password);
    }

    /**
     * @covers PasswordApi::passwordsMatch()
     */
    public function testPasswordsMatch(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->assertTrue($this->api->passwordsMatch('12345678', $hashedPass));
    }

    /**
     * @covers PasswordApi::passwordsMatch()
     * @expectedException InvalidArgumentException
     */
    public function testPasswordsMatchExceptionOnEmpty(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch('', $hashedPass);
    }

    /**
     * @covers PasswordApi::passwordsMatch()
     * @expectedException InvalidArgumentException
     */
    public function testPasswordsMatchExceptionOnNull(): void
    {
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch(null, $hashedPass);
    }
}
