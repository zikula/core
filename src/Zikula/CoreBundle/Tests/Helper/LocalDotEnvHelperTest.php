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

namespace Zikula\CoreBundle\Tests\Helper;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Zikula\Bundle\CoreBundle\Helper\LocalDotEnvHelper;

class LocalDotEnvHelperTest extends TestCase
{
    private $projectDir;

    public function testWriteLocalEnvVars()
    {
        $this->projectDir = dirname(__DIR__, 5) . '/var/cache/test/dotenvtest';
        $fileSystem = new Filesystem();
        $fileSystem->copy(__DIR__ . '/Fixture/.env.local', $this->projectDir . '/.env.local', true);
        $originalFileContents = trim($this->getFileContents());
        $helper = new LocalDotEnvHelper($this->projectDir);

        $helper->writeLocalEnvVars([]);
        $this->assertEquals($originalFileContents, $this->getFileContents());

        $helper->writeLocalEnvVars(['MY_NEW_VAR' => 'foo']);
        $expected = $originalFileContents . "\nMY_NEW_VAR=foo";
        $this->assertEquals($expected, $this->getFileContents());

        $helper->writeLocalEnvVars(['MY_NEW_VAR' => 'f#oo']);
        $expected = $originalFileContents . "\nMY_NEW_VAR=f%23oo";
        $this->assertEquals($expected, $this->getFileContents());

        $helper->writeLocalEnvVars(['MY_NEW_VAR' => '\'bar\'']);
        $expected = $originalFileContents . "\nMY_NEW_VAR='bar'";
        $this->assertEquals($expected, $this->getFileContents());

        $helper->writeLocalEnvVars(['MY_NEW_VAR' => 'foo', 'BAR' => 123], true);
        $expected = "MY_NEW_VAR=foo\nBAR=123";
        $this->assertEquals($expected, $this->getFileContents());

        $helper->writeLocalEnvVars(['MY_NEW_VAR' => '!f@oo', 'BAR' => '!1(2)3'], true);
        $expected = "MY_NEW_VAR=f@oo\nBAR=1(2)3";
        $this->assertEquals($expected, $this->getFileContents());

        $helper->writeLocalEnvVars(['MY_NEW_VAR' => '!\'f@oo\''], true);
        $expected = 'MY_NEW_VAR=\'f@oo\'';
        $this->assertEquals($expected, $this->getFileContents());

        $helper->writeLocalEnvVars(['FOO' => 'foo', 'BAR' => 'bar', 'FEE' => 'fee', 'BEE' => 'bee'], true);
        $helper->writeLocalEnvVars(['BAR' => 'bar2', 'BOO' => 'boo']); // overwriting a value should place it at the end of the list
        $expected = "FOO=foo\nFEE=fee\nBEE=bee\nBAR=bar2\nBOO=boo";
        $this->assertEquals($expected, $this->getFileContents());

        $data = ['database_driver' => 'mysql', 'database_host' => 'localhost', 'database_name' => 'foo'];
        $vars = [
            'DATABASE_URL' => '!' . $data['database_driver']
                . '://$DATABASE_USER:$DATABASE_PWD'
                . '@' . $data['database_host'] . (!empty($data['database_port']) ? ':' . $data['database_port'] : '')
                . '/' . $data['database_name']
                . '?serverVersion=5.7' // any value will work (bypasses DBALException)
        ];
        $helper->writeLocalEnvVars($vars, true);
        $expected = 'DATABASE_URL=mysql://$DATABASE_USER:$DATABASE_PWD@localhost/foo?serverVersion=5.7';
        $this->assertEquals($expected, $this->getFileContents());
    }

    private function getFileContents(): string
    {
        return file_get_contents($this->projectDir . '/.env.local');
    }
}
