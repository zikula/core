<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule;

use Zikula\Core\AbstractExtensionInstaller;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

/**
 * Installation and upgrade routines for the theme module.
 */
class ThemeModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Initialise the theme module.
     *
     * @return boolean true if initialisation successful, false otherwise
     */
    public function install()
    {
        // create the table
        try {
            $this->schemaTool->create(['Zikula\ThemeModule\Entity\ThemeEntity']);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());

            return false;
        }

        // detect all themes on install
        $this->container->get('zikula_theme_module.helper.bundle_sync_helper')->regenerate();

        // activate all current themes
        $themes = $this->container->get('zikula_theme_module.theme_entity.repository')->findAll();
        /** @var \Zikula\ThemeModule\Entity\ThemeEntity $theme */
        foreach ($themes as $theme) {
            $theme->setState(ThemeEntityRepository::STATE_ACTIVE);
        }
        $this->entityManager->flush();

        // define defaults for module vars
        $this->setVar('modulesnocache', '');
        $this->setVar('enablecache', false);
        $this->setVar('compile_check', true);
        $this->setVar('cache_lifetime', 1800);
        $this->setVar('cache_lifetime_mods', 1800);
        $this->setVar('force_compile', false);
        $this->setVar('trimwhitespace', false);
        $this->setVar('maxsizeforlinks', 30);
        $this->setVar('itemsperpage', 25);

        $this->setVar('cssjscombine', false);
        $this->setVar('cssjscompress', false);
        $this->setVar('cssjsminify', false);
        $this->setVar('cssjscombine_lifetime', 3600);

        // View
        $this->setVar('render_compile_check', true);
        $this->setVar('render_force_compile', false);
        $this->setVar('render_cache', false);
        $this->setVar('render_expose_template', false);
        $this->setVar('render_lifetime', 3600);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * @param  string $oldversion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            case '3.4.2':
                $this->delVar('enable_mobile_theme');
                // future upgrade
        }

        // Update successful
        return true;
    }

    /**
     * delete the Admin module
     *
     * @return bool true if deletion successful, false otherwise
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }
}
