<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package ZikulaExamples_ExampleDoctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Version.
 */
class ExampleDoctrine_Version extends Zikula_AbstractVersion
{
    /**
     * Module meta data.
     *
     * @return array Module metadata.
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('ExampleDoctrine');
        $meta['description']    = $this->__("ExampleDoctrine module.");
        //! module name that appears in URL
        $meta['url']            = $this->__('exampledoctrine');
        $meta['version']        = '1.0.0';
        $meta['securityschema'] = array('ExampleDoctrine::' => '::',
                                        'ExampleDoctrine:User:' => 'UserName::');
        return $meta;
    }
}
