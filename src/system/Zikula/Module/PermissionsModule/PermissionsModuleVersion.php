<?php

namespace Zikula\Module\PermissionsModule;

class PermissionsModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Permissions');
        $meta['description'] = $this->__('User permissions manager.');
        //! module name that appears in URL
        $meta['url'] = $this->__('permissions');
        $meta['version'] = '1.1.1';
        $meta['core_min'] = '1.3.6';
        $meta['securityschema'] = array('ZikulaPermissionsModule::' => '::');
        return $meta;
    }

}