<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Zikula\Core\AbstractBundle;
  
class Bootstrap
{
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
        $res = $conn->executeQuery('SELECT bundlename, bundleclass, autoload, bundlestate FROM bundles');
        foreach ($res->fetchAll(\PDO::FETCH_NUM) as $row) {
            list($name, $class, $autoload, $state) = $row;
            $autoload = unserialize($autoload);
            if (isset($autoload['psr-0'])) {
                foreach($autoload['psr-0'] as $prefix => $path) {
                    $kernel->getAutoloader()->add($prefix, $path);
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

            // todo - should we catch class not loadable here or not? If so how to handle it?
            // see https://github.com/zikula/core/issues/1424
        }
        $conn->close();
    }
}
