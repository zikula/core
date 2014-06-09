{pageaddvar name="javascript" value="system/Zikula/Module/SearchModule/Resources/public/js/ZikulaSearchModule.User.Form.js"}
{gt text='Site search' assign='templatetitle' domain='zikula'}
{include file='User/menu.tpl'}

<form class="form-horizontal" role="form" id="search_form" method="post" action="{modurl modname='ZikulaSearchModule' type='user' func='search'}">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="search_q" id="search_q_label">{gt text='Search keywords' domain='zikula'}</label>
            <div class="col-lg-9">
                <input type="search" id="search_q" class="form-control" name="q" size="20" maxlength="255" results="10" autosave="Search" value="{$q|safetext}" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="searchtype">{gt text='Keyword settings' domain='zikula'}</label>
            <div class="col-lg-9">
                <select class="form-control" name="searchtype" id="searchtype" size="1">
                    <option value="AND"{if $searchtype eq 'AND'} selected="selected"{/if}>{gt text='All words' domain='zikula'}</option>
                    <option value="OR"{if $searchtype eq 'OR'}selected="selected""{/if}>{gt text='Any words' domain='zikula'}</option>
                    <option value="EXACT"{if $searchtype eq 'EXACT'}selected="selected""{/if}>{gt text='Exact phrase' domain='zikula'}</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="searchorder">{gt text='Order of results' domain='zikula'}</label>
            <div class="col-lg-9">
                <select class="form-control" name="searchorder" id="searchorder" size="1">
                    <option value="newest"{if $searchtype eq 'newest'} selected="selected"{/if}>{gt text="Newest first" domain='zikula'}</option>
                    <option value="oldest"{if $searchtype eq 'oldest'} selected="selected"{/if}>{gt text="Oldest first" domain='zikula'}</option>
                    <option value="alphabetical"{if $searchtype eq 'alphabetical'} selected="selected"{/if}>{gt text="Alphabetical" domain='zikula'}</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Search now"}">
                    {gt text="Search now"}
                </button>
            </div>
        </div>
    </fieldset>
    {if $plugin_options}
    <fieldset>
        <legend>{gt text='Filter search by type' domain='zikula'}</legend>
        <div class="search_toogle">
            <input type="checkbox" name="togglebox" id="togglebox" checked="checked" tabindex="0" />
            <label for="togglebox">{gt text='De/Select all' domain='zikula'}</label>
        </div>
        {foreach from=$plugin_options key='plugin' item='plugin_option'}
        {$plugin_option}
        {/foreach}
    </fieldset>
    {/if}
</form>
