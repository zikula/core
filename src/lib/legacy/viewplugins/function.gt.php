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
 *   - plural:   (optional) plural version of the string
 *   - count:    (optional) if we have plural we need to specify the count
 *   - tagN:     (optional) replace for sprintf() e.g. %s or %1$s
 *   - domain:   (optional) textdomain to be used (not required, the system will fill this out automatically
 *   - comment:  (optional) comment to the translator (this is not processed by this code)
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Examples
 * {gt text="Hello world"}
 * {gt text="Hello %s" tag1=$name}
 * {gt text="You want one cup" plural="You want two cups" count=2}
 * {gt text='Hello %1$s, welcome to %2$s' tag1=$city tag2=$country comment="%1 is a name %2 is the place"}
 * ## WARNING! When using %1$s in a template, smarty compiles this to PHP so the string must be in single quotes or
 * ## the $s will be evaluated as variable $s
 *
 *
 * String replacement follows the rules at http://php.net/sprintf but please note Smarty seems to pass
 * all variables as strings so %s and %n$s are mostly used.
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string Translation if it was available
 */
function smarty_function_gt($params, Zikula_View $view)
{
    if (isset($params['domain'])) {
        $domain = ('zikula' == strtolower($params['domain']) ? null : $params['domain']);
    } else {
        $domain = $view->getDomain(); // default domain
    }

    if (!isset($params['text'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_function_gt', 'text']));

        return false;
    }
    $text = $params['text'];

    // validate plural settings if applicable
    if ((!isset($params['count']) && isset($params['plural'])) || (isset($params['count']) && !isset($params['plural']))) {
        $view->trigger_error(__('Error! If you use a plural or count in gettext, you must use both parameters together.'));

        return false;
    }

    $count = (isset($params['count']) ? (int)$params['count'] : 0);
    $plural = (isset($params['plural']) ? $params['plural'] : false);

    // build array for tags (for %s, %1$s etc) if applicable
    ksort($params);
    $tags = [];
    foreach ($params as $key => $value) {
        if (preg_match('#^tag([0-9]{1,2})$#', $key)) {
            $tags[] = $value;
        }
    }
    $tags = (0 == count($tags) ? null : $tags);

    // perform gettext
    if ($plural) {
        $result = (isset($tags) ? _fn($text, $plural, $count, $tags, $domain) : _n($text, $plural, $count, $domain));
    } else {
        $result = (isset($tags) ? __f($text, $tags, $domain) : __($text, $domain));
    }

    // assign or return
    if (isset($params['assign'])) {
        $view->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
