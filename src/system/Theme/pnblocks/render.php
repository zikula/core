<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: pnRender.php 27057 2009-10-21 16:15:43Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * pnRender - Zikula wrapper class for Smarty
 * Display a pnRender block
 *
 * @package     Zikula_System_Modules
 * @subpackage  pnRender
 */

/**
 * initialise block
 *
 */
function theme_renderblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('Theme:Renderblock:', 'Block title::');
}

/**
 * Get information on block
 *
 * @return    array    blockinfo array
 */
function theme_renderblock_info()
{
    return array('module'         => 'Theme',
                 'text_type'      => __('Rendering engine'),
                 'text_type_long' => __('Custom rendering engine block'),
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
function theme_renderblock_display($row)
{
    if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$row[title]::", ACCESS_OVERVIEW)) {
        return;
    }

    // Break out options from our content field
    $vars = BlockUtil::varsFromContent($row['content']);

    // Parameter check
    if (!isset($vars['template']) || !isset($vars['module'])) {
        $row['content'] = DataUtil::formatForDisplayHTML(__('Misconfigured block')) ;
        return pnBlockThemeBlock($row);
    }

    // If the module is available we load the user api to ensure that the language
    // defines are present for use inside of the block. If we do not do this, the user
    // will see _THEDEFINES only
    // If the module is not available we show an error messages.
    if ( (!pnModAvailable($vars['module'])) || (!pnModAPILoad($vars['module'], 'user')) ) {
        $row['content'] = DataUtil::formatForDisplayHTML(__('Misconfigured block').' - '.__('No module.')) . $vars['module'];
        return pnBlockThemeBlock($row);
    }

    $pnRender = Renderer::getInstance($vars['module'], false);

    // Get the additional parameters and assign them
    if (isset($vars['parameters']) && !empty($vars['parameters'])) {
        $params = explode( ';', $vars['parameters'] );
        if (count($params) > 0 ) {
            foreach($params as $param) {
                $assign = explode('=', $param);
                $pnRender->assign(trim($assign[0]), trim($assign[1]));
            }
        }
    }

    $row['content'] = $pnRender->fetch($vars['template']);

    return pnBlockThemeBlock($row);
}

/**
 * Update the block
 *
 * @param     $row     old blockinfo array
 * @return    array    new blockinfo array
 */
function theme_renderblock_update($row)
{
    if (!SecurityUtil::checkPermission('Theme:Renderblock:', "$row[title]::", ACCESS_ADMIN)) {
        return false;
    }
    $module = FormUtil::getPassedValue('rmodule', null, 'POST');
    $template = FormUtil::getPassedValue('rtemplate', null, 'POST');
    $parameters = FormUtil::getPassedValue('rparameters', null, 'POST');

    $row['content'] = pnBlockVarsToContent(compact('module', 'template', 'parameters' ));
    return($row);
}

/**
 * Modify the block
 *
 * @param     $row     blockinfo array
 * @return    string   HTML output string
 */
function theme_renderblock_modify($row)
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

    $pnRender = Renderer::getInstance('Theme', false);
    $pnRender->assign($vars);
    return $pnRender->fetch('theme_block_render.htm');
}
