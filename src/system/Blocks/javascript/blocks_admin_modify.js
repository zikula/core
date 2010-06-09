// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Event.observe(window, 'load', blocks_modify_init, false);

function blocks_modify_init()
{
    Event.observe('blocks_advanced_placement_onclick', 'click', blocks_advanced_placement_onclick, false);
    $('block_placement_advanced').hide();
    $('blocks_advanced_placement_onclick').removeClassName('z-hide');
    $('blocks_advanced_placement_onclick').addClassName('z-show');
}

function blocks_advanced_placement_onclick()
{
    switchdisplaystate('block_placement_advanced');
}