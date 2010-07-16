<?php

class Groups_Version extends Zikula_Version
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Groups manager');
        $meta['description']    = $this->__('Provides support for user groups, and incorporates an interface for adding, removing and administering them.');
        //! module name that appears in URL
        $meta['url']            = $this->__('groups');
        $meta['version']        = '2.3.1';
        $meta['securityschema'] = array('Groups::' => 'Group ID::');
        return $meta;
    }
}