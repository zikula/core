<?php

namespace Acme\ExampleModule;

class ExampleModuleInstaller extends \Zikula_AbstractInstaller
{
    public function install()
    {
        return true;
    }

    public function upgrade($oldversion)
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}