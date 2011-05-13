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

class Theme_Block_Render extends Zikula_Controller_AbstractBlock
{
    /**
     * Module to render.
     *
     * @var string
     */
    protected $rmodule;

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        // do not configure the view as a normal controller
    }

    /**
     * Set view property.
     *
     * @param Zikula_View $view Default null means new Render instance for this module name.
     *
     * @return Zikula_AbstractController
     */
    protected function setView(Zikula_View $view = null)
    {
        if (is_null($view)) {
            $view = Zikula_View::getInstance($this->rmodule ? $this->rmodule : 'Theme');
        }

        $this->view = $view;
        return $this;
    }

    /**
     * Initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Theme:Renderblock:', 'Block ID::');
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
     * @param     $blockinfo     blockinfo array
     * @return    string   HTML output string
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$blockinfo[bid]::", ACCESS_OVERVIEW)) {
            return;
        }

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // If the module is not specified or available does nothing
        if (!isset($vars['module']) || empty($vars['module']) || !ModUtil::available($vars['module'])) {
            return;
        } else {
            $this->rmodule = $vars['module'];
        }

        $showerror = SecurityUtil::checkPermission('Theme:Renderblock:', "$blockinfo[bid]::", ACCESS_ADMIN);

        // If the template is not speficied or empty it register an error for the admin
        if (!isset($vars['template']) || empty($vars['template'])) {
            if ($showerror) {
                LogUtil::registerError($this->__f('Misconfigured block. ID: %s', $blockinfo['bid']));
            }
            return;
        }

        // configure the view object
        $this->configureView();

        // checks the existance of the template
        if (!$this->view->template_exists($vars['template'])) {
            if ($showerror) {
                LogUtil::registerError($this->__f('The specified template for the render block doesn\'t exists for the \'%1$s\' module. Block ID: %2$s', array($vars['module'], $blockinfo['bid'])));
            }
            return;
        }

        // Get the additional parameters and assign them
        if (isset($vars['parameters']) && !empty($vars['parameters'])) {
            $params = explode(';', $vars['parameters']);
            if (count($params) > 0 ) {
                foreach ($params as $param) {
                    $assign = explode('=', $param);
                    $this->view->assign(trim($assign[0]), trim($assign[1]));
                }
            }
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        $blockinfo['content'] = $this->view->fetch($vars['template']);

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * Modify the block
     *
     * @param     $blockinfo     blockinfo array
     * @return    string   HTML output string
     */
    public function modify($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$blockinfo[bid]::", ACCESS_ADMIN)) {
            return false;
        }

        $this->configureView();

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // validate the current data
        $valid = false;
        $warnings = array();
        if (isset($vars['module']) && $vars['module']) {
            if (ModUtil::available($vars['module'])) {
                if (isset($vars['template']) && $vars['template']) {
                    $view = Zikula_View::getInstance($vars['module']);
                    if (!$view->template_exists($vars['template'])) {
                        $warnings[] = $this->__f("The specified template does not exist on the '%s' module.", $vars['module']);
                    } else {
                        $valid = true;
                    }
                }
            } else {
                $warnings[] = $this->__f("The module '%s' is not available.", $vars['module']);
            }
        }

        if (!$valid) {
            $warnings[] = $this->__('With the current setup the block is disabled.');
        }

        // generate the output
        return $this->view->assign($vars)
                          ->assign('warnings', $warnings)
                          ->fetch('theme_block_render_modify.tpl');
    }

    /**
     * Update the block
     *
     * @param     $blockinfo     old blockinfo array
     * @return    array    new blockinfo array
     */
    function update($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$blockinfo[bid]::", ACCESS_ADMIN)) {
            return false;
        }

        $module = FormUtil::getPassedValue('rmodule', null, 'POST');
        $template = FormUtil::getPassedValue('rtemplate', null, 'POST');
        $parameters = FormUtil::getPassedValue('rparameters', null, 'POST');

        $blockinfo['content'] = BlockUtil::varsToContent(compact('module', 'template', 'parameters' ));

        return($blockinfo);
    }
}
