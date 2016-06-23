<?php

namespace Zikula\ZAuthModule;

use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractExtensionInstaller;

class ZAuthModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        'Zikula\ZAuthModule\Entity\AuthenticationMappingEntity'
    ];

    public function setBundle(AbstractBundle $bundle)
    {
        $this->bundle = $bundle;
    }

    public function install()
    {
        $this->schemaTool->create($this->entities);

        return true;
    }

    public function upgrade($oldversion)
    {
        return true;
    }

    public function uninstall()
    {
        $this->schemaTool->drop($this->entities);

        return true;
    }
}
