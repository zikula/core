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

namespace BlocksModule\Block;

use UserUtil, ModUtil, SecurityUtil, LogUtil, DataUtil, System, ZLanguage, CategoryRegistryUtil, CategoryUtil;
use PageUtil, ThemeUtil, BlockUtil, EventUtil;

class XsltBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('xsltblock::', 'Block title::');
    }

    /**
     * get information on block
     *
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
        $doc = new \DOMDocument();
        $xsl = new \XSLTProcessor();

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
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the bock form
     */
    public function modify($blockinfo)
    {
        // Get our block vars
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        $this->view->setCaching(\Zikula_View::CACHE_DISABLED);

        // assign our block vars
        $this->view->assign($vars);

        // return the output
        return $this->view->fetch('Block/xslt_modify.tpl');
    }

    /**
     * update block settings
     *
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        $vars['docurl']        = $this->request->request->get('docurl', '');
        $vars['styleurl']      = $this->request->request->get('styleurl', '');
        $vars['doccontents']   = $this->request->request->get('doccontents', '');
        $vars['stylecontents'] = $this->request->request->get('stylecontents', '');

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        return($blockinfo);
    }
}
