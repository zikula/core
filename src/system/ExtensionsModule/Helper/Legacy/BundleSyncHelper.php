<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Helper\Legacy;

use Zikula\ExtensionsModule\Util as ExtensionsUtil;

/**
 * @deprecated remove at Core-2.0
 * Class BundleSyncHelper
 *
 * A class to assist in the syncronization of legacy modules.
 *
 * @package Zikula\ExtensionsModule\Helper\Legacy
 */
class BundleSyncHelper
{
    public static function scanForModules()
    {
        $filemodules = array();

        // set the paths to search
        $rootdirs = array('modules' => \ModUtil::TYPE_MODULE); // do not scan `/system` since all are accounted for above

        // scan for legacy modules
        // NOTE: the scan below does rescan all psr-0 & psr-4 type modules and intentionally fails.
        foreach ($rootdirs as $rootdir => $moduletype) {
            if (is_dir($rootdir)) {
                $dirs = \FileUtil::getFiles($rootdir, false, true, null, 'd');

                foreach ($dirs as $dir) {
                    $oomod = false;
                    // register autoloader
                    if (file_exists("$rootdir/$dir/Version.php") || is_dir("$rootdir/$dir/lib")) {
                        \ZLoader::addAutoloader($dir, array($rootdir, "$rootdir/$dir/lib"));
                        \ZLoader::addPrefix($dir, $rootdir);
                        $oomod = true;
                    }

                    // loads the gettext domain for 3rd party modules
                    if (is_dir("modules/$dir/locale")) {
                        \ZLanguage::bindModuleDomain($dir);
                    }

                    try {
                        $modversion = ExtensionsUtil::getVersionMeta($dir, $rootdir);
                    } catch (\Exception $e) {
                        throw new \RuntimeException($e->getMessage());
                        continue;
                    }

                    if (!$modversion) {
                        continue;
                    }

                    if (!isset($modversion['capabilities'])) {
                        $modversion['capabilities'] = array();
                    }

                    $name = $dir;

                    // Get the module version
                    if (!$modversion instanceof \Zikula_AbstractVersion) {
                        if (isset($modversion['profile']) && $modversion['profile']) {
                            $modversion['capabilities']['profile'] = '1.0';
                        }
                        if (isset($modversion['message']) && $modversion['message']) {
                            $modversion['capabilities']['message'] = '1.0';
                        }
                    } elseif ($oomod) {
                        // Work out if admin-capable
                        if (file_exists("$rootdir/$dir/lib/$dir/Controller/Admin.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['admin'] = array('url' => \ModUtil::url($modversion['name'], 'admin', 'index'));
                            $modversion['capabilities'] = $caps;
                        }

                        // Work out if user-capable
                        if (file_exists("$rootdir/$dir/lib/$dir/Controller/User.php")) {
                            $caps = $modversion['capabilities'];
                            $caps['user'] = array('url' => \ModUtil::url($modversion['name'], 'user', 'index'));
                            $modversion['capabilities'] = $caps;
                        }
                    }

                    $version = $modversion['version'];
                    $description = $modversion['description'];

                    if (isset($modversion['displayname']) && !empty($modversion['displayname'])) {
                        $displayname = $modversion['displayname'];
                    } else {
                        $displayname = $modversion['name'];
                    }

                    $capabilities = serialize($modversion['capabilities']);

                    // bc for urls
                    if (isset($modversion['url']) && !empty($modversion['url'])) {
                        $url = $modversion['url'];
                    } else {
                        $url = $displayname;
                    }

                    if (isset($modversion['securityschema']) && is_array($modversion['securityschema'])) {
                        $securityschema = serialize($modversion['securityschema']);
                    } else {
                        $securityschema = serialize(array());
                    }

                    $core_min = isset($modversion['core_min']) ? $modversion['core_min'] : '';
                    $core_max = isset($modversion['core_max']) ? $modversion['core_max'] : '';
                    $oldnames = isset($modversion['oldnames']) ? $modversion['oldnames'] : '';

                    if (isset($modversion['dependencies']) && is_array($modversion['dependencies'])) {
                        $moddependencies = serialize($modversion['dependencies']);
                    } else {
                        $moddependencies = serialize(array());
                    }

                    $filemodules[$name] = array(
                        'directory'       => $dir,
                        'name'            => $name,
                        'type'            => $moduletype,
                        'displayname'     => $displayname,
                        'url'             => $url,
                        'oldnames'        => $oldnames,
                        'version'         => $version,
                        'capabilities'    => $capabilities,
                        'description'     => $description,
                        'securityschema'  => $securityschema,
                        'dependencies'    => $moddependencies,
                        'core_min'        => $core_min,
                        'core_max'        => $core_max,
                    );

                    // important: unset modversion and modtype, otherwise the
                    // following modules will have some values not defined in
                    // the next version files to be read
                    unset($modversion);
                    unset($modtype);
                }
            }
        }

        return $filemodules;
    }
}
