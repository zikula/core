<?php

namespace Acme\AddressBookModule;

class AddressBookModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Acme AddressBookModule');
        $meta['description']    = $this->__('AddressBookModule description');
        $meta['url']            = $this->__('acmeaddressbook');
        $meta['version']        = '0.0.1';
        $meta['core_min']       = '1.3.7';
        $meta['securityschema'] = array('AcmeAddressBookModule::' => '::');

        return $meta;
    }
}