<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pnRender.php 27057 2009-10-21 16:15:43Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * Render - Zikula wrapper class for Smarty
 * Display a Render block
 *
 * @package     Zikula_System_Modules
 * @subpackage  Theme
 */

class Theme_Block_Render extends Zikula_Block
{
    /**
     * initialise block
     *
     */
    public function init()
    {
        // Security
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

        $render = Renderer::getInstance($vars['module'], false);

        // Get the additional parameters and assign them
        if (isset($vars['parameters']) && !empty($vars['parameters'])) {
            $params = explode(';', $vars['parameters']);
            if (count($params) > 0 ) {
                foreach($params as $param) {
                    $assign = explode('=', $param);
                    $render->assign(trim($assign[0]), trim($assign[1]));
                }
            }
        }

        $row['content'] = $render->fetch($vars['template']);

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

        $render = Renderer::getInstance('Theme', false);
        $render->assign($vars);

        return $render->fetch('theme_block_render.htm');
    }
}
