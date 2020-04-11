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

namespace Zikula\Bundle\CoreBundle\Helper;

use function Composer\Autoload\includeFile;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Exception;
use PDO;
use Zikula\Bundle\CoreBundle\Exception\StaleCacheException;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Constant;

class PersistedBundleHelper
{
    /**
     * @var array the active/inactive state of each extension (extension state !== bundle state)
     */
    private $extensionStateMap = [];

    public function getPersistedBundles(ZikulaHttpKernelInterface $kernel, array &$bundles): void
    {
        try {
            $this->doGetPersistedBundles($kernel, $bundles);
        } catch (StaleCacheException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            // fail silently on purpose
        }
    }

    private function doGetPersistedBundles(ZikulaHttpKernelInterface $kernel, array &$bundles): void
    {
        $conn = $this->getConnection();
        $conn->connect();
        $res = $conn->executeQuery('SELECT bundleclass, autoload, bundletype FROM bundles');
        $unavailableExtensions = 0;
        foreach ($res->fetchAll(PDO::FETCH_NUM) as [$class, $autoload, $type]) {
            $extensionIsActive = $this->extensionIsActive($conn, $class, $type);
            if (!$extensionIsActive) {
                continue;
            }
            try {
                $autoload = unserialize($autoload);
                $this->addAutoloaders($kernel, $autoload);

                if (class_exists($class)) {
                    $bundles[$class] = ['all' => true];
                } else {
                    $unavailableExtensions += $this->markExtensionUnavailable($conn, $class);
                }
            } catch (Exception $exception) {
                // unable to autoload $prefix / $path
            }
        }
        $conn->close();
        if ($unavailableExtensions > 0) {
            // clear the cache & start over
            throw new StaleCacheException('An extension has been removed without uninstalling.');
        }
    }

    private function getConnection(): Connection
    {
        $connectionParams = [
            'url' => $_ENV['DATABASE_URL'] ?? ''
        ];

        return DriverManager::getConnection($connectionParams, new Configuration());
    }

    /**
     * Determine if an extension is active.
     */
    private function extensionIsActive(Connection $conn, string $class, string $type): ?bool
    {
        $extensionName = $this->extensionNameFromClass($class);
        if (isset($this->extensionStateMap[$extensionName])) {
            // used cached value
            $state = $this->extensionStateMap[$extensionName];
        } else {
            // load all values into class var for lookup
            $sql = 'SELECT m.name, m.state, m.id FROM extensions as m';
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionStateMap[$row['name']] = [
                    'state' => (int)$row['state'],
                    'id'    => (int)$row['id'],
                ];
            }

            $state = $this->extensionStateMap[$extensionName] ?? ['state' => ('T' === $type) ? Constant::STATE_INACTIVE : Constant::STATE_UNINITIALISED];
        }

        return in_array($state['state'], [Constant::STATE_ACTIVE, Constant::STATE_UPGRADED, Constant::STATE_TRANSITIONAL], true);
    }

    private function markExtensionUnavailable(Connection $conn, string $class): int
    {
        $extensionName = $this->extensionNameFromClass($class);
        $id = $this->extensionStateMap[$extensionName]['id'];

        return $conn->executeUpdate('UPDATE extensions set state = ? where id = ?', [Constant::STATE_MISSING, $id]);
    }

    private function extensionNameFromClass(string $class): string
    {
        $extensionNameArray = explode('\\', $class);
        return array_pop($extensionNameArray);
    }

    /**
     * Add autoloaders to kernel or include files from json.
     */
    public function addAutoloaders(ZikulaHttpKernelInterface $kernel, array $autoload = []): void
    {
        $srcDir = $kernel->getProjectDir() . '/src/';
        if (isset($autoload['psr-0'])) {
            foreach ($autoload['psr-0'] as $prefix => $path) {
                $kernel->getAutoloader()->add($prefix, $srcDir . $path);
            }
        }
        if (isset($autoload['psr-4'])) {
            foreach ($autoload['psr-4'] as $prefix => $path) {
                $kernel->getAutoloader()->addPsr4($prefix, $srcDir . $path);
            }
        }
        if (isset($autoload['classmap'])) {
            $kernel->getAutoloader()->addClassMap($autoload['classmap']);
        }
        if (isset($autoload['files'])) {
            foreach ($autoload['files'] as $path) {
                includeFile($path);
            }
        }
    }
}
