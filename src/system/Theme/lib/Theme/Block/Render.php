<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Theme_Block_Render extends Zikula_Block
{
    /**
     * initialise block
     *
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Theme:Renderblock:', 'Block title::');
    }

    /**
     * Get information on block
     *
     * @return    array    blockinfo array
     */
    public function info()
    {
        return array('module'         => 'Theme',
                     'text_type'      => $this->__('Rendering engine'),
                     'text_type_long' => $this->__('Custom rendering engine block'),
                     'allow_multiple' => true,
                     'form_content'   => false,
                     'form_refresh'   => false,
                     'show_preview'   => true);
    }

    /**
     * Display the block
     *
     * @param     $row     blockinfo array
     * @return    string   HTML output string
     */
    public function display($row)
    {
        if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$row[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($row['content']);

        // Parameter check
        if (!isset($vars['template']) || !isset($vars['module'])) {
            $row['content'] = $this->__('Misconfigured block');
            return BlockUtil::themeBlock($row);
        }

        // If the module is available we load the user api.
        // If the module is not available we show an error messages.
        if ( (!ModUtil::available($vars['module'])) || (!ModUtil::loadApi($vars['module'], 'user')) ) {
            $row['content'] = $this->__('Misconfigured block').' - '.$this->__('No module.').$vars['module'];
            return BlockUtil::themeBlock($row);
        }

        $this->view->setCaching(false);

        // Get the additional parameters and assign them
        if (isset($vars['parameters']) && !empty($vars['parameters'])) {
            $params = explode(';', $vars['parameters']);
            if (count($params) > 0 ) {
                foreach($params as $param) {
                    $assign = explode('=', $param);
                    $this->view->assign(trim($assign[0]), trim($assign[1]));
                }
            }
        }

        $row['content'] = $this->view->fetch($vars['template']);

        return BlockUtil::themeBlock($row);
    }

    /**
     * Update the block
     *
     * @param     $row     old blockinfo array
     * @return    array    new blockinfo array
     */
    function update($row)
    {
        if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$row[title]::", ACCESS_ADMIN)) {
            return false;
        }

        $module = FormUtil::getPassedValue('rmodule', null, 'POST');
        $template = FormUtil::getPassedValue('rtemplate', null, 'POST');
        $parameters = FormUtil::getPassedValue('rparameters', null, 'POST');

        $row['content'] = BlockUtil::varsToContent(compact('module', 'template', 'parameters' ));

        return($row);
    }

    /**
     * Modify the block
     *
     * @param     $row     blockinfo array
     * @return    string   HTML output string
     */
    public function modify($row)
    {
        if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$row[title]::", ACCESS_ADMIN)) {
            return false;
        }

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($row['content']);

        // set some defaults
        !isset($vars['module']) ? $vars['module'] = '' : null;
        !isset($vars['template']) ? $vars['template'] = '' : null;
        !isset($vars['parameters']) ? $vars['parameters'] = '' : null;

        $this->view->setCaching(false);

        $this->view->assign($vars);

        return $this->view->fetch('theme_block_render.tpl');
    }
}
