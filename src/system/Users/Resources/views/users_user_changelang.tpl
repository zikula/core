{gt text='Language switcher' assign='templatetitle'}
{include file='users_user_menu.tpl'}

<form id="changelang" class="z-form" action="{modurl modname='Users' type='user' func='main'}" method="post">
    <fieldset>
        <legend>{gt text="Change language"}</legend>
        <div class="z-formrow">
            <label for="user_changelang">{gt text="New language"}</label>
            <select id="user_changelang" name="setsessionlanguage">
                {foreach key='code' item='language' from=$languages}
                {if $code eq $usrlang}
                <option value="{$code}" selected="selected">{$language|safetext}</option>
                {else}
                <option value="{$code}">{$language|safetext}</option>
                {/if}
                {/foreach}
            </select>
        </div>
    </fieldset>
    <div class="z-formbuttons z-buttons">
        {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
        <a href="{modurl modname='Users' type='user' func='main'}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall  __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
    </div>
</form>
