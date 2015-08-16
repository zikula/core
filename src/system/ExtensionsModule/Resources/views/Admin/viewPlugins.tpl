{adminheader}
<h3>
    <span class="fa fa-table"></span>
    {if $systemplugins}
    {gt text='System plugins'}
    {else}
    {gt text='Plugins list'}
    {/if}
</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            {if !$systemplugins}
            <th>
                <form action="{route name='zikulaextensionsmodule_admin_viewplugins'}" method="post" enctype="application/x-www-form-urlencoded">
                    <div>
                        <input type="hidden" name="sort" value="{$sort|safetext}" />
                        <input type="hidden" name="state" value="{$state|safetext}" />
                        <input type="hidden" name="systemplugins" value="{$systemplugins|safetext}" />
                        <div>
                            <label for="bymodule">
                                <a href="{route name='zikulaextensionsmodule_admin_viewplugins' sort='module' state=$state bymodule=$module systemplugins=$systemplugins}">{gt text='Module'}</a>
                            </label><br />
                            {selector_module name='bymodule' selectedValue=$module allValue='0' __allText='All' submit=true}
                        </div>
                    </div>
                </form>
            </th>
            {/if}

            <th><a class="{if empty($sort) || $sort eq 'module'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulaextensionsmodule_admin_viewplugins' sort='module' state=$state bymodule=$module systemplugins=$systemplugins}">{gt text='Internal Plugin name'}</a></th>
            <th><a class="{if $sort eq 'name'}z-order-asc{else}z-order-unsorted{/if}" href="{route name='zikulaextensionsmodule_admin_viewplugins' sort='name' state=$state bymodule=$module systemplugins=$systemplugins}">{gt text='Plugin display name'}</a></th>
            <th>{gt text='Description'}</th>
            <th>{gt text='Version'}</th>
            <th class="nowrap">
                <form action="{route name='zikulaextensionsmodule_admin_viewplugins'}" method="post" enctype="application/x-www-form-urlencoded">
                    <div>
                        <input type="hidden" name="sort" value="{$sort|safetext}" />
                        <input type="hidden" name="bymodule" value="{$module|safetext}" />
                        <input type="hidden" name="systemplugins" value="{$systemplugins|safetext}" />
                        <div>
                            <label for="modules_state">{gt text='State'}</label><br />
                            <select id="modules_state" name="state" onchange="submit()">
                                <option value="-1">{gt text='All'}</option>
                                <option value="{const name='PluginUtil::NOTINSTALLED'}"{if $state eq 2} selected="selected"{/if}>{gt text='Not installed'}</option>
                                <option value="{const name='PluginUtil::DISABLED'}"{if $state eq 0} selected="selected"{/if}>{gt text='Inactive'}</option>
                                <option value="{const name='PluginUtil::ENABLED'}"{if $state eq 1} selected="selected"{/if}>{gt text='Active'}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </th>
            <th class="text-right">{gt text='Actions'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$plugins item='plugin'}
        <tr>
            {if !$systemplugins}
            <td>{$plugin.instance->getModuleName()|safetext}</td>
            {/if}
            <td>
                {if $plugin.instance instanceof Zikula_Plugin_ConfigurableInterface and $plugin.instance->isInstalled() and $_type eq 'system'}
                <a href="{route name='zikulaextensionsmodule_adminplugin_dispatch' _plugin=$plugin.instance->getPluginName() _action='configure'}">{$plugin.instance->getPluginName()|safetext}</a>
                {elseif $plugin.instance instanceof Zikula_Plugin_ConfigurableInterface and $plugin.instance->isInstalled() and $_type eq 'module'}
                <a href="{route name='zikulaextensionsmodule_adminplugin_dispatch' _module=$plugin.instance->getModuleName() _plugin=$plugin.instance->getPluginName() _action="configure"}">{$plugin.instance->getPluginName()|safetext}</a>
                {else}
                {$plugin.instance->getPluginName()|safetext}
                {/if}
            </td>
            <td>{$plugin.instance->getMetaDisplayName()|safetext}</td>
            <td>{$plugin.instance->getMetaDescription()|safetext}</td>
            <td>{$plugin.version|safetext}</td>
            <td class="nowrap">
                <span class="label label-{$plugin.statusclass}">
                    {$plugin.status|safetext}
                </span>
                {if isset($plugin.newversion)}
                <br />({$plugin.newversion|safetext})
                {/if}
            </td>
            <td class="actions text-right nowrap">
                {strip}
                {foreach from=$plugin.actions item='action'}
                <a href="{$action.url|safetext}" class="fa fa-{$action.image} tooltips" style="color:{$action.color|default:'#000'}" title="{$action.title}"></a>&nbsp;
                {/foreach}
                {/strip}
            </td>
        </tr>
        {foreachelse}
        <tr><td colspan="7">{gt text='No items found.'}</td></tr>
        {/foreach}
    </tbody>
</table>
{adminfooter}
