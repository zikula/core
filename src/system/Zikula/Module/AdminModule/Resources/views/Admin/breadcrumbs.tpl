<ol class="breadcrumb">
    <li>{gt text='You are in:'} <a href="{route name='zikulaadminmodule_admin_adminpanel'}">{gt text='Administration'}</a></li>

    {if $func neq 'adminpanel'}
        <li><a href="{route name='zikulaadminmodule_admin_adminpanel' acid=$currentcat}">{$menuoptions.$currentcat.title|safetext}</a></li>
    {else}
        <li>{$menuoptions.$currentcat.title|safetext}</li>
    {/if}

    {if $func ne 'adminpanel'}
        {foreach item='moditem' from=$menuoptions.$currentcat.items}
            {if $toplevelmodule eq $moditem.modname}
                <li><a href="{modurl modname=$toplevelmodule type='admin' func='index'}">{$moditem.menutext|safetext}</a></li>
                {break}
            {/if}
        {/foreach}

        {if $func neq 'index'}
            <li class="active z-admin-pagefunc">{$func|safetext}</li>
        {/if}
    {/if}
</ol>
