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

namespace Zikula\Bundle\CoreBundle\Bundle;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Exception;
use InvalidArgumentException;
use PDO;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\AbstractBundle;
use Zikula\ExtensionsModule\Constant;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;
use function Composer\Autoload\includeFile;

class Bootstrap
{
    /**
     * @var array the active/inactive state of each extension
     */
    private $extensionStateMap = [];

    public function getConnection(ZikulaHttpKernelInterface $kernel): Connection
    {
        // get bundles from persistence
        $connectionParams = $kernel->getConnectionConfig();
        $connectionParams['dbname'] = $connectionParams['parameters']['database_name'];
        $connectionParams['user'] = $connectionParams['parameters']['database_user'];
        $connectionParams['password'] = $connectionParams['parameters']['database_password'];
        $connectionParams['host'] = $connectionParams['parameters']['database_host'];
        $connectionParams['driver'] = $connectionParams['parameters']['database_driver'];

        return DriverManager::getConnection($connectionParams, new Configuration());
    }

    public function getPersistedBundles(ZikulaHttpKernelInterface $kernel, array &$bundles): void
    {
        try {
            $this->doGetPersistedBundles($kernel, $bundles);
        } catch (Exception $exception) {
            // fail silently on purpose
        }
    }

    private function doGetPersistedBundles(ZikulaHttpKernelInterface $kernel, array &$bundles): void
    {
        $conn = $this->getConnection($kernel);
        $conn->connect();
        $res = $conn->executeQuery('SELECT bundleclass, autoload, bundlestate, bundletype FROM bundles');
        foreach ($res->fetchAll(PDO::FETCH_NUM) as list($class, $autoload, $state, $type)) {
            $extensionIsActive = $this->extensionIsActive($conn, $class, $type);
            if ($extensionIsActive) {
                try {
                    $autoload = unserialize($autoload);
                    $this->addAutoloaders($kernel, $autoload);

                    if (class_exists($class)) {
                        $bundle = new $class();
                        try {
                            if ($bundle instanceof AbstractBundle) {
                                $bundle->setState((int)$state);
                            }
                            $bundles[] = $bundle;
                        } catch (InvalidArgumentException $exception) {
                            // continue
                        }
                    }
                } catch (Exception $exception) {
                    // unable to autoload $prefix / $path
                }
            }
        }
        $conn->close();
    }

    /**
     * Determine if an extension is active.
     */
    private function extensionIsActive(Connection $conn, string $class, string $type): ?bool
    {
        $extensionNameArray = explode('\\', $class);
        $extensionName = array_pop($extensionNameArray);
        if (isset($this->extensionStateMap[$extensionName])) {
            // used cached value
            $state = $this->extensionStateMap[$extensionName];
        } else {
            // load all values into class var for lookup
            $sql = 'SELECT m.name, m.state, m.id FROM modules as m';
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionStateMap[$row['name']] = [
                    'state' => (int)$row['state'],
                    'id'    => (int)$row['id'],
                ];
            }
            $sql = 'SELECT t.name, t.state, t.id FROM themes as t';
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionStateMap[$row['name']] = [
                    'state' => (int)$row['state'],
                    'id'    => (int)$row['id'],
                ];
            }

            $state = $this->extensionStateMap[$extensionName] ?? ['state' => ('T' === $type) ? ThemeEntityRepository::STATE_INACTIVE : Constant::STATE_UNINITIALISED];
        }

        if ('T' === $type) {
            return ThemeEntityRepository::STATE_ACTIVE === $state['state'];
        }

        return in_array($state['state'], [Constant::STATE_ACTIVE, Constant::STATE_UPGRADED, Constant::STATE_TRANSITIONAL], true);
    }

    /**
     * Add autoloaders to kernel or include files from json.
     */
    public function addAutoloaders(ZikulaHttpKernelInterface $kernel, array $autoload = []): void
    {
        if (isset($autoload['psr-0'])) {
            foreach ($autoload['psr-0'] as $prefix => $path) {
                $kernel->getAutoloader()->add($prefix, $path);
            }
        }
        if (isset($autoload['psr-4'])) {
            foreach ($autoload['psr-4'] as $prefix => $path) {
                $kernel->getAutoloader()->addPsr4($prefix, $path);
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
