{gt text="Extensions database" assign=extdbtitle}
{assign value="<strong><a href=\"http://zikula.org/library/\">`$extdbtitle`</a></strong>" var=extdblink}

{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text='Modules list'}
</h3>

<p class="alert alert-info">{gt text='Note: Modules are software that extends the functionality of a site. There is a wide choice of add-on modules available from the %s.' tag1=$extdblink}</p>

{pagerabc posvar='letter' forwardvars='module,type,func' printempty=true route='zikulaextensionsmodule_admin_view'}

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{sortlink __linktext='Internal name' sort='name' currentsort=$sort sortdir=$sortdir route='zikulaextensionsmodule_admin_view'}</th>
            <th>{sortlink __linktext='Display name' sort='displayname' currentsort=$sort sortdir=$sortdir route='zikulaextensionsmodule_admin_view'}</th>
            <th>{gt text='Module URL'}</th>
            <th>{gt text='Description'}</th>
            <th>{gt text='Version'}</th>
            <th class="nowrap">
                <form action="{route name='zikulaextensionsmodule_admin_view'}" method="post" enctype="application/x-www-form-urlencoded">
                    <div>
                        <label for="modules_state">{gt text='State'}</label><br />
                        <select id="modules_state" name="state" onchange="submit()">
                            <option value="0">{gt text='All'}</option>
                            <option value="{const name='ModUtil::STATE_UNINITIALISED'}"{if $state eq 1} selected="selected"{/if}>{gt text='Not installed'}</option>
                            <option value="{const name='ModUtil::STATE_INACTIVE'}"{if $state eq 2} selected="selected"{/if}>{gt text='Inactive'}</option>
                            <option value="{const name='ModUtil::STATE_ACTIVE'}"{if $state eq 3} selected="selected"{/if}>{gt text='Active'}</option>
                            <option value="{const name='ModUtil::STATE_MISSING'}"{if $state eq 4} selected="selected"{/if}>{gt text='Files missing'}</option>
                            <option value="{const name='ModUtil::STATE_UPGRADED'}"{if $state eq 5} selected="selected"{/if}>{gt text='New version uploaded'}</option>
                            {if $multi}
                            <option value="{const name='ModUtil::STATE_NOTALLOWED'}"{if $state eq 6} selected="selected"{/if}>{gt text='Not allowed'}</option>
                            {/if}
                            <option value="10"{if $state eq 10} selected="selected"{/if}>{gt text='Incompatible'}</option>
                            <option value="{const name='ModUtil::STATE_INVALID'}"{if $state eq -1} selected="selected"{/if}>{gt text='Invalid structure'}</option>
                        </select>
                    </div>
                </form>
            </th>
            <th class="text-right">{gt text='Actions'}</th>
        </tr>
    </thead>
    <tbody>
        {section name='modules' loop=$modules}
        <tr>
            <td>
                {if isset($modules[modules].modinfo.capabilities.admin) and $modules[modules].modinfo.state eq 3}
                    {if isset($modules[modules].modinfo.capabilities.admin.url)}
                        {assign value=$modules[modules].modinfo.capabilities.admin.url var='url'}
                    {elseif isset($modules[modules].modinfo.capabilities.admin.route)}
                        {route name=$modules[modules].modinfo.capabilities.admin.route assign='url'}
                    {/if}
                <a title="{gt text="Go to the module's administration panel"}" href="{$url}">{$modules[modules].modinfo.name|safetext}</a>
                {else}
                {$modules[modules].modinfo.name|safetext}
                {/if}
            </td>
            <td>{$modules[modules].modinfo.displayname|safetext|default:"&nbsp;"}</td>
            <td>{$modules[modules].modinfo.url|safetext}</td>
            <td>{$modules[modules].modinfo.description|safetext|default:"&nbsp;"}</td>
            <td>{$modules[modules].modinfo.version|safetext}</td>
            <td class="nowrap">                
                <span class="label label-{$modules[modules].statusclass|safetext}">
                    {$modules[modules].status|safetext}
                </span>
                {if isset($modules[modules].modinfo.newversion)}
                <br />({$modules[modules].modinfo.newversion|safetext})
                {/if}
            </td>
            <td class="actions">
                {assign var='options' value=$modules[modules].options}
                {strip}
                {section name='options' loop=$options}
                <a href="{$options[options].url|safetext}" class="fa fa-{$options[options].image} tooltips" style="color:{$options[options].color}" title="{$options[options].title}"></a>&nbsp;
                {/section}
                {/strip}
            </td>
        </tr>
        {sectionelse}
        <tr><td colspan="7">{gt text='No items found.'}</td></tr>
        {/section}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulaextensionsmodule_admin_view'}
{adminfooter}
