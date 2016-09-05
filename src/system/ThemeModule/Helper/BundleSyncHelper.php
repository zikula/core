<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;
use Zikula\ThemeModule\Entity\ThemeEntity;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper;

/**
 * Helper functions for the theme module
 */
class BundleSyncHelper
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ThemeEntityRepository
     */
    private $themeEntityRepository;

    /**
     * @var BootstrapHelper
     */
    private $bootstrapHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BundleSyncHelper constructor.
     * @param KernelInterface $kernel
     * @param ThemeEntityRepository $themeEntityRepository
     * @param BootstrapHelper $bootstrapHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(KernelInterface $kernel, ThemeEntityRepository $themeEntityRepository, BootstrapHelper $bootstrapHelper, TranslatorInterface $translator)
    {
        $this->kernel = $kernel;
        $this->themeEntityRepository = $themeEntityRepository;
        $this->bootstrapHelper = $bootstrapHelper;
        $this->translator = $translator;
    }

    /**
     * Regenerates the theme list
     * @return bool true
     * @throws \Exception
     */
    public function regenerate()
    {
        // sync the filesystem and the bundles table
        $this->bootstrapHelper->load();

        // Get all themes on filesystem
        $filethemes = [];

        $scanner = new Scanner();
        $scanner->scan(['themes'], 4);
        $newThemes = $scanner->getThemesMetaData();

        /** @var \Zikula\Bundle\CoreBundle\Bundle\MetaData $themeMetaData */
        foreach ($newThemes as $name => $themeMetaData) {
            // PSR-0 is @deprecated - remove in Core-2.0
            foreach ($themeMetaData->getPsr0() as $ns => $path) {
                \ZLoader::addPrefix($ns, $path);
            }
            foreach ($themeMetaData->getPsr4() as $ns => $path) {
                \ZLoader::addPrefixPsr4($ns, $path);
            }

            $bundleClass = $themeMetaData->getClass();

            /** @var $bundle \Zikula\ThemeModule\AbstractTheme */
            $bundle = new $bundleClass();
            $versionClass = $bundle->getVersionClass();

            if (class_exists($versionClass)) {
                // 1.4-module spec - @deprecated - remove in Core-2.0
                $version = new $versionClass($bundle);
                $version['name'] = $bundle->getName();

                $themeVersionArray = $version->toArray();
                unset($themeVersionArray['id']);
                $themeVersionArray['xhtml'] = 1;
            } else {
                // 2.0-module spec
                $themeMetaData->setTranslator($this->translator);
                $themeMetaData->setDirectoryFromBundle($bundle);
                $themeVersionArray = $themeMetaData->getThemeFilteredVersionInfoArray();
            }

            $directory = explode('/', $bundle->getRelativePath());
            array_shift($directory);
            $themeVersionArray['directory'] = implode('/', $directory);

            // loads the gettext domain for theme
            \ZLanguage::bindThemeDomain($bundle->getName());

            // set defaults for all themes
            $themeVersionArray['type'] = 3;
            $themeVersionArray['state'] = ThemeEntityRepository::STATE_INACTIVE;
            $themeVersionArray['contact'] = 3;

            $filethemes[$bundle->getName()] = $themeVersionArray;
        }

        // scan for old theme types (<Core-1.4) @deprecated - remove at Core-2.0
        $dirArray = \FileUtil::getFiles('themes', false, true, null, 'd');
        foreach ($dirArray as $dir) {
            // Work out the theme type
            if (file_exists("themes/$dir/version.php")) {
                $themetype = 3;
                // set defaults
                $themeversion['name'] = preg_replace('/_/', ' ', $dir);
                $themeversion['displayname'] = preg_replace('/_/', ' ', $dir);
                $themeversion['version'] = '0';
                $themeversion['description'] = '';
                include "themes/$dir/version.php";
            } else {
                // anything else isn't a theme
                // this skips all directories not containing a version.php file (including >=1.4-type themes)
                continue;
            }

            $filethemes[$themeversion['name']] = [
                'directory' => $dir,
                'name' => $themeversion['name'],
                'type' => 3,
                'displayname' => (isset($themeversion['displayname']) ? $themeversion['displayname'] : $themeversion['name']),
                'version' => (isset($themeversion['version']) ? $themeversion['version'] : '1.0.0'),
                'description' => (isset($themeversion['description']) ? $themeversion['description'] : $themeversion['displayname']),
                'admin' => (isset($themeversion['admin']) ? (int)$themeversion['admin'] : '0'),
                'user' => (isset($themeversion['user']) ? (int)$themeversion['user'] : '1'),
                'system' => (isset($themeversion['system']) ? (int)$themeversion['system'] : '0'),
                'state' => (isset($themeversion['state']) ? $themeversion['state'] : ThemeEntityRepository::STATE_INACTIVE),
                'contact' => (isset($themeversion['contact']) ? $themeversion['contact'] : ''),
                'xhtml' => (isset($themeversion['xhtml']) ? (int)$themeversion['xhtml'] : 1)
            ];

            unset($themeversion);
            unset($themetype);
        }

        /**
         * Persist themes
         */
        $dbthemes = [];
        $themeEntities = $this->themeEntityRepository->findAll();

        // @todo - can this be done with the `findAll()` method or doctrine (index by name, hydrate to array?)
        foreach ($themeEntities as $entity) {
            $entity = $entity->toArray();
            $dbthemes[$entity['name']] = $entity;
        }

        // See if we have lost any themes since last generation
        foreach ($dbthemes as $name => $themeinfo) {
            if (empty($filethemes[$name])) {
                // delete a running configuration
                try {
                    \ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deleterunningconfig', ['themename' => $name]);
                } catch (\Exception $e) {
                    if (\System::isInstalling()) {
                        // silent fail when installing or upgrading
                    } else {
                        throw $e;
                    }
                }

                // delete item from db
                $item = $this->themeEntityRepository->findOneBy(['name' => $name]);
                $this->themeEntityRepository->removeAndFlush($item);

                unset($dbthemes[$name]);
            }
        }

        // See if we have gained any themes since last generation,
        // or if any current themes have been upgraded
        foreach ($filethemes as $name => $themeinfo) {
            if (empty($dbthemes[$name])) {
                // add item to db
                $item = new ThemeEntity();
                $item->merge($themeinfo);
                $this->themeEntityRepository->persistAndFlush($item);
            }
        }

        // see if any themes have changed
        foreach ($filethemes as $name => $themeinfo) {
            if (isset($dbthemes[$name])) {
                if (($themeinfo['directory'] != $dbthemes[$name]['directory']) ||
                    ($themeinfo['type'] != $dbthemes[$name]['type']) ||
                    ($themeinfo['description'] != $dbthemes[$name]['description']) ||
                        ($themeinfo['version'] != $dbthemes[$name]['version']) ||
                        ($themeinfo['admin'] != $dbthemes[$name]['admin']) ||
                        ($themeinfo['user'] != $dbthemes[$name]['user']) ||
                        ($themeinfo['system'] != $dbthemes[$name]['system']) ||
                        ($themeinfo['contact'] != $dbthemes[$name]['contact']) ||
                        ($themeinfo['xhtml'] != $dbthemes[$name]['xhtml'])) {
                    $themeinfo['id'] = $dbthemes[$name]['id'];

                    // update item
                    /** @var $item ThemeEntity */
                    $item = $this->themeEntityRepository->find($themeinfo['id']);
                    $item->merge($themeinfo);
                    $this->themeEntityRepository->persistAndFlush($item);
                }
            }
        }

        return true;
    }
}
