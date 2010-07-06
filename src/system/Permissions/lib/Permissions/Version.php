<?php

class Permissions_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Permission manager');
        $meta['description']    = $this->__("Provides an interface for fine-grained management of accessibility of the site's functionality and content through permission rules.");
        //! module name that appears in URL
        $meta['url']            = $this->__('permissions');
        $meta['version']        = '1.1';
        $meta['contact']        = 'http://zikula.org/';
        $meta['securityschema'] = array('Permissions::' => '::');
        return $meta;
    }
}