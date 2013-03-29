<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;

/**
 * Generates a module.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ModuleGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;

    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    public function generate($namespace, $module, $dir, $format, $license = 'MIT')
    {
        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the module as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the module as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the module as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $basename = substr($module, 0, -6);
        $namespaceParts = explode('\\', $namespace);
        $vendorName = $namespaceParts[0];
        $moduleName = substr($module, strlen($vendorName), strlen($module));
        $parameters = array(
            'vendor'           => $vendorName,
            'namespace'        => $namespace,
            'module'           => $module,
            'module_name'      => $moduleName,
            'namespace_double' => str_replace('\\', '\\\\', $namespace),
            'module_double'    => str_replace('\\', '\\\\', $module),
            'format'           => $format,
            'module_basename'  => $basename,
            'extension_alias'  => Container::underscore($basename),
        );

        $this->filesystem->copy($this->skeletonDir.'/.gitignore', $dir.'/.gitignore');
        $this->filesystem->copy($this->skeletonDir.'/.travis.yml', $dir.'/.travis.yml');
        $this->renderFile($this->skeletonDir, 'composer.json.twig', $dir.'/composer.json', $parameters);
        $this->renderFile($this->skeletonDir, 'Module.php.twig', $dir.'/'.$module.'.php', $parameters);
        $this->renderFile($this->skeletonDir, 'Version.php.twig', $dir.'/'.$basename.'Version.php', $parameters);
        $this->renderFile($this->skeletonDir, 'Installer.php.twig', $dir.'/'.$basename.'Installer.php', $parameters);
        $this->renderFile($this->skeletonDir, 'phpunit.xml.dist.twig', $dir.'/phpunit.xml.dist', $parameters);
        $this->renderFile($this->skeletonDir, 'README.md.twig', $dir.'/README.md', $parameters);
        $this->renderFile($this->skeletonDir, 'LICENSE-'.$license.'.twig', $dir.'/LICENSE.md', $parameters);
        $this->filesystem->copy($this->skeletonDir.'/gettext.pot', $dir.'/Resources/locale/'.strtolower($module).'.pot');
//        $this->renderFile($this->skeletonDir, 'Extension.php.twig', $dir.'/DependencyInjection/'.$basename.'Extension.php', $parameters);
//        $this->renderFile($this->skeletonDir, 'Configuration.php.twig', $dir.'/DependencyInjection/Configuration.php', $parameters);
        $this->renderFile($this->skeletonDir, 'DefaultController.php.twig', $dir.'/Controller/DefaultController.php', $parameters);
        $this->renderFile($this->skeletonDir, 'DefaultControllerTest.php.twig', $dir.'/Tests/Controller/DefaultControllerTest.php', $parameters);
        $this->renderFile($this->skeletonDir, 'index.html.twig.twig', $dir.'/Resources/views/Default/index.html.twig', $parameters);
        $this->filesystem->mkdir($dir.'/Resources/doc');
        $this->filesystem->touch($dir.'/Resources/doc/index.rst');
        $this->filesystem->mkdir($dir.'/Resources/public/css');
        $this->filesystem->mkdir($dir.'/Resources/public/images');
        $this->filesystem->mkdir($dir.'/Resources/public/js');


//        if ('xml' === $format || 'annotation' === $format) {
//            $this->renderFile($this->skeletonDir, 'services.xml.twig', $dir.'/Resources/config/services.xml', $parameters);
//        } else {
//            $this->renderFile($this->skeletonDir, 'services.'.$format.'.twig', $dir.'/Resources/config/services.'.$format, $parameters);
//        }
//
//        if ('annotation' != $format) {
//            $this->renderFile($this->skeletonDir, 'routing.'.$format.'.twig', $dir.'/Resources/config/routing.'.$format, $parameters);
//        }
    }
}
