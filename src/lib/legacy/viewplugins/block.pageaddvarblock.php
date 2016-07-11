<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 *   <script type="text/javascript">
 *       alert ('The closing </body> tag is coming.');
 *   </script>
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
            LogUtil::log(__f('Warning! The page variable %1$s is deprecated. Please use %2$s instead.', ['rawtext', 'header']), E_USER_DEPRECATED);
            $varname = 'header';
        }

        if (($varname != 'header') && ($varname != 'footer')) {
            throw new Zikula_Exception_Fatal(__f('Invalid page variable name: \'%1$s\'.', [$varname]));
        }

        PageUtil::addVar($varname, $content);
    }
}
