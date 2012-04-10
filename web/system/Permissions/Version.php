<?php

class Permissions_Version extends Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Permissions');
        $meta['description']    = $this->__("Manage users's permissions.");
        $meta['url']            = $this->__('permissions');
        $meta['version']        = '1.1.1';
        $meta['securityschema'] = array('Permissions::' => '::');
        return $meta;
    }
}
