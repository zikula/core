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

namespace Zikula\ExtensionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ModuleController
 * @package Zikula\ExtensionsModule\Controller
 * @Route("/module")
 */
class ModuleController extends AbstractController
{
    /**
     * @Route("/list/{pos}")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @param int $pos
     * @return array
     */
    public function viewModuleListAction(Request $request, $pos = 1)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulaextensionsmodule_module_viewmodulelist');
        $sortableColumns->addColumns([new Column('displayname'), new Column('state')]);
        $sortableColumns->setOrderByFromRequest($request);

        $vetoEvent = new GenericEvent();
        $this->get('event_dispatcher')->dispatch(ExtensionEvents::REGENERATE_VETO, $vetoEvent);
        if (!$vetoEvent->isPropagationStopped() & $pos == 1) {
            // regenerate the extension list only when viewing the first page
            $extensionsInFileSystem = $this->get('zikula_extensions_module.bundle_sync_helper')->scanForBundles();
            $this->get('zikula_extensions_module.bundle_sync_helper')->syncExtensions($extensionsInFileSystem);
        }

        $pagedResult = $this->getDoctrine()->getManager()
            ->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->getPagedCollectionBy([], [$sortableColumns->getSortColumn()->getName() => $sortableColumns->getSortDirection()], $this->getVar('itemsperpage'), $pos);
        $modules = [];
        foreach ($pagedResult as $k => $module) {
            $modules[$k] = $module = $module->toArray();
            $modules[$k]['actions'] = $this->getModuleActions($module);
            $modules[$k]['isCore'] = $this->isCoreModule($module['name']);
//            $modules[$k]['newversion'] = ($modules[$k]['state'] == \ModUtil::STATE_UPGRADED) ? $filemodules[$mod['name']]['version'] : null;
        }

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'pager' => ['limit' => $this->getVar('itemsperpage'), 'count' => count($pagedResult)],
            'modules' => $modules
        ];
    }

    private function getModuleActions(array $module)
    {
        if (!$this->hasPermission('ZikulaExtensionsModule::', "$module[name]::$module[id]", ACCESS_ADMIN)) {
            return $module;
        }

        $actions = [];
        switch ($module['state']) {
            case \ModUtil::STATE_ACTIVE:
                if (!$this->isCoreModule($module['name'])) {
                    $actions[] = [
                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_deactivate', ['id' => $module['id']]),
                        'icon' => 'minus-circle text-danger',
                        'color' => '#c00',
                        'title' => $this->__f('Deactivate %s', ['%s' => $module['displayname']])
                    ];
                }
                if (\PluginUtil::hasModulePlugins($module['name'])) {
                    $actions[] = [
                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_viewplugins', [
                            'bymodule' => $module['name']]),
                        'icon' => 'plug',
                        'color' => 'black',
                        'title' => $this->__f('Plugins for %s', ['%s' => $module['displayname']])
                    ];
                }
                break;
            case \ModUtil::STATE_INACTIVE:
                $actions[] = [
                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_activate', ['id' => $module['id']]),
                    'icon' => 'plus-square text-success',
                    'color' => '#0c0',
                    'title' => $this->__f('Activate %s', ['%s' => $module['displayname']])
                ];
                $actions[] = [
                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_remove', ['id' => $module['id']]),
                    'icon' => 'trash-o',
                    'color' => '#c00',
                    'title' => $this->__f('Uninstall %s', ['%s' => $module['displayname']])
                ];
                break;
            case \ModUtil::STATE_MISSING:
                // Nothing to do.
                break;
            case \ModUtil::STATE_UPGRADED:
                $actions[] = [
                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_upgrade', ['id' => $module['id']]),
                    'icon' => 'refresh',
                    'color' => '#00c',
                    'title' => $this->__f('Upgrade %s', ['%s' => $module['displayname']])
                ];
                break;
            case \ModUtil::STATE_INVALID:
                // nothing to do.
                // do not allow deletion of invalid modules if previously installed (#1278)
                break;
            case \ModUtil::STATE_NOTALLOWED:
                $actions[] = [
                    'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_remove', ['id' => $module['id']]),
                    'icon' => 'trash-o',
                    'color' => '#c00',
                    'title' => $this->__f('Remove %s', ['%s' => $module['displayname']])
                ];
                break;
            case \ModUtil::STATE_UNINITIALISED:
            default:
                if ($module['state'] < 10) {
                    $actions[] = [
                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_initialise', ['id' => $module['id']]),
                        'icon' => 'cog text-success',
                        'color' => '#0c0',
                        'title' => $this->__f('Install %s', ['%s' => $module['displayname']])
                    ];
                } else {
                    $actions[] = [
                        'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_compinfo', ['id' => $module['id']]),
                        'icon' => 'info-circle',
                        'color' => 'black',
                        'title' => $this->__f('Incompatible version: %s', ['%s' => $module['displayname']])
                    ];
                }
                break;
        }

        if ($module['state'] != \ModUtil::STATE_INVALID) {
            $actions[] = [
                'url' => $this->get('router')->generate('zikulaextensionsmodule_admin_modify', ['id' => $module['id']]),
                'icon' => 'wrench',
                'color' => 'black',
                'title' => $this->__f('Edit %s', ['%s' => $module['displayname']])
            ];
        }

        return $actions;
    }

    private function isCoreModule($moduleName)
    {
        return in_array($moduleName, [
            'ZikulaAdminModule',
            'ZikulaBlocksModule',
            'ZikulaCategoriesModule',
            'ZikulaExtensionsModule',
            'ZikulaGroupsModule',
            'ZikulaMailerModule',
            'ZikulaPageLockModule',
            'ZikulaPermissionsModule',
            'ZikulaRoutesModule',
            'ZikulaSearchModule',
            'ZikulaSecurityCenterModule',
            'ZikulaSettingsModule',
            'ZikulaThemeModule',
            'ZikulaUsersModule',
        ]);
    }
}
