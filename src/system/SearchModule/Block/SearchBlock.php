<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\SearchModule\AbstractSearchable;

/**
 * Block to display a search form
 */
class SearchBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered bock
     */
    public function display(array $properties)
    {
        $title = !empty($properties['title']) ? $properties['title'] : '';
        if (!$this->hasPermission('Searchblock::', "$title::", ACCESS_READ)) {
            return '';
        }
        // set defaults
        $properties['displaySearchBtn'] = isset($properties['displaySearchBtn']) ? $properties['displaySearchBtn'] : false;
        $properties['active'] = isset($properties['active']) ? $properties['active'] : [];
        $pluginOptions = [];

        // LEGACY handling (<1.4.0) @deprecated - remove at Core-2.0
        foreach ($properties['active'] as $moduleName) {
            $pluginOptions[$moduleName] = \ModUtil::apiFunc($moduleName, 'search', 'options', $properties);
        }
        // 1.4.x type handling @deprecated - remove at Core-2.0
        $core14searchableModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(AbstractSearchable::SEARCHABLE);
        foreach ($core14searchableModules as $searchableModule) {
            if (!in_array($searchableModule['name'], $properties['active'])) {
                continue;
            }
            if ($this->getVar('disable_' . $searchableModule['name'])) {
                continue;
            }
            if (!$this->hasPermission('ZikulaSearchModule::Item', $searchableModule['name'] . '::', ACCESS_READ)) {
                continue;
            }
            $moduleBundle = \ModUtil::getModule($searchableModule['name']);
            /** @var $searchableInstance AbstractSearchable */
            $searchableInstance = new $searchableModule['capabilities']['searchable']['class']($this->get('service_container'), $moduleBundle);
            if (!($searchableInstance instanceof AbstractSearchable)) {
                continue;
            }
            $pluginOptions[$searchableModule['name']] = $searchableInstance->getOptions(true, []);
        }

        // get Core-2.0 searchable modules
        $searchableModules = $this->get('zikula_search_module.internal.searchable_module_collector')->getAll();
        $moduleFormBuilder = $this->get('form.factory')
            ->createNamedBuilder('modules', 'Symfony\Component\Form\Extension\Core\Type\FormType', [], [
                'auto_initialize' => false,
                'required' => false
            ]);
        foreach ($searchableModules as $moduleName => $searchableInstance) {
            if (!in_array($moduleName, $properties['active'])) {
                continue;
            }
            if (!$this->hasPermission('ZikulaSearchModule::Item', $moduleName . '::', ACCESS_READ)) {
                continue;
            }
            $moduleFormBuilder->add($moduleName, 'Zikula\SearchModule\Form\Type\AmendableModuleSearchType', [
                'label' => $this->get('kernel')->getModule($moduleName)->getMetaData()->getDisplayName(),
                'translator' => $this->getTranslator(),
                'active' => true,
                'permissionApi' => $this->get('zikula_permissions_module.api.permission')
            ]);
            $searchableInstance->amendForm($moduleFormBuilder->get($moduleName));
        }
        $form = $this->get('form.factory')->create('Zikula\SearchModule\Form\Type\SearchType', [], [
            'translator' => $this->get('translator.default'),
            'action' => $this->get('router')->generate('zikulasearchmodule_search_execute')
        ]);
        $form->add($moduleFormBuilder->getForm());

        $templateParameters = [
            'form' => $form->createView(),
            'properties' => $properties,
            'pluginOptions' => $pluginOptions
        ];

        return $this->renderView('@ZikulaSearchModule/Block/search.html.twig', $templateParameters);
    }

    /**
     * Returns the fully qualified class name of the block's form class.
     *
     * @return string Template path
     */
    public function getFormClassName()
    {
        return 'Zikula\SearchModule\Block\Form\Type\SearchBlockType';
    }

    /**
     * Returns any array of form options.
     *
     * @return array Options array
     */
    public function getFormOptions()
    {
        $searchModules = [];
        // get all the old type search plugins @deprecated remove at Core-2.0
        $search_modules = \ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins');
        foreach ($search_modules as $module) {
            $searchModules[$module['title']] = $module['name'];
        }
        // get 1.4.x type searchable modules and add to array @deprecated remove at Core-2.0
        $searchableModules = \ModUtil::getModulesCapableOf(AbstractSearchable::SEARCHABLE);
        foreach ($searchableModules as $searchableModule) {
            $searchModules[$searchableModule['displayname']] = $searchableModule['name'];
        }
        // get Core-2.0 type searchable modules and add to array
        $searchableModules = $this->get('zikula_search_module.internal.searchable_module_collector')->getAll();
        foreach (array_keys($searchableModules) as $moduleName) {
            $displayName = $this->get('kernel')->getModule($moduleName)->getMetaData()->getDisplayName();
            $searchModules[$displayName] = $moduleName;
        }
        // remove disabled
        foreach ($searchModules as $displayName => $moduleName) {
            if ((bool) $this->getVar('disable_' . $moduleName, false)) {
                unset($searchModules[$displayName]);
            }
        }

        // get all the search plugins
        return [
            'activeModules' => $searchModules
        ];
    }

    /**
     * Returns the template used for rendering the editing form.
     *
     * @return string Template path
     */
    public function getFormTemplate()
    {
        return '@ZikulaSearchModule/Block/search_modify.html.twig';
    }
}
