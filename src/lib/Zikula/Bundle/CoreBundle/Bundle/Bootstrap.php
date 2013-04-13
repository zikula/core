<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
  
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
        $res = $conn->executeQuery('SELECT name, class, autoload FROM bundles');
        foreach ($res->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $autoload = unserialize($row['autoload']);
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
            $class = $row['class'];

            if (class_exists($class)) {
                $bundles[] = new $class;
            } else {
                throw new \RuntimeException(sprintf('Looks like the bundle %s files are missing', $row['name']));
            }
        }
        $conn->close();
    }
}
