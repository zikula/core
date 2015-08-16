<h3>
    <span class="fa fa-pencil"></span>
    {gt text="Edit theme"} {$themeinfo.displayname}
</h3>
{if $themeinfo.type eq 3}
<ul class="nav nav-tabs nav-tabs-admin">
    <li {if $func eq 'modify'}class="active"{/if}>
        <a href="{route name='zikulathememodule_admin_modify' themename=$themename}">{gt text="Settings"}</a>
    </li>
    <li {if $func eq 'pageconfigurations' or $func eq 'modifypageconfigurationassignment' or $func eq 'modifypageconfigtemplates'}class="active"{/if}>
        <a href="{route name='zikulathememodule_admin_pageconfigurations' themename=$themename}">{gt text="Page configurations"}</a>
    </li>
    <li {if $func eq 'palettes'}class="active"{/if}>
        <a href="{route name='zikulathememodule_admin_palettes' themename=$themename}">{gt text="Colour palettes"}</a>
    </li>
    <li {if $func eq 'variables'}class="active"{/if}>
        <a href="{route name='zikulathememodule_admin_variables' themename=$themename}">{gt text="Variables"}</a>
    </li>
</ul>
{/if}