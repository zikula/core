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
 * Zikula_View function to use the _dgettext() function
 *
 * This function takes a identifier and returns the corresponding language constant.
 *
 * Available parameters:
 *   - text:     (required) string to translate
 *   - tagN:     (optional) replace for sprintf() e.g. %s or %1$s
 *   - domain:   (optional) textdomain to be used (not required, the system will fill this out automatically
 *   - comment:  (optional) comment to the translator (this is not processed by this code)
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Examples
 * {gettext}Hello world{/gettext}
 * {gettext tag1=$name}Hello %s{/gettext}
 * {gettext  tag1=$city tag2=$country comment="%1 is a name %2 is the place"}Hello %1$s, welcome to %2$s{/gettext}
 *
 * String replacement follows the rules at http://php.net/sprintf but please note Smarty seems to pass
 * all variables as strings so %s and %n$s are mostly used.
 *
 * @param array       $params  All attributes passed to this function from the template.
 * @param string      $content The block content.
 * @param Zikula_View $view    Reference to the Zikula_View object.
 *
 * @return string Translation if it was available.
 */
function smarty_block_gettext($params, $content, Zikula_View $view)
{
    if ($content) {
        if (isset($params['domain'])) {
            $domain = (strtolower($params['domain']) == 'zikula' ? null : $params['domain']);
        } else {
            $domain = $view->getDomain(); // default domain
        }

        // build array for tags (for %s, %1$s etc) if applicable
        ksort($params);
        $tags = [];
        foreach ($params as $key => $value) {
            if (preg_match('#^tag([0-9]{1,2})$#', $key)) {
                $tags[] = $value;
            }
        }
        $tags = (count($tags) == 0 ? null : $tags);

        // perform gettext
        $output = (isset($tags) ? __f($content, $tags, $domain) : __($content, $domain));

        if (isset($params['assign'])) {
            $render->assign($params['assign'], $output);
        } else {
            return $output;
        }
    }
}
