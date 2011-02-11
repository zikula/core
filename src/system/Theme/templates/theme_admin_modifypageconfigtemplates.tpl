{include file='theme_admin_menu.tpl'}
{gt text="Edit page configuration" assign=templatetitle}
<div class="z-admincontainer">
    {include file="theme_admin_modifymenu.tpl"}
    <div class="z-adminpageicon">{img modname=core src=xedit.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle} - {$filename|safetext}</h2>
    <form class="z-form" action="{modurl modname="Theme" type="admin" func="updatepageconfigtemplates"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Theme"}" />
            <input type="hidden" name="themename" value="{$themename|safetext}" />
            <input type="hidden" name="filename" value="{$filename|safetext}" />
            <fieldset>
                <legend>{gt text="Page settings"}</legend>
                <div class="z-formrow">
                    <label for="theme_pagetemplate">{gt text="Page template"}</label>
                    <select id="theme_pagetemplate" name="pagetemplate">
                        <option value="">&nbsp;</option>
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
                <div class="z-formrow">
                    <label for="theme_modulewrapper">{gt text="Display module wrapper divisions"}</label>
                    <input id="theme_modulewrapper" type="checkbox" name="modulewrapper" value="1" {if $pageconfiguration.modulewrapper} checked="checked"{/if} />
                </div>
                <div class="z-formrow">
                    <label for="theme_blockwrapper">{gt text="Display block wrapper divisions"}</label>
                    <input id="theme_blockwrapper" type="checkbox" name="blockwrapper" value="1" {if $pageconfiguration.blockwrapper} checked="checked"{/if} />
                </div>
            </fieldset>
            <fieldset>
                <legend>{gt text="Filter settings"}</legend>
                <p class="z-informationmsg">{gt text='Comma separated list of filters, eg. myfilter (for outputfilters) represents outputfilter.myfilter.php file, see <a href="http://www.smarty.net/manual/en/advanced.features.php">the Smarty documentation</a> for more information about filters.'}</p>
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
            <fieldset>
                <legend>{gt text="Block position templates"}</legend>
                {foreach from=$blockpositions item=position}
                <div class="z-formrow">
                    <label for="theme_blocktemplate_{$position|safetext}">{$position}</label>
                    <select id="theme_blocktemplate_{$position|safetext}" name="blockpositiontemplates[{$position|safetext}]">
                        <option value="">{gt text="Default template"}</option>
                        {html_options values=$blocktemplates output=$blocktemplates selected=$pageconfiguration.blockpositions.$position}
                    </select>
                </div>
                {/foreach}
                {capture assign=undefinedblockpositions}
                {strip}
                <ul>
                    {assign var=undefinedblockposition value=false}
                    {foreach name=blockpositions from=$pageconfiguration.blockpositions key=position item=template}
                    {if $blockpositions.$position eq false}
                    {assign var=undefinedblockposition value=true}
                    <li{if $smarty.foreach.blockpositions.last} class="last"{/if}><a href="{modurl modname="Blocks" type="admin" func="newposition"}">{$position}</a></li>
                    {/if}
                    {/foreach}
                </ul>
                {/strip}
                {/capture}
                {if $undefinedblockposition eq true}
                <div class="z-warningmsg" id="theme_undefinedblockpositions"><p>{gt text="The following block positions are used in this page configuration, but they have not been defined within the Blocks module;</p>%s<p>Click on a block position to go create that position." tag1=$undefinedblockpositions}</p></div>
                {/if}
            </fieldset>
            <fieldset>
                <legend>{gt text="Block type templates"}</legend>
                {foreach from=$allblocks item=moduleblocks}
                {foreach from=$moduleblocks item=block}
                <div class="z-formrow">
                    <label for="theme_blocktemplate_{$block.bkey|safetext}">{$block.module|safetext}/{$block.text_type_long|safetext}</label>
                    {assign var=bkey value=$block.bkey}
                    <select id="theme_blocktemplate_{$block.bkey|safetext}" name="blocktypetemplates[{$block.bkey|safetext}]">
                        <option value="">{gt text="Default template"}</option>
                        {html_options values=$blocktemplates output=$blocktemplates selected=$pageconfiguration.blocktypes.$bkey}
                    </select>
                </div>
                {/foreach}
                {/foreach}
            </fieldset>
            <fieldset>
                <legend>{gt text="Block instance templates"}</legend>
                {foreach from=$blocks item=block}
                <div class="z-formrow">
                    <label for="theme_blocktemplate_{$block.bid|safetext}">
                        {if $block.title neq ''}
                        {$block.title|safetext}
                        {else}
                        {gt text='Untitled block of type %1$s, ID %2$s' tag1=$block.bkey tag2=$block.bid"}
                        {/if}
                    </label>
                    {assign var=bid value=$block.bid}
                    <select id="theme_blocktemplate_{$block.bid|safetext}" name="blockinstancetemplates[{$block.bid|safetext}]">
                        <option value="">{gt text="Default template"}</option>
                        {html_options values=$blocktemplates output=$blocktemplates selected=$pageconfiguration.blockinstances.$bid}
                    </select>
                </div>
                {/foreach}
            </fieldset>
            <div class="z-buttons z-formbuttons">
                {button src=button_ok.gif set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Theme type=admin func=pageconfigurations themename=$themename}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </div>
    </form>
</div>
