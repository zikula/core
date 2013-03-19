<?php

namespace Zikula\Theme\MobileTheme;

class MobileThemeVersion extends \Zikula_AbstractThemeVersion
{
    public function getMetaData()
    {
        $meta = array(
            'displayname' => $this->__('Mobile'),
            'description' => $this->__('The mobile theme is an auxiliary theme designed specially for outputting pages in a mobile-friendly format.'),
            'version'     => '1.0.0',
            'admin'       => 0,
            'user'        => 0,
            'system'      => 1,
        );

        return $meta;
    }
}