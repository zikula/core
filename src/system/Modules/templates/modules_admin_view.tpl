{ajaxheader ui=true}
{include file="modules_admin_menu.tpl"}
{gt text="Extension database" assign=extdbtitle}
{assign value="<strong><a href=\"http://community.zikula.org/module-Extensions-view.htm\">`$extdbtitle`</a></strong>" var=extdblink}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large __alt="View"}</div>
    <h2>{gt text="Modules list"}</h2>
    <p class="z-informationmsg">{gt text='Note: Modules are software that extends the functionality of a site. There is a wide choice of add-on modules available from the %s.' tag1=$extdblink}</p>
    <div style="padding:1em 0;"><strong>[{pagerabc posvar="letter" forwardvars="module,type,func"}]</strong></div>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>
                    {assign var='currentCol' value='name'}
                    {gt text="Internal name" assign='currentStr'}
                    {if $sort eq $currentCol}
                    <a class="z-order-{$sortdir|lower}" href="{modurl modname="Modules" type="admin" func="view" sort=$currentCol sortdir=$sortdirReverse}">{$currentStr}</a>
                    {else}
                    <a class="z-order-unsorted" href="{modurl modname="Modules" type="admin" func="view" sort=$currentCol sortdir=$sortdir}">{$currentStr}</a>
                    {/if}
                </th>
                <th>
                    {assign var='currentCol' value='displayname'}
                    {gt text="Display name" assign='currentStr'}
                    {if $sort eq $currentCol}
                    <a class="z-order-{$sortdir|lower}" href="{modurl modname="Modules" type="admin" func="view" sort=$currentCol sortdir=$sortdirReverse}">{$currentStr}</a>
                    {else}
                    <a class="z-order-unsorted" href="{modurl modname="Modules" type="admin" func="view" sort=$currentCol sortdir=$sortdir}">{$currentStr}</a>
                    {/if}
                </th>
                <th>{gt text="Module URL"}</th>
                <th>{gt text="Description"}</th>
                <th>{gt text="Version"}</th>
                <th class="z-nowrap">
                    <form action="{modurl modname="Modules" type="admin" func="view"}" method="post" enctype="application/x-www-form-urlencoded">
                        <div>
                            <label for="modules_state">{gt text="State"}</label><br />
                            <select id="modules_state" name="state" onchange="submit()">
                                <option value="0">{gt text="All"}</option>
                                <option value="{const name="ModUtil::STATE_UNINITIALISED"}"{if $state eq 1} selected="selected"{/if}>{gt text="Not installed"}</option>
                                <option value="{const name="ModUtil::STATE_INACTIVE}"{if $state eq 2} selected="selected"{/if}>{gt text="Inactive"}</option>
                                <option value="{const name="ModUtil::STATE_ACTIVE}"{if $state eq 3} selected="selected"{/if}>{gt text="Active"}</option>
                                <option value="{const name="ModUtil::STATE_MISSING}"{if $state eq 4} selected="selected"{/if}>{gt text="Files missing"}</option>
                                <option value="{const name="ModUtil::STATE_UPGRADED}"{if $state eq 5} selected="selected"{/if}>{gt text="New version uploaded"}</option>
                                {if $multi}
                                <option value="{const name="ModUtil::STATE_NOTALLOWED}"{if $state eq 6} selected="selected"{/if}>{gt text="Not allowed"}</option>
                                {/if}
                                <option value="10"{if $state eq 10} selected="selected"{/if}>{gt text="Incompatible"}</option>
                                <option value="{const name="ModUtil::STATE_INVALID"}"{if $state eq -1} selected="selected"{/if}>{gt text="Invalid structure"}</option>
                            </select>
                        </div>
                    </form>
                </th>
                <th class="z-right">{gt text="Actions"}</th>
            </tr>
        </thead>
        <tbody>
            {section name=modules loop=$modules}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>
                    {if isset($modules[modules].modinfo.capabilities.admin) and $modules[modules].modinfo.state eq 3}
                    <a title="{gt text="Go to the module's administration panel"}" href="{modurl modname=$modules[modules].modinfo.url type=admin}">{$modules[modules].modinfo.name|safetext}</a>
                    {else}
                    {$modules[modules].modinfo.name|safetext}
                    {/if}
                </td>
                <td>{$modules[modules].modinfo.displayname|safetext|default:"&nbsp;"}</td>
                <td>{$modules[modules].modinfo.url|safetext}</td>
                <td>{$modules[modules].modinfo.description|safetext|default:"&nbsp;"}</td>
                <td>{$modules[modules].modinfo.version|safetext}</td>
                <td class="z-nowrap">
                    {img src=$modules[modules].statusimage modname=core set=icons/extrasmall alt=$modules[modules].status title=$modules[modules].status}&nbsp;{$modules[modules].status|safetext}
                    {if isset($modules[modules].modinfo.newversion)}
                    <br />({$modules[modules].modinfo.newversion|safetext})
                    {/if}
                </td>
                <td class="z-right z-nowrap">
                    {assign var="options" value=$modules[modules].options}
                    {strip}
                    {section name=options loop=$options}
                    <a href="{$options[options].url|safetext}">{img modname=core src=$options[options].image set=icons/extrasmall title=$options[options].title alt=$options[options].title class='tooltips'}</a>&nbsp;
                    {/section}
                    {/strip}
                </td>
            </tr>
            {sectionelse}
            <tr class="z-datatableempty"><td colspan="7">{gt text="No items found."}</td></tr>
            {/section}
        </tbody>
    </table>
    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
</div>

<script type="text/javascript">
    Zikula.UI.Tooltips($$('.tooltips'));
</script>
