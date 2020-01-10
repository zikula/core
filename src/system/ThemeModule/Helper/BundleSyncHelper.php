<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Helper;

use Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Bundle\Helper\BundlesSchemaHelper;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Helper\ComposerValidationHelper;
use Zikula\ThemeModule\AbstractTheme;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;
use Zikula\ThemeModule\Entity\ThemeEntity;

/**
 * Helper functions for the theme module
 */
class BundleSyncHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var ThemeEntityRepository
     */
    private $themeEntityRepository;

    /**
     * @var BundlesSchemaHelper
     */
    private $bundlesSchemaHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ComposerValidationHelper
     */
    private $composerValidationHelper;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        ThemeEntityRepository $themeEntityRepository,
        BundlesSchemaHelper $bundlesSchemaHelper,
        TranslatorInterface $translator,
        ComposerValidationHelper $composerValidationHelper,
        SessionInterface $session
    ) {
        $this->kernel = $kernel;
        $this->themeEntityRepository = $themeEntityRepository;
        $this->bundlesSchemaHelper = $bundlesSchemaHelper;
        $this->translator = $translator;
        $this->composerValidationHelper = $composerValidationHelper;
        $this->session = $session;
    }

    /**
     * Regenerates the theme list.
     *
     * @throws Exception
     */
    public function regenerate(): bool
    {
        // sync the filesystem and the bundles table
        $this->bundlesSchemaHelper->load();

        // Get all themes on filesystem
        $bundleThemes = [];

        $scanner = new Scanner();
        $scanner->setTranslator($this->translator);
        $scanner->scan(['themes']);
        $newThemes = $scanner->getThemesMetaData();

        /** @var MetaData $themeMetaData */
        foreach ($newThemes as $name => $themeMetaData) {
            foreach ($themeMetaData->getPsr4() as $ns => $path) {
                $this->kernel->getAutoloader()->addPsr4($ns, $path);
            }

            $bundleClass = $themeMetaData->getClass();

            /** @var $bundle AbstractTheme */
            $bundle = new $bundleClass();
            $themeMetaData->setTranslator($this->translator);
            $themeVersionArray = $themeMetaData->getThemeFilteredVersionInfoArray();

            // set defaults for all themes
            $themeVersionArray['type'] = 3;
            $themeVersionArray['state'] = ThemeEntityRepository::STATE_INACTIVE;
            $themeVersionArray['contact'] = 3;

            $finder = new Finder();
            $finder->files()->in($bundle->getPath())->depth(0)->name('composer.json');
            foreach ($finder as $splFileInfo) {
                // there will only be one loop here
                $this->composerValidationHelper->check($splFileInfo);
                if ($this->composerValidationHelper->isValid()) {
                    $bundleThemes[$bundle->getName()] = $themeVersionArray;
                } else {
                    $this->session->getFlashBag()->add('error', $this->translator->trans('Cannot load %extension because the composer file is invalid.', ['%extension' => $bundle->getName()]));
                    foreach ($this->composerValidationHelper->getErrors() as $error) {
                        $this->session->getFlashBag()->add('error', $error);
                    }
                }
            }
        }

        // persist themes
        $dbthemes = [];
        $themeEntities = $this->themeEntityRepository->findAll();
        /** @var ThemeEntity $entity */
        foreach ($themeEntities as $entity) {
            $entity = $entity->toArray();
            $dbthemes[$entity['name']] = $entity;
        }

        // see if we have lost any themes since last generation
        foreach ($dbthemes as $name => $themeinfo) {
            if (empty($bundleThemes[$name])) {
                /** @var ThemeEntity $item */
                $item = $this->themeEntityRepository->findOneBy(['name' => $name]);
                // delete item from db
                $this->themeEntityRepository->removeAndFlush($item);

                unset($dbthemes[$name]);
            }
        }

        // see if we have gained any themes since last generation,
        // or if any current themes have been upgraded
        foreach ($bundleThemes as $name => $themeinfo) {
            if (!empty($dbthemes[$name])) {
                continue;
            }
            // add item to db
            $item = new ThemeEntity();
            $item->merge($themeinfo);
            $this->themeEntityRepository->persistAndFlush($item);
        }

        // see if any themes have changed
        foreach ($bundleThemes as $name => $themeinfo) {
            if (!isset($dbthemes[$name])) {
                continue;
            }
            if (
                ($dbthemes[$name]['type'] !== $themeinfo['type']) ||
                ($dbthemes[$name]['description'] !== $themeinfo['description']) ||
                ($dbthemes[$name]['version'] !== $themeinfo['version']) ||
                ((bool)$dbthemes[$name]['admin'] !== (bool)$themeinfo['admin']) ||
                ((bool)$dbthemes[$name]['user'] !== (bool)$themeinfo['user']) ||
                ((bool)$dbthemes[$name]['system'] !== (bool)$themeinfo['system']) ||
                ((string)$dbthemes[$name]['contact'] !== (string)$themeinfo['contact'])
            ) {
                $themeinfo['id'] = $dbthemes[$name]['id'];
                // update item
                /** @var $item ThemeEntity */
                $item = $this->themeEntityRepository->find($themeinfo['id']);
                $item->merge($themeinfo);
                $this->themeEntityRepository->persistAndFlush($item);
            }
        }

        return true;
    }
}
