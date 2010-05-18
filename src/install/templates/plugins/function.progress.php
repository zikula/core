<?php

/**
 * Display an HTML progressbar.
 *
 * @param array $params All attributes passed to this function from the template.
 * @param object &$smarty Reference to the Smarty object.
 *
 * @return string HTML code of the progressbar.
 */

function smarty_function_progress($params, &$smarty)
{
    if (!isset($params['percent'])) {
        $percent = 0;
    } else {
        $percent = $params['percent'];
    }

    $progress = '<div class="progressbarcontainer"><div class="progress"><span class="bar" style="width:$percent%">';
    $progress .= $percent;
    $progress .= '%</span></div></div>';

    return $progress;
}
