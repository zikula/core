<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Zikula\Core\AbstractBundle;
  
class Bootstrap
{
    /**
     * @var array the active/inactive state of each extension
     */
    private $extensionState = array();

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
            $extensionIsInactive = $this->extensionIsInactive($conn, $class, $type);
            if (!$extensionIsInactive) {
                try {
                    $autoload = unserialize($autoload);
                    if (isset($autoload['psr-0'])) {
                        foreach($autoload['psr-0'] as $prefix => $path) {
                            $kernel->getAutoloader()->add($prefix, $path);
                        }
                    }
                    if (isset($autoload['psr-4'])) {
                        foreach($autoload['psr-4'] as $prefix => $path) {
                            $kernel->getAutoloader()->addPsr4($prefix, $path);
                        }
                    }
                    if (isset($autoload['classmap'])) {
                        $kernel->getAutoloader()->addClassMap($autoload['classmaps']);
                    }
                    if (isset($autoload['files'])) {
                        foreach($autoload['files'] as $path) {
                            include $path;
                        }
                    }

                    if (class_exists($class)) {
                        $bundle = new $class;
                        try {
                            $bundle->setState($state);
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
     * determine if the extension is inactive
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param string $class
     * @param string $type
     *
     * @return bool
     */
    private function extensionIsInactive($conn, $class, $type) {
        $extensionNameArray = explode('\\', $class);
        $extensionName = array_pop($extensionNameArray);
        if (isset($this->extensionState[$extensionName])) {
            // used cached value
            $state = $this->extensionState[$extensionName];
        } else {
            // load all values into class var for lookup
            $sql = "SELECT m.name, m.state FROM modules as m";
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionState[$row['name']] = (int)$row['state'];
            }
            $sql = "SELECT t.name, t.state FROM themes as t";
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionState[$row['name']] = (int)$row['state'];
            }
            $state = $this->extensionState[$extensionName];
        }
        switch($type) {
            case 'T':
                // themes in an inactive state should not be autoloaded
                return ($state == \ThemeUtil::STATE_INACTIVE);
                break;
            default:
                // modules in any state other than ACTIVE or UNINITIALIZED are inactive and should not be autoloaded
                return (($state !== \ModUtil::STATE_ACTIVE) && ($state !== \ModUtil::STATE_UNINITIALISED));
        }
    }
}
