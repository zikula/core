<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: html.php 20806 2006-12-19 13:46:28Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 * @author Mark West
 */

class Blocks_Block_Xslt extends Zikula_Block
{
    /**
     * initialise block
     *
     * @author       The Zikula Development Team
     */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('xsltblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @author       The Zikula Development Team
     * @return       array       The block information
     */
    public function info()
    {
        return array('module'         => 'Blocks',
                'text_type'       => $this->__('XSLT'),
                'text_type_long'  => $this->__('XSLT'),
                'allow_multiple'  => true,
                'form_content'    => false,
                'form_refresh'    => false,
                'show_preview'    => true,
                'admin_tableless' => true);
    }

    /**
     * display block
     *
     * @author       The Zikula Development Team
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('xsltblock::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        // Get our block vars
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        if ( (!isset($vars['docurl']) || !isset($vars['styleurl'])) &&
                (!isset($vars['doccontents']) || !isset($vars['stylecontents']))) {
            return;
        }

        // create new objects
        $doc = new DOMDocument();
        $xsl = new XSLTProcessor();

        // load stylesheet
        if (isset($vars['styleurl']) && !empty($vars['styleurl'])) {
            $doc->load($vars['styleurl']);
        } else {
            $doc->loadXML($vars['stylecontents']);
        }
        $xsl->importStyleSheet($doc);

        // load xml source
        if (isset($vars['docurl']) && !empty($vars['docurl'])) {
            $doc->load($vars['docurl']);
        } else {
            $doc->loadXML($vars['doccontents']);
        }

        // apply stylesheet and return output
        $blockinfo['content'] = $xsl->transformToXML($doc);
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     *
     * @author       The Zikula Development Team
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the bock form
     */
    public function modify($blockinfo)
    {
        // Get our block vars
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Create output object
        $renderer = Renderer::getInstance('Blocks', false);

        // assign our block vars
        $renderer->assign($vars);

        // return the output
        return $renderer->fetch('blocks_block_xslt_modify.htm');
    }

    /**
     * update block settings
     *
     * @author       The Zikula Development Team
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        $vars['docurl'] = FormUtil::getPassedValue('docurl', '', 'POST');
        $vars['styleurl'] = FormUtil::getPassedValue('styleurl', '', 'POST');
        $vars['doccontents'] = FormUtil::getPassedValue('doccontents', '', 'POST');
        $vars['stylecontents'] = FormUtil::getPassedValue('stylecontents', '', 'POST');

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        return($blockinfo);
    }
}