<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace ExampleModule;

/**
 * Version.
 */
class Version extends \Zikula\Framework\AbstractVersion
{
    /**
     * Module meta data.
     *
     * @return array Module metadata.
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('ExampleModule');
        $meta['description']    = $this->__("ExampleModule module.");
        //! module name that appears in URL
        $meta['url']            = $this->__('example');
        $meta['version']        = '1.0.0';
        $meta['securityschema'] = array('ExampleModule::' => '::',
                                        'ExampleModule:User:' => 'UserName::');
        return $meta;
    }
}
