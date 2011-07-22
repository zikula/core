{pageaddvar name='javascript' value='zikula.ui'}
{adminheader}
{include file="theme_admin_modifymenu.tpl"}

<h4>{gt text="Edit page configuration"} - {$filename|safetext}</h4>

<form class="z-form" action="{modurl modname="Theme" type="admin" func="updatepageconfigtemplates"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <input type="hidden" name="filename" value="{$filename|safetext}" />

        <fieldset>
            <legend>{gt text="Page settings"}</legend>

            <div class="z-formrow">
                <label for="theme_pagetemplate">{gt text="Page template"}</label>
                <select id="theme_pagetemplate" name="pagetemplate">
                    {html_options values=$moduletemplates output=$moduletemplates selected=$pageconfiguration.page}
                </select>
            </div>
            <div class="z-formrow">
                <label for="theme_blocktemplate">{gt text="Block template"}</label>
                <select id="theme_blocktemplate" name="blocktemplate">
                    <option value="">{gt text="Default template"}</option>
                    {html_options values=$blocktemplates output=$blocktemplates selected=$pageconfiguration.block}
                </select>
            </div>
            <div class="z-formrow">
                <label for="theme_pagepalette">{gt text="Palette"}</label>
                <select id="theme_pagepalette" name="pagepalette">
                    <option value="">&nbsp;</option>
                    {html_options values=$palettes output=$palettes selected=$pageconfiguration.palette}
                </select>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Wrappers settings"}</legend>

            <div class="z-formrow">
                <label for="theme_modulewrapper">{gt text="Enable main content wrapper"}</label>
                <div id="theme_modulewrapper">
                    <input id="theme_modulewrapper_yes" type="radio" name="modulewrapper" value="1" {if $pageconfiguration.modulewrapper eq 1}checked="checked"{/if} />
                    <label for="theme_modulewrapper_yes">{gt text="Yes"}</label>
                    <input id="theme_modulewrapper_no" type="radio" name="modulewrapper" value="0" {if $pageconfiguration.modulewrapper eq 0}checked="checked"{/if} />
                    <label for="theme_modulewrapper_no">{gt text="No"}</label>
                </div>
                <div class="z-informationmsg z-formnote">
                    {gt text='This will add a wrapper for the main content, like:'}
                    <br />&lt;div id="z-maincontent" class="z-module-$module"&gt;$maincontent&lt;/div&gt;
                </div>
            </div>
            <div class="z-formrow">
                <label for="theme_blockwrapper">{gt text="Enable block wrappers"}</label>
                <div id="theme_blockwrapper">
                    <input id="theme_blockwrapper_yes" type="radio" name="blockwrapper" value="1" {if $pageconfiguration.blockwrapper eq 1}checked="checked"{/if} />
                    <label for="theme_blockwrapper_yes">{gt text="Yes"}</label>
                    <input id="theme_blockwrapper_no" type="radio" name="blockwrapper" value="0" {if $pageconfiguration.blockwrapper eq 0}checked="checked"{/if} />
                    <label for="theme_blockwrapper_no">{gt text="No"}</label>
                </div>
                <div class="z-informationmsg z-formnote">
                    {gt text='This will add a wrapper for each block content, like:'}
                    <br />&lt;div class="z-block z-blockposition-$position z-bkey-$bkey z-bid-$bid"&gt;$blockcontent&lt;/div&gt;
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Filter settings"}</legend>

            <p class="z-informationmsg z-formnote">{gt text='Comma separated list of filters, eg. myfilter (for outputfilters) represents outputfilter.myfilter.php file, see <a href="http://www.smarty.net/manual/en/advanced.features.php">the documentation</a> for more information about filters.'}</p>
            <div class="z-formrow">
                <label for="theme_filters_outputfilters">{gt text="Output filters"}</label>
                <input type="text" id="theme_filters_outputfilters" name="filters[outputfilters]" size="40" maxlength="255" value="{$pageconfiguration.filters.outputfilters|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="theme_filters_prefilters">{gt text="Pre-filters"}</label>
                <input type="text" id="theme_filters_prefilters" name="filters[prefilters]" size="40" maxlength="255" value="{$pageconfiguration.filters.prefilters|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="theme_filters_postfilters">{gt text="Post-filters"}</label>
                <input type="text" id="theme_filters_postfilters" name="filters[postfilters]" size="40" maxlength="255" value="{$pageconfiguration.filters.postfilters|safetext}" />
            </div>
        </fieldset>

        <div class="z-informationmsg">{gt text='The theme engine will consider the more specific setup; in that order, a block instance template, is used over a block type template, and a block position one.'}</div>

        <ul id="blocktemplates">
            <li class="tab"><a href="#blockinstancestab">{gt text="Block instance templates"}</a></li>
            <li class="tab"><a href="#blocktypestab">{gt text="Block type templates"}</a></li>
            <li class="tab"><a href="#blockpositionstab">{gt text="Block position templates"}</a></li>
        </ul>

        <div id="blockpositionstab">
            <fieldset>
                <legend>{gt text="Existing block positions"}</legend>

                {foreach from=$blockpositions key='position' item='description'}
                <div class="z-formrow">
                    <label for="theme_blockpositiontemplate_{$position|safetext}" title="{$description|safetext}">{$position}</label>
                    <select id="theme_blockpositiontemplate_{$position|safetext}" name="blockpositiontemplates[{$position|safetext}]">
                        <option value="">{gt text="Default template"}</option>
                        {html_options values=$blocktemplates output=$blocktemplates selected=$pageconfiguration.blockpositions.$position}
                    </select>
                </div>
                {/foreach}
                {capture assign='undefinedblockpositions'}
                {strip}
                <ul>
                    {assign var='undefinedblockposition' value=false}
                    {foreach name='blockpositions' from=$pageconfiguration.blockpositions key='position' item='template'}
                    {if !isset($blockpositions.$position)}
                    {assign var='undefinedblockposition' value=true}
                    <li><a href="{modurl modname="Blocks" type="admin" func="newposition" name=$position|safetext}">{$position|safetext}</a></li>
                    {/if}
                    {/foreach}
                </ul>
                {/strip}
                {/capture}
                {if $undefinedblockposition eq true}
                <div class="z-warningmsg z-formnote" id="theme_undefinedblockpositions">{gt text="<p>The following block positions are used in this page configuration, but they have not been defined within the Blocks module;</p>%s<p>Click on a block position to go create that position.</p>" tag1=$undefinedblockpositions}</div>
                {/if}
            </fieldset>
        </div>

        <div id="blocktypestab">
            {foreach from=$allblocks item='moduleblocks'}
            {foreach from=$moduleblocks item='block' name='modblocks'}
            {if $smarty.foreach.modblocks.first}
            <fieldset>
                <legend>{$block.module|safetext}</legend>
                {/if}
                <div class="z-formrow">
                    <label for="theme_blocktypetemplate_{$block.module|safetext}_{$block.bkey|safetext}" title="{$block.text_type_long|safetext}">{$block.text_type|safetext}</label>
                    {assign var=bkey value=$block.bkey}
                    <select id="theme_blocktypetemplate_{$block.module|safetext}_{$block.bkey|safetext}" name="blocktypetemplates[{$block.bkey|safetext}]">
                        <option value="">{gt text="Default template"}</option>
                        {html_options values=$blocktemplates output=$blocktemplates selected=$pageconfiguration.blocktypes.$bkey}
                    </select>
                </div>
                {if $smarty.foreach.modblocks.last}
            </fieldset>
            {/if}
            {/foreach}
            {/foreach}
        </div>

        <div id="blockinstancestab">
            <fieldset>
                <legend>{gt text="Existing block instances"}</legend>

                {foreach from=$blocks item='block'}
                <div class="z-formrow">
                    <label for="theme_blockinstancetemplate_{$block.bid|safetext}" title="{$block.description|safetext}">
                        {if $block.title neq ''}
                        {$block.title|safetext}
                        {else}
                        {gt text='Untitled block of type %1$s' tag1=$block.bkey}
                        {/if}
                        <span class="z-sub">({$block.bid})</span>
                    </label>
                    <select id="theme_blockinstancetemplate_{$block.bid|safetext}" name="blockinstancetemplates[{$block.bid|safetext}]">
                        <option value="">{gt text="Default template"}</option>
                        {html_options values=$blocktemplates output=$blocktemplates selected=$pageconfiguration.blockinstances[$block.bid]}
                    </select>
                </div>
                {/foreach}
            </fieldset>
        </div>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Theme type=admin func=pageconfigurations themename=$themename}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}

<script type="text/javascript">
    document.observe('dom:loaded', function() {
        Zikula.UI.Tooltips($$('label'));
        tabstest = new Zikula.UI.Tabs('blocktemplates');
    });
</script>
