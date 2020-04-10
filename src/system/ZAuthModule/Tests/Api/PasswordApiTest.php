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

namespace Zikula\ZAuthModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Api\PasswordApi;

class PasswordApiTest extends TestCase
{
    /**
     * @var PasswordApiInterface
     */
    private $api;

    protected function setUp(): void
    {
        $this->api = new PasswordApi();
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
        $hashedPass = $this->api->getHashedPassword('12345678');
        $this->assertFalse($this->api->passwordsMatch('', $hashedPass));
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
