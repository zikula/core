// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Event.observe(window, 'load', blocks_modify_init);

function blocks_modify_init()
{
    $('blocks_advanced_placement_onclick').observe('click', blocks_advanced_placement_onclick);
    $('blocks_advanced_placement_onclick').removeClassName('z-hide');
    $('blocks_advanced_placement_onclick').addClassName('z-show');

    if (total_existing_filters == 0)
    {
        $('block_placement_advanced').hide();
    }
}

function blocks_advanced_placement_onclick()
{
    Zikula.switchdisplaystate('block_placement_advanced');
}
