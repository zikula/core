<?php

namespace Zikula\ThemesBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ZikulaThemesExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        
        
        $dir = new \DirectoryIterator($container->getParameter('kernel.root_dir') . '/../themes');
        
        $modules = array();
        foreach($dir as $fileInfo) {
            /* @var $fileInfo \SplFileInfo */
            
            if($fileInfo->isDir() && !$fileInfo->isDot()) {
                $themeName = $fileInfo->getFilename();
                
                $container->setDefinition(
                    'assetic.theme_directory_resource.' . $themeName,
                    new \Symfony\Bundle\AsseticBundle\DependencyInjection\DirectoryResourceDefinition('Themes'.$themeName, 'twig', array(
                        $container->getParameter('kernel.root_dir').'/../themes/'.$themeName.'/Resources/views'
                    ))
                );
            }
        }
    }
}
