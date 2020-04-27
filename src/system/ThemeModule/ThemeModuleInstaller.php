<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

class ThemeModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        // define defaults for module vars
        $this->setVar('modulesnocache');
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

    public function upgrade(string $oldVersion): bool
    {
        // 3.4.3 shipped with Core-1.4.3
        // 3.4.4 shipped with Core-2.0.15
        // version number reset to 3.0.0 at Core 3.0.0
        switch ($oldVersion) {
            case '2.9.9':
                // nothing to do
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }
}
