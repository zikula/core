{gt text="Extensions database" assign=extdbtitle}
{assign value="<strong><a href=\"http://go.zikula.org/inappstore\">`$extdbtitle`</a></strong>" var=extdblink}
{assign var='popups' value=false}

{adminheader}
<h3>
    <span class="icon-list"></span>
    {gt text="Modules list"}
</h3>

<p class="alert alert-info">{gt text='Note: Modules are software that extends the functionality of a site. There is a wide choice of add-on modules available from the %s.' tag1=$extdblink}</p>

<ul data-role="listview" data-inset="true" data-filter="true" data-filter-placeholder="{gt text='Search modules'}" data-autodividers="true">
    {section name=modules loop=$modules}
        <li data-icon="gear" data-filtertext="{$modules[modules].modinfo.name|safetext} {$modules[modules].modinfo.displayname|safetext}">
            {if isset($modules[modules].modinfo.capabilities.admin) and $modules[modules].modinfo.state eq 3}
                <a title="{gt text="Go to the module's administration panel"}" href="{modurl modname=$modules[modules].modinfo.url type=admin func=index}">
            {else}
                <a href="#">
            {/if}
                {$modules[modules].modinfo.name|safetext}
                <span class="label label-{$modules[modules].statusclass|safetext}">
                    {$modules[modules].status|safetext}
                </span>
                {if isset($modules[modules].modinfo.newversion)}
                    ({$modules[modules].modinfo.newversion|safetext})
                {/if}
            </a>
            <span class="ui-li-count">{$modules[modules].modinfo.version|safetext}</span>
            <a href="#zikulaextensionsmodule-{$modules[modules].modinfo.name}-popup" data-rel="popup" data-position-to="window" data-transition="pop">Actions</a>
            {capture assign='popups'}
                {$popups}
                <div data-role="popup" id="zikulaextensionsmodule-{$modules[modules].modinfo.name}-popup">
                    <ul data-role="listview">
                        <li data-role="list-divider">{$modules[modules].modinfo.name}</li>
                        {assign var="options" value=$modules[modules].options}
                        {strip}
                            {section name=options loop=$options}
                                <li>
                                    <a href="{$options[options].url|safetext}" style="color:{$options[options].color}" title="{$options[options].title}"><i class="icon-{$options[options].image} tooltips"></i>{$options[options].title}</a>
                                </li>
                            {/section}
                        {/strip}
                    </ul>
                </div>
            {/capture}
        </li>
    {/section}
</ul>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}

{$popups}