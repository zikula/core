<?php

namespace Zikula\Theme\RssTheme;

class RssThemeVersion extends \Zikula_AbstractThemeVersion
{
    public function getMetaData()
    {
        $meta = array(
            'displayname' => $this->__('RSS'),
            'description' => $this->__('The RSS theme is an auxiliary theme designed specially for outputting pages as an RSS feed.'),
            //! URL for display in the request.
            'admin'       => 0,
            'user'        => 0,
            'system'      => 1,
        );

        return $meta;
    }
}

/* themevariables.ini gettext strings*/
no__('Show item descriptions');
no__('RSS v0.91,RSS v0.20');
no__('RSS Version');
