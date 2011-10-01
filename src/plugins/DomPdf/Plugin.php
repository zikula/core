<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Doctrine plugin definition.
 */
class SystemPlugin_DomPdf_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_AlwaysOnInterface
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array('displayname' => $this->__('dompdf'),
                     'description' => $this->__('Provides dompdf HTML to PDF converter'),
                     'version'     => '0.6.0'
                      );
    }

    /**
     * Initialise.
     *
     * Runs at plugin init time.
     *
     * @return void
     */
    public function initialize()
    {
        // include dom pdf classes
        $pdfConfigFile = 'plugins/DomPdf/lib/vendor/dompdf/dompdf_config.inc.php';
        if (!file_exists($pdfConfigFile)) {
            return false;
        }
        require_once $pdfConfigFile;
    }
}
