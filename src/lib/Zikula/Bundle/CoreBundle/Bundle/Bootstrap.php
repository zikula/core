<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Bundle;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\AbstractBundle;
use Zikula\ExtensionsModule\Constant;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

class Bootstrap
{
    /**
     * @var array the active/inactive state of each extension
     */
    private $extensionStateMap = [];

    public function getConnection($kernel)
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

    public function getPersistedBundles(ZikulaKernel $kernel, array &$bundles)
    {
        try {
            $this->doGetPersistedBundles($kernel, $bundles);
        } catch (\Exception $e) {
            // fail silently on purpose (drak)
        }
    }

    private function doGetPersistedBundles(ZikulaKernel $kernel, array &$bundles)
    {
        $conn = $this->getConnection($kernel);
        $conn->connect();
        $res = $conn->executeQuery('SELECT bundlename, bundleclass, autoload, bundlestate, bundletype FROM bundles');
        foreach ($res->fetchAll(\PDO::FETCH_NUM) as $row) {
            list($name, $class, $autoload, $state, $type) = $row;
            $extensionIsActive = $this->extensionIsActive($kernel, $conn, $class, $type);
            if ($extensionIsActive) {
                try {
                    $autoload = unserialize($autoload);
                    $this->addAutoloaders($kernel, $autoload);

                    if (class_exists($class)) {
                        $bundle = new $class();
                        try {
                            if ($bundle instanceof AbstractBundle) {
                                $bundle->setState($state);
                            }
                            $bundles[] = $bundle;
                        } catch (\InvalidArgumentException $e) {
                            // continue
                        }
                    }
                } catch (\Exception $e) {
                    // unable to autoload $prefix / $path
                    // todo - should we catch class not loadable here or not? If so how to handle it?
                    // see https://github.com/zikula/core/issues/1424
                }
            }
        }
        $conn->close();
    }

    /**
     * determine if the extension is active
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param string $class
     * @param string $type
     *
     * @return bool
     */
    private function extensionIsActive(ZikulaKernel $kernel, $conn, $class, $type)
    {
        $extensionNameArray = explode('\\', $class);
        $extensionName = array_pop($extensionNameArray);
        if (isset($this->extensionStateMap[$extensionName])) {
            // used cached value
            $state = $this->extensionStateMap[$extensionName];
        } else {
            // load all values into class var for lookup
            $sql = "SELECT m.name, m.state, m.id FROM modules as m";
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionStateMap[$row['name']] = [
                    'state' => (int)$row['state'],
                    'id'    => (int)$row['id'],
                ];
            }
            $sql = "SELECT t.name, t.state, t.id FROM themes as t";
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionStateMap[$row['name']] = [
                    'state' => (int)$row['state'],
                    'id'    => (int)$row['id'],
                ];
            }

            if (isset($this->extensionStateMap[$extensionName])) {
                $state = $this->extensionStateMap[$extensionName];
            } else {
                $state = ['state' => ('T' == $type) ? ThemeEntityRepository::STATE_INACTIVE : Constant::STATE_UNINITIALISED];
            }
        }

        switch ($type) {
            case 'T':
                return ThemeEntityRepository::STATE_ACTIVE == $state['state'];
                break;
            default:
                if ((Constant::STATE_ACTIVE == $state['state']) || (Constant::STATE_UPGRADED == $state['state']) || (Constant::STATE_TRANSITIONAL == $state['state'])) {
                    return true;
                }

                return false;
        }
    }

    /**
     * Add autoloaders to kernel or include files from json
     *
     * @param ZikulaKernel $kernel
     * @param array        $autoload
     */
    public function addAutoloaders(ZikulaKernel $kernel, array $autoload)
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
                include $path;
            }
        }
    }
}
