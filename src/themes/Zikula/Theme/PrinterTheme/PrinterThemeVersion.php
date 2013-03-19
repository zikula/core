<?php

namespace Zikula\Theme\PrinterTheme;

class PrinterThemeVersion extends \Zikula_AbstractThemeVersion
{
    public function getMetaData()
    {
        $meta = array(
            'displayname' => $this->__('Printer'),
            'description' => $this->__('The Printer theme is an auxiliary theme designed specially for outputting pages in a printer-friendly format.'),
            'version'     => '2.0.0',
            'admin'       => 0,
            'user'        => 0,
            'system'      => 1,
        );
    }
}
