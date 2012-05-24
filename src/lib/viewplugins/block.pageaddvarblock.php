<?php
/**
 * Copyright Zikula Foundation 2011 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_View function to add the contents of a block to either the header or footer multicontent page variable
 *
 * This function adds the content of the block to either the end of the <head> portion of the page (using 'header') or to
 * a position just prior to the closing </body> tag (using 'footer').
 *
 * Available parameters:
 *   - name:     The name of the page variable to set, either 'header' or 'footer'; optional, default is 'header'
 *
 * Examples:
 *
 *  This inline stylesheet will appear in the page's <head> section just before the closing </head>:
 * <code>
 *   {pageaddvarblock name='header'}
 *   <style type="text/css">
 *       p { font-size: 1.5em; }
 *   </style>
 *   {/pageaddvarblock}
 * </code>
 *
 *  This inline script will appear in the page's <body> section just before the closing </body>:
 * <code>
 *   {pageaddvarblock name='footer'}
 *   <script language="javascript" type="text/javascript">
 *       alert ('The closing </body> tag is coming.');
 *   </style>
 *   {/pageaddvarblock}
 * </code>
 *
 * @param array       $params  All attributes passed to this function from the template.
 * @param string      $content The content of the block.
 * @param Zikula_View $view    Reference to the Zikula_View object.
 *
 * @return string
 */
function smarty_block_pageaddvarblock($params, $content, Zikula_View $view)
{
    if ($content) {
        $varname = isset($params['name']) ? $params['name'] : 'header';

        if (System::isLegacyMode() && ($varname == 'rawtext')) {
            LogUtil::log(__f('Warning! The page variable %1$s is deprecated. Please use %2$s instead.', array('rawtext', 'header')), E_USER_DEPRECATED);
            $varname = 'header';
        }

        if (($varname != 'header') && ($varname != 'footer')) {
            throw new Zikula_Exception_Fatal(__f('Invalid page variable name: \'%1$s\'.', array($varname)));
        }

        PageUtil::addVar($varname, $content);
    }
}
