<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\SearchModule\AbstractSearchable;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("ZikulaSearchModule:Config:config.html.twig")
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return Response|array
     */
    public function configAction(Request $request)
    {
        // Security check
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $modVars = $this->getVars();
        $plugins = [];

        // get all the LEGACY (<1.4.0) search plugins @deprecated
        $legacySearchableModules = \ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins', ['loadall' => true]);
        $legacySearchableModules = false === $legacySearchableModules ? [] : $legacySearchableModules;
        foreach ($legacySearchableModules as $key => $legacySearchableModule) {
            $modid = \ModUtil::getIdFromName($legacySearchableModule['title']);
            $modinfo = \ModUtil::getInfo($modid);
            $plugins[$modinfo['displayname']] = $legacySearchableModule['title'];
        }

        // get 1.4.0 type searchable modules and add to array @deprecated
        $searchableModules = \ModUtil::getModulesCapableOf(AbstractSearchable::SEARCHABLE);
        foreach ($searchableModules as $searchableModule) {
            $plugins[$searchableModule['displayname']] = $searchableModule['name'];
        }

        // get Core-2.0 type searchable modules and add to array
        $searchableModules = $this->get('zikula_search_module.internal.searchable_module_collector')->getAll();
        foreach (array_keys($searchableModules) as $searchableModuleName) {
            $displayName = $this->get('kernel')->getModule($searchableModuleName)->getMetaData()->getDisplayName();
            $plugins[$displayName] = $searchableModuleName;
        }

        $disabledPlugins = [];
        // get the disabled state
        foreach ($plugins as $moduleName) {
            $modVarKey = 'disable_' . $moduleName;
            if (!isset($modVars[$modVarKey])) {
                continue;
            }
            if ($modVars[$modVarKey]) {
                $disabledPlugins[] = $moduleName;
            }
            unset($modVars[$modVarKey]);
        }
        $modVars['plugins'] = $disabledPlugins;

        $form = $this->createForm('Zikula\SearchModule\Form\Type\ConfigType', $modVars, [
                'translator' => $this->get('translator.default'),
                'plugins' => $plugins
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                foreach ($plugins as $searchPlugin) {
                    // set the disabled flag
                    $disabledFlag = in_array($searchPlugin, $formData['plugins']);
                    $this->setVar('disable_' . $searchPlugin, $disabledFlag);
                }
                unset($formData['plugins']);
                $this->setVars($formData);
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
            'plugins' => $plugins
        ];
    }
}
