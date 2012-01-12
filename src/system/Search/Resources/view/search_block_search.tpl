<form class="z-form z-linear" method="post" action="{modurl modname="Search" type="user" func="search"}">
    <div>
        <fieldset>
            <legend>{gt text="Search keyword"}</legend>
            <div class="z-formrow">
                <input id="block_search_q" type="text" name="q" size="20" maxlength="255" />
            </div>
            {if $vars.displaySearchBtn eq 1}
            <div class="z-buttons">
                <input class="z-bt-ok z-bt-small" type="submit" value="{gt text="Search now" domain='zikula'}" />
            </div>
            {/if}
            {if isset($vars.active) && is_array($vars.active)}
            {foreach item="dummy" key="actives" from=$vars.active}
            <input type="hidden" name="active[{$actives|safetext}]" value="1" />
            {/foreach}
            {/if}
            
            {searchvartofieldnames data=$modvars.Search prefix="modvar" assign="modvariables"}
            {foreach item="value" key="name" from=$modvariables}
            <input type="hidden" name="{$name|safetext}" value="{$value|safetext}" />
            {/foreach}
        </fieldset>
    </div>
</form>
