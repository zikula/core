<?php

function smarty_modifier_zikularoutesmoduleToString($object)
{
    return "<pre>" . print_r($object, true) . "</pre>";
}
