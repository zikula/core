<?php

namespace Zikula\Theme\BootstrapTheme;

class BootstrapThemeVersion extends \Zikula_AbstractThemeVersion
{
    public function getMetaData()
    {
        $meta = array(
            'displayname' => $this->__('Bootstrap'),
            'description' => $this->__("Bootstrap testing version. Based on Andreas 08."),
            'version'     => '0.0.1',
            'admin'       => 1,
            'user'        => 1,
            'system'      => 0,
        );

        return $meta;
    }
}

