<?php

namespace Zikula\Theme\AtomTheme;

class AtomThemeVersion extends \Zikula_AbstractThemeVersion
{
    public function getMetaData()
    {
        $meta = array(
            'displayname' => $this->__('Atom'),
            'description' => $this->__("The Atom theme is an auxiliary theme specially designed for rendering pages in Atom mark-up."),
            'version'     => '1.0.0',
            'admin'       => 0,
            'user'        => 0,
            'system'      => 1,
        );

        return $meta;
    }
}

/* themevariables.ini gettext strings*/
no__('Show item descriptions');
