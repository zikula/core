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
 * Smarty function to set the initial focus for a form.
 *
 * Usage:
 * <code>
 * {formsetinitialfocus inputId='PluginId'}
 * </code>
 * The "PluginId" refers to the plugin that should have focus initially.
 *
 * @param array            $params All attributes passed to this function from the template
 * @param Zikula_Form_View $view   Reference to Form render object
 *
 * @return string HTML to set the initial focus for a form
 */

function smarty_function_formsetinitialfocus($params, $view)
{
    if (!isset($params['inputId'])) {
        $view->trigger_error('initialFocus: inputId parameter required');

        return false;
    }

    $doSelect = (isset($params['doSelect']) ? $params['doSelect'] : false);
    $id = $params['inputId'];

    if ($doSelect) {
        $selectHtml = 'inp.select();';
    } else {
        $selectHtml = '';
    }

    // FIXME: part of PN???
    $html = "
<script type=\"text/javascript\">
var bodyElement = document.getElementsByTagName('body')[0];
var f = function() {
  var inp = document.getElementById('$id');
  if (inp != null) {
    inp.focus();
    $selectHtml
  }
};
var oldF = window.onload;
window.onload = function() { f(); if (oldF) oldF(); };
</script>";

    return $html;
}
