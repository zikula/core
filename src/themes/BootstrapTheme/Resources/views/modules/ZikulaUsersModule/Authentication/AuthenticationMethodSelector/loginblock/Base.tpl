{assign var='idPrefix' value='zikulausersmodule-authentication-select-method-loginblock'}
<form class="authentication_select_method navbar-form" style="" id="{$idPrefix}-form_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" method="post" action="{$form_action}" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" id="{$idPrefix}_csrftoken_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="{$idPrefix}_selector_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method_selector" value="1" />
        <input type="hidden" id="{$idPrefix}_module_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method[modname]" value="{$authentication_method.modname}" />
        <input type="hidden" id="{$idPrefix}_method_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method[method]" value="{$authentication_method.method}" />
        <button style="text-align: left;" type="submit" id="{$idPrefix}_submit_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" class="btn btn-default btn-xs btn-block authentication_select_method_button" name="submit">
            {if isset($icon) && !empty($icon)}
                {if !$isFontAwesomeIcon}
                    <img src="{$icon}" height="12" alt="" />
                {else}
                    <i class="fa {$icon} fa-fw"></i>
                {/if}
            {/if}
            {$submit_text}
        </button>
    </div>
</form>
