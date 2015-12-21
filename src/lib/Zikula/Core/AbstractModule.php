<?php

namespace Zikula\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zikula\Core\Theme\AssetBag;

abstract class AbstractModule extends AbstractBundle
{
    private $serviceIds = array();

    public function getNameType()
    {
        return 'Module';
    }

    public function addStylesheet($name = 'style.css')
    {
        $moduleStylesheet =  $this->getContainer()->get('zikula_core.common.theme.asset_helper')->resolve('@' . $this->getName() . ":css/$name");
        if (!empty($moduleStylesheet)) {
            $this->container->get('zikula_core.common.theme.assets_css')->add([$moduleStylesheet => AssetBag::WEIGHT_DEFAULT]);
        }
    }

//    /**
//     * @return ModuleInstallerInterface
//     */
//    abstract public function createInstaller();

    public function build(ContainerBuilder $container)
    {
        // modules have to use DI Extensions
    }

//    public function getContainerExtension()
//    {
//        $ex = parent::getContainerExtension();
//
//        if ($ex != null) {
//            $ex = new DependencyInjection\SandboxContainerExtension($ex, $this->serviceIds);
//        }
//
//        return $ex;
//    }

    public function getServiceIds()
    {
        return $this->serviceIds;
    }
}
