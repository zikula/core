<?php

namespace Zikula\Theme\SeaBreezeTheme;

class SeaBreezeThemeVersion extends \Zikula_AbstractThemeVersion
{
    public function getMetaData()
    {
        $meta = array(
            'displayname' => $this->__('SeaBreeze'),
            'description' => $this->__('The SeaBreeze theme is a browser-oriented theme.'),
            'version'     => '3.2.0',
            'admin'       => 0,
            'user'        => 1,
            'system'      => 0,
        );

        return $meta;
    }
}
