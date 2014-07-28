<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Symfony\Component\Yaml\Yaml;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
  
class Bootstrap
{
    /**
     * @var array the active/inactive state of each extension
     */
    private $extensionStateMap = array();

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
     * determine if the extension is active
     *
     * @param \Doctrine\DBAL\Connection $conn
     * @param string $class
     * @param string $type
     *
     * @return bool
     */
    private function extensionIsActive(ZikulaKernel $kernel, $conn, $class, $type) {
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
                $this->extensionStateMap[$row['name']] = array(
                    'state' =>(int)$row['state'],
                    'id'    => (int)$row['id'],
                );
            }
            $sql = "SELECT t.name, t.state, t.id FROM themes as t";
            $rows = $conn->executeQuery($sql);
            foreach ($rows as $row) {
                $this->extensionStateMap[$row['name']] = array(
                    'state' =>(int)$row['state'],
                    'id'    => (int)$row['id'],
                );
            }
            if (isset($this->extensionStateMap[$extensionName])) {
                $state = $this->extensionStateMap[$extensionName];
            } else {
                $state =  array('state' => ($type == 'T') ?  \ThemeUtil::STATE_INACTIVE : \ModUtil::STATE_UNINITIALISED);
            }
        }
        switch($type) {
            case 'T':
                return ($state['state'] == \ThemeUtil::STATE_ACTIVE);
                break;
            default:
                if ($state['state'] == \ModUtil::STATE_ACTIVE) {
                    return true;
                } else if ($state['state'] == \ModUtil::STATE_UPGRADED && isset($_GET['id']) &&$state['id'] == $_GET['id'] && isset($_GET['secret'])) {
                    $secret = $_GET['secret'];
                    $rootDir = $kernel->getRootDir() . "/config";
                    $path = $rootDir . "/custom_parameters.yml";
                    if (!is_readable($path)) {
                        $path = $rootDir . "/parameters.yml";
                    }
                    $parameters = Yaml::parse(file_get_contents($path));
                    $urlSecret = $parameters['parameters']['url_secret'];

                    // Only set module to active if the secret matches.
                    return ($secret === $urlSecret);
                } else {
                    return false;
                }
        }
    }

    /**
     * Add autoloaders to kernel or include files from json
     *
     * @param ZikulaKernel $kernel
     * @param array $autoload
     */
    public function addAutoloaders(ZikulaKernel $kernel, array $autoload)
    {
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
    }
}
