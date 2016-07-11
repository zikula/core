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

use ModUtil;
use Zikula\BlocksModule\AbstractBlockHandler;

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

        // set some defaults
        if (empty($title)) {
            $title = $this->__('Search');
        }
        if (!isset($properties['displaySearchBtn'])) {
            $properties['displaySearchBtn'] = 0;
        }
        if (!isset($properties['active'])) {
            $properties['active'] = [];
        }

        $pluginOptions = [];
        $activeModules = array_keys($properties['active']);
        foreach ($activeModules as $moduleName) {
            $pluginOptions[$moduleName] = ModUtil::apiFunc($moduleName, 'search', 'options', $properties);
        }

        $templateParameters = [
            'properties' => $properties,
            'pluginOptions' => $pluginOptions
        ];

        return $this->renderView('@ZikulaSearchModule/Block/search.html.twig', $templateParameters);
    }

    /**
     * Returns the fully qualified class name of the block's form class.
     *
     * @return string Template path.
     */
    public function getFormClassName()
    {
        return 'Zikula\SearchModule\Block\Form\Type\SearchBlockType';
    }

    /**
     * Returns any array of form options.
     *
     * @return array Options array.
     */
    public function getFormOptions()
    {
        // get all the search plugins
        return [
            'plugins' => ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins')
        ];
    }

    /**
     * Returns the template used for rendering the editing form.
     *
     * @return string Template path.
     */
    public function getFormTemplate()
    {
        return '@ZikulaSearchModule/Block/search_modify.html.twig';
    }
}
