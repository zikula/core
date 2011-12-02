<?php

namespace Zikula\Tests\Common\FileSystem\Configuration;

use Zikula\Common\FileSystem\Configuration\SftpConfiguration;
use Zikula\Common\FileSystem\Configuration\ConfigurationInterface;

/**
 * Zikula_FileSystem_Configuration_Sftp test case.
 */
class SftpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_FileSystem_Configuration_Sftp
     */
    private $sftp;
    private $sftp2;
    private $sftp3;
    private $sftp4;
    private $sftp5;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->sftp = new SftpConfiguration('host', 'user', 'pass', '/', 22);
        $this->sftp2 = new SftpConfiguration();
        $this->sftp3 = new SftpConfiguration('host', 'user', 'pass', 'dir', 'port');
        $this->sftp4 = new SftpConfiguration('host', 'user', 'pass', '/test', 'port');
        $this->sftp5 = new SftpConfiguration('host', 'user', '', '', 22, 'ssh-rsa', 'pubkey', 'privkey', 'passphrase');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->sftp = null;
        $this->sftp2 = null;
        $this->sftp3 = null;
        $this->sftp4 = null;
        $this->sftp5 = null;
        parent::tearDown();
    }

    public function test__construct()
    {
        $this->assertInstanceOf('Zikula\Common\FileSystem\Configuration\ConfigurationInterface', $this->sftp);
    }

    public function testGetUser()
    {
        $this->assertEquals('user', $this->sftp->getUser());
        $this->assertEquals('Anonymous', $this->sftp2->getUser());
    }

    public function testGetPass()
    {
        $this->assertEquals('pass', $this->sftp->getPass());
        $this->assertEquals('', $this->sftp2->getPass());
    }

    public function testGetHost()
    {
        $this->assertEquals('host', $this->sftp->getHost());
        $this->assertEquals('localhost', $this->sftp2->getHost());
    }

    public function testGetPort()
    {
        $this->assertEquals('22', $this->sftp->getPort());
        $this->assertEquals('22', $this->sftp2->getPort());
        $this->assertEquals('22', $this->sftp3->getPort());
    }

    public function testGetDir()
    {
        $this->assertEquals('/', $this->sftp->getDir());
        $this->assertEquals('./', $this->sftp2->getDir());
        $this->assertEquals('./dir', $this->sftp3->getDir());
        $this->assertEquals('/test', $this->sftp4->getDir());
    }

    public function testGetPubkey()
    {
        $this->assertEquals('', $this->sftp->getPubKey());
        $this->assertEquals('pubkey', $this->sftp5->getPubKey());
    }

    public function testGetPrivKey()
    {
        $this->assertEquals('', $this->sftp->getPrivKey());
        $this->assertEquals('privkey', $this->sftp5->getPrivKey());
    }

    public function testGetPassphrase()
    {
        $this->assertEquals('', $this->sftp->getPassphrase());
        $this->assertEquals('passphrase', $this->sftp5->getPassphrase());
    }

    public function testGetAuthType()
    {
        $this->assertEquals('pass', $this->sftp->getAuthType());
        $this->assertEquals('ssh-rsa', $this->sftp5->getAuthType());
    }

}