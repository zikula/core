{if $modvars.ZConfig.shorturls.multilingual}
{gt text='Language switcher' assign='templatetitle'}
{include file='User/menu.tpl'}

<form id="changelang" class="form-horizontal" role="form" action="{modurl modname='ZikulaUsersModule' type='user' func='index'}" method="post">
    <fieldset>
        <legend>{gt text="Change language"}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="user_changelang">{gt text="New language"}</label>
            <div class="col-lg-9">
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
        <div class="col-lg-offset-3 col-lg-9">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a class="btn btn-default" href="{modurl modname='ZikulaUsersModule' type='user' func='index'}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall  __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{else}
<div class="alert alert-danger">{gt text="Multi-lingual features are deactivated."}</div>
{/if}
