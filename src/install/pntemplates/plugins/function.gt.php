<?php

function smarty_function_gt($params, &$smarty)
{
    if (isset($params['domain'])) {
        $domain = ($params['domain'] == strtolower('zikula') ? null : $params['domain']);
    } else {
        $domain = null; // default domain
    }

    if (!isset($params['text'])) {
        $smarty->trigger_error(__("Error! Gettext 'gt' requires an attribute text."));
        return false;
    }
    $text = $params['text'];

    // validate plural settings if applicable
    if (((!isset($params['count']) && isset($params['plural']))) || ((isset($params['count']) && !isset($params['plural'])))) {
        $smarty->trigger_error(__('Error! If you use a plural or count in gettext, you must use both parameters together.'));
        return false;
    }

    $count = (isset($params['count']) ? (int)$params['count'] : 0);
    $plural = (isset($params['plural']) ? $params['plural'] : false);

    // build array for tags (for %s, %1$s etc) if applicable
    ksort($params);
    $tags = array();
    foreach ($params as $key => $value) {
        if (preg_match('#^tag([0-9]{1,2})$#', $key)) {
            $tags[] = $value;
        }
    }
    $tags = (count($tags) == 0 ? null : $tags);

    // perform gettext
    if ($plural) {
        $result = (isset($tags) ? _fn($text, $plural, $count, $tags, $domain) : _n($text, $plural, $count, $domain));
    } else {
        $result = (isset($tags) ? __f($text, $tags, $domain) : __($text, $domain));
    }

    // assign or return
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
