// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Event.observe(window, 'load', blocks_modify_init);

function blocks_modify_init()
{
    $('blocks_advanced_placement_onclick').observe('click', blocks_advanced_placement_onclick);
    $('block_placement_advanced').hide();
    $('blocks_advanced_placement_onclick').removeClassName('z-hide');
    $('blocks_advanced_placement_onclick').addClassName('z-show');
}

function blocks_advanced_placement_onclick()
{
    switchdisplaystate('block_placement_advanced');
}
