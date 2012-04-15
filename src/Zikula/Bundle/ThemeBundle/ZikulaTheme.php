<?php

namespace Zikula\ThemeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class ZikulaTheme extends Bundle
{

    public function __construct()
    {
        $name = get_class($this);
        $posNamespaceSeperator = strrpos($name, '\\');
        $this->name = substr($name, $posNamespaceSeperator + 1);
    }

    public abstract function getVersion();

    public function getServiceIds()
    {
        return array();
    }
}
