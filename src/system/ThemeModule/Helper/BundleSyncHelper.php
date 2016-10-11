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
            foreach ($themeMetaData->getPsr4() as $ns => $path) {
                \ZLoader::addPrefixPsr4($ns, $path);
            }

            $bundleClass = $themeMetaData->getClass();

            /** @var $bundle \Zikula\ThemeModule\AbstractTheme */
            $bundle = new $bundleClass();
            $themeMetaData->setTranslator($this->translator);
            $themeMetaData->setDirectoryFromBundle($bundle);
            $themeVersionArray = $themeMetaData->getThemeFilteredVersionInfoArray();

            $directory = explode('/', $bundle->getRelativePath());
            array_shift($directory);
            $themeVersionArray['directory'] = implode('/', $directory);

            // loads the gettext domain for theme
//            \ZLanguage::bindThemeDomain($bundle->getName()); // @todo how to replace?

            // set defaults for all themes
            $themeVersionArray['type'] = 3;
            $themeVersionArray['state'] = ThemeEntityRepository::STATE_INACTIVE;
            $themeVersionArray['contact'] = 3;

            $filethemes[$bundle->getName()] = $themeVersionArray;
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
