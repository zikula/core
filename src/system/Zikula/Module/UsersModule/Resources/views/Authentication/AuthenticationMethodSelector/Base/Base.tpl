<form class="pull-left authentication_select_method" style="margin-right: 5px; margin-bottom: 5px;" id="authentication_select_method_form_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" method="post" action="{$form_action}" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" id="authentication_select_method_csrftoken_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="authentication_select_method_selector_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method_selector" value="1" />
        <input type="hidden" id="authentication_select_method_module_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method[modname]" value="{$authentication_method.modname}" />
        <input type="hidden" id="authentication_select_method_method_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method[method]" value="{$authentication_method.method}" />
        <button style="min-height: 67px;" type="submit" id="authentication_select_method_submit_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" class="btn {if $is_selected}btn-info{else}btn-default{/if} btn-sm authentication_select_method_button" name="submit">
            {if isset($icon) && !empty($icon)}
                {if !$isFontAwesomeIcon}
                    <img src="{$icon}" />
                {else}
                    <i class="fa {$icon} fa-3x"></i>
                {/if}
                <br />
            {/if}
            {$submit_text}
        </button>
    </div>
</form>