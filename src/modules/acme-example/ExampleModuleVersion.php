<?php

namespace Acme\ExampleModule;

class ExampleModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']    = $this->__('Acme ExampleModule');
        $meta['description']    = $this->__('ExampleModule description');
        $meta['url']            = $this->__('acmeexample');
        $meta['version']        = '0.0.1';
        $meta['core_min']       = '1.4.0';
        $meta['securityschema'] = array('AcmeExampleModule::' => '::');

        return $meta;
    }
}