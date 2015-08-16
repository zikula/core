{if $modvars.ZConfig.multilingual }
{gt text='Language switcher' assign='templatetitle'}
{include file='User/menu.tpl'}

<form id="changelang" class="form-horizontal" role="form" action="{route name='zikulausersmodule_user_index'}" method="post">
    <fieldset>
        <legend>{gt text="Change language"}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="user_changelang">{gt text="New language"}</label>
            <div class="col-sm-9">
                <select id="user_changelang" name="setsessionlanguage" class="form-control">
                    {foreach key='code' item='language' from=$languages}
                    {if $code eq $usrlang}
                    <option value="{$code}" selected="selected">{$language|safetext}</option>
                    {else}
                    <option value="{$code}">{$language|safetext}</option>
                    {/if}
                    {/foreach}
                </select>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button class="btn btn-success" title="{gt text="Save"}">
                {gt text="Save"}
            </button>
            <a class="btn btn-danger" href="{route name='zikulausersmodule_user_index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>
{else}
<div class="alert alert-danger">{gt text="Multi-lingual features are deactivated."}</div>
{/if}
