{pageaddvar name="javascript" value="system/Search/javascript/search_user_form.js"}
{gt text='Site search' assign='templatetitle' domain='zikula'}
{include file='search_user_menu.tpl'}

<form class="z-form" id="search_form" method="post" action="{modurl modname='Search' type='user' func='search'}">
    <fieldset>
        <div class="z-formrow">
            <label for="search_q" id="search_q_label">{gt text='Search keywords' domain='zikula'}</label>
            <input type="text" id="search_q" name="q" size="20" maxlength="255" value="{$q|safetext}" />
        </div>
        <div class="z-formrow">
            <label for="searchtype">{gt text='Keyword settings' domain='zikula'}</label>
            <select name="searchtype" id="searchtype" size="1">
                <option value="AND"{if $searchtype eq 'AND'} selected="selected"{/if}>{gt text='All words' domain='zikula'}</option>
                <option value="OR"{if $searchtype eq 'OR'}selected="selected""{/if}>{gt text='Any words' domain='zikula'}</option>
                <option value="EXACT"{if $searchtype eq 'EXACT'}selected="selected""{/if}>{gt text='Exact phrase' domain='zikula'}</option>
            </select>
        </div>
        <div class="z-formrow">
            <label for="searchorder">{gt text='Order of results' domain='zikula'}</label>
            <select name="searchorder" id="searchorder" size="1">
                <option value="newest"{if $searchtype eq 'newest'} selected="selected"{/if}>{gt text="Newest first" domain='zikula'}</option>
                <option value="oldest"{if $searchtype eq 'oldest'} selected="selected"{/if}>{gt text="Oldest first" domain='zikula'}</option>
                <option value="alphabetical"{if $searchtype eq 'alphabetical'} selected="selected"{/if}>{gt text="Alphabetical" domain='zikula'}</option>
            </select>
        </div>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Search now" __title="Search now" __text="Search now"}
        </div>
    </fieldset>
    {if $plugin_options}
    <fieldset class="z-linear">
        <legend>{gt text='Filter search by type' domain='zikula'}</legend>
        <div class="search_toogle">
            <input type="checkbox" name="togglebox" id="togglebox" checked="checked" tabindex="0" onclick="toggleboxes(this);" />
            <label for="togglebox">{gt text='De/Select all' domain='zikula'}</label>
        </div>
        {foreach from=$plugin_options key='plugin' item='plugin_option'}
        {$plugin_option}
        {/foreach}
    </fieldset>
    {/if}
</form>
