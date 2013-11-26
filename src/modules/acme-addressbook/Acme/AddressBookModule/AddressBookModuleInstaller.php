<?php

namespace Acme\AddressBookModule;

class AddressBookModuleInstaller extends \Zikula_AbstractInstaller
{
    public function install()
    {
        \DoctrineHelper::createSchema($this->entityManager,
            array(
                 'Acme\AddressBookModule\Entity\Email',
                 'Acme\AddressBookModule\Entity\PhoneNumber',
                 'Acme\AddressBookModule\Entity\ContactGroup',
                 'Acme\AddressBookModule\Entity\Contact',
            )
        );
        return true;
    }

    public function upgrade($oldversion)
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}