<?php

namespace Zikula\Theme\Andreas08Theme;

class Andreas08ThemeVersion extends \Zikula_AbstractThemeVersion
{
    public function getMetaData()
    {
        $meta = array(
            'displayname' => $this->__('Andreas08'),
            'description' => $this->__("Based on the theme Andreas08 by Andreas Viklund and extended for Zikula with the CSS Framework 'fluid960gs'."),
            'version'     => '2.0.0',
            'admin'       => 1,
            'user'        => 1,
            'system'      => 0,
        );

        return $meta;
    }
}

