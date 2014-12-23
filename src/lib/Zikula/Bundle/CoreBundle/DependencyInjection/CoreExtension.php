<?php

namespace Zikula\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CoreExtension extends Extension
{
    /**
     * Responds to the app.config configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('session.xml');
        $loader->load('services.xml');
        $loader->load('core.xml');
        $loader->load('twig.xml');

//        $config = Yaml::parse(file_get_contents(ZIKULA_ROOT.'/../app/config/core_legacy.yml'));
//        foreach ($config as $key => $array) {
//            foreach ($array as $id => $value) {
//                $container->setParameter($id, $value);
//            }
//        }

        // @todo temporary hack
//        $container->setParameter('_zconfig', $config);

//        $this->addClassesToCompile(array(
//            'Zikula\\Component\\DependencyInjection\\ContainerBuilder',
//        ));

        // todo - temporary - remove when Smarty is removed, also need to redeligate some
        // of this to other's responsibility
        $cacheDir = $container->getParameterBag()->resolveValue('%kernel.cache_dir%/ztemp');
        $dirs = array('doctrinemodels', 'idsTmp', 'purifierCache', 'doctrinemodels',
        'Theme_cache', 'Theme_compiled', 'Theme_Config', 'view_cache', 'view_compiled', 'error_logs');
        foreach ($dirs as $dir) {
            if (!is_dir($cacheDir.'/'.$dir)) {
                mkdir($cacheDir.'/'.$dir, 0777, true);
            }
        }
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/symfony';
    }
}
