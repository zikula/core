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
 * Zikula_View outputfilter to add page variables and additional header global into page header
 *
 * By default this output filter places page variable output immediately prior to the closing
 * head tag (</head>). The output can, optionally, be placed anywhere in the template by adding
 * the HTML comment <!-- pagevars --> to the page template. Note that this must always be in
 * the header for the output to function correctly.
 *
 * @param string      $source Output source
 * @param Zikula_View $view   Reference to Zikula_View instance
 *
 * @return string
 */
function smarty_outputfilter_pagevars($source, $view)
{
    $return = '';

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
    $cssjscombine = ModUtil::getVar('ZikulaThemeModule', 'cssjscombine', false);

    $type = $view->getRequest()->get('type');
    $zkType = $view->getRequest()->attributes->get('_zkType');
    $methodAnnotationValue = ServiceUtil::get('zikula_core.common.theme_engine')->getAnnotationValue(); // Core-2.0 FC
    $isAdminController = ('admin' == $type || 'admin' == $zkType || (isset($methodAnnotationValue) && ('admin' == $methodAnnotationValue)));

    // get list of stylesheets and scripts from JCSSUtil
    $jcss = JCSSUtil::prepareJCSS($cssjscombine, $view->cache_dir, $themeinfo, $isAdminController);

    if (is_array($jcss['stylesheets']) && !empty($jcss['stylesheets'])) {
        foreach ($jcss['stylesheets'] as $stylesheet) {
            if (empty($stylesheet)) {
                continue;
            }
            // check if the stylesheets is in the additional_header array
            if ($themeinfo['xhtml']) {
                $return .= '<link rel="stylesheet" href="' . DataUtil::formatForDisplay($stylesheet) . '" type="text/css" />' . "\n";
            } else {
                $return .= '<link rel="stylesheet" href="' . DataUtil::formatForDisplay($stylesheet) . '" type="text/css">' . "\n";
            }
        }
    }

    // get inline js config and print it just before any script tag
    $jsConfig = JCSSUtil::getJSConfig();
    if (!empty($jsConfig)) {
        $return .= $jsConfig;
    }

    if (is_array($jcss['javascripts']) && !empty($jcss['javascripts'])) {
        foreach ($jcss['javascripts'] as $j => $javascript) {
            if (empty($javascript)) {
                unset($jcss['javascripts'][$j]);
                continue;
            }
            // check if the javascript is in the additional_header array
            $return .= '<script type="text/javascript" src="' . DataUtil::formatForDisplay($javascript) . '"></script>' . "\n";
        }
    }

    $headerContent = PageUtil::getVar('header');
    if (is_array($headerContent) && !empty($headerContent)) {
        $return .= implode("\n", $headerContent) . "\n";
    }

    // if we've got some page vars to add the header wrap the output in
    // suitable identifying comments when in development mode
    $return = trim($return);
    if (!empty($return) && 0 != System::getVar('development')) {
        $return = "<!-- zikula pagevars -->\n" . $return . "\n<!-- /zikula pagevars -->";
    }

    // get any body page vars
    $bodyvars = PageUtil::getVar('body');
    if (!empty($bodyvars)) {
        $bodyattribs = '<body ' . @implode(' ', $bodyvars) . '>';
        $source = str_replace('<body>', $bodyattribs, $source);
    }

    // get any footer page vars
    $footervars = PageUtil::getVar('footer');
    if (!empty($footervars)) {
        $footersource = @implode("\n", $footervars) . "\n</body>";
        $source = str_replace('</body>', $footersource, $source);
    }

    // replace the string in the template source
    if (stripos($source, '<!-- pagevars -->')) {
        $source = str_replace('<!-- pagevars -->', $return, $source);
    } else {
        $headPos = stripos($source, '</head>');
        if (false !== $headPos) {
            if ($headPos == strripos($source, '</head>')) {
                // Position of the first </head> matches the last </head> so str_replace is safe
                $source = str_replace('</head>', $return . "\n</head>", $source);
            } else {
                // Position of the first </head> does not match the last </head> so str_replace is NOT safe
                // There was probably a {zdebug} tag opening a _dbgconsole.
                // Need to use preg_replace so we can limit to the first.
                preg_replace('#</head>#i', $return . "\n</head>", $source, 1);
            }
        }
    }

    // return the modified source
    return $source;
}
