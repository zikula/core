<?php

function smarty_function_progress($params, &$smarty)
{
    if (!isset($params['percent'])) {
        $percent = 0;
    }else{
        $percent = $params['percent'];
    }

    $progress = '<div class="progressbarcontainer"><div class="progress"><span class="bar" style="width:$percent%">';
    $progress .= $percent;
    $progress .= '%</span></div></div>';

    return $progress;
}
