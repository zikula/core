<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule;

/**
 * Version information for the security centre module
 */
class SecurityCenterModuleVersion extends \Zikula_AbstractVersion
{
    /**
     * Generate an array of meta data about this module
     *
     * @return array meta data array
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Security Center');
        $meta['description']    = $this->__('Manage site security and settings.');
        //! module name that appears in URL
        $meta['url']            = $this->__('securitycenter');
        $meta['version']        = '1.4.4';
        $meta['core_min'] = '1.4.0';
        $meta['securityschema'] = array('ZikulaSecurityCenterModule::' => '::');

        return $meta;
    }
}
