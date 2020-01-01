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
     */
    public function testGetHashedPasswordOnEmpty(): void
    {
        $this->expectException(\TypeError::class);
        $hashedPass = $this->api->getHashedPassword('12345678', '');
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     */
    public function testGetHashedPasswordOnNull(): void
    {
        $this->expectException(\TypeError::class);
        $hashedPass = $this->api->getHashedPassword('12345678', null);
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     */
    public function testGetHashedPasswordOnString(): void
    {
        $this->expectException(\TypeError::class);
        $hashedPass = $this->api->getHashedPassword('12345678', 'a');
    }

    /**
     * @covers PasswordApi::getHashedPassword()
     */
    public function testGetHashedPasswordOnUndefined(): void
    {
        $this->expectException(\ErrorException::class);
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
     */
    public function testPasswordsMatchExceptionOnEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch('', $hashedPass);
    }

    /**
     * @covers PasswordApi::passwordsMatch()
     */
    public function testPasswordsMatchExceptionOnNull(): void
    {
        $this->expectException(\TypeError::class);
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->api->passwordsMatch(null, $hashedPass);
    }
}
