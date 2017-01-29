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

use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
     * @Template
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function configAction(Request $request)
    {
        // Security check
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $modVars = $variableApi->getAll('ZikulaSearchModule');

        // get all the LEGACY (<1.4.0) search plugins
        $plugins = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins', ['loadall' => true]);
        $plugins = false === $plugins ? [] : $plugins;

        // get 1.4.0+ type searchable modules and add to array
        $searchableModules = ModUtil::getModulesCapableOf(AbstractSearchable::SEARCHABLE);
        foreach ($searchableModules as $searchableModule) {
            $plugins[] = ['title' => $searchableModule['name']];
        }

        // get 1.4.0+ type searchable modules and add to array
        $searchableModules = $this->get('zikula_search_module.internal.searchable_module_collector')->getAll();
        foreach (array_keys($searchableModules) as $searchableModuleName) {
            $plugins[] = ['title' => $searchableModuleName];
        }

        $disabledPlugins = [];

        // get the disabled state
        foreach ($plugins as $key => $plugin) {
            if (!isset($plugin['title'])) {
                continue;
            }
            $modVarKey = 'disable_' . $plugin['title'];
            if (!isset($modVars[$modVarKey])) {
                continue;
            }
            if ($modVars[$modVarKey]) {
                $disabledPlugins[] = $plugin['title'];
            }
            unset($modVars[$modVarKey]);
        }
        $modVars['plugins'] = $disabledPlugins;

        $form = $this->createForm('Zikula\SearchModule\Form\Type\ConfigType',
            $modVars, [
                'translator' => $this->get('translator.default'),
                'plugins' => $plugins
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // Update module variables.
                $this->setVar('itemsperpage', $formData['itemsperpage']);
                $this->setVar('limitsummary', $formData['limitsummary']);
                $this->setVar('opensearch_adult_content', $formData['opensearch_adult_content']);
                $this->setVar('opensearch_enabled', $formData['opensearch_enabled']);

                // loop round the plugins
                foreach ($plugins as $searchPlugin) {
                    if (!isset($searchPlugin['title'])) {
                        continue;
                    }
                    // set the disabled flag
                    $disabledFlag = in_array($searchPlugin['title'], $formData['plugins']);
                    $this->setVar('disable_' . $searchPlugin['title'], $disabledFlag);
                }

                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        $templateParameters = array_merge($modVars, [
            'form' => $form->createView(),
            'plugins' => $plugins
        ]);

        return $templateParameters;
    }
}
