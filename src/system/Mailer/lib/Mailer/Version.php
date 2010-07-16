<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Mailer_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Mailer');
        $meta['description']    = $this->__("Provides mail-sending functionality for communication with the site's users, and an interface for managing the e-mail service settings used by the mailer.");
        //! module name that appears in URL
        $meta['url']            = $this->__('mailer');
        $meta['version']        = '1.3.1';

        $meta['securityschema'] = array('Mailer::' => '::');
        return $meta;
    }
}