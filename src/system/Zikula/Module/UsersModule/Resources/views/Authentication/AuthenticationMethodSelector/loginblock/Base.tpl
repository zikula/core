<form class="authentication_select_method" style="margin-bottom: 5px;" id="authentication_select_method_form_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" method="post" action="{$form_action}" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" id="authentication_select_method_csrftoken_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="authentication_select_method_selector_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method_selector" value="1" />
        <input type="hidden" id="authentication_select_method_module_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method[modname]" value="{$authentication_method.modname}" />
        <input type="hidden" id="authentication_select_method_method_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" name="authentication_method[method]" value="{$authentication_method.method}" />
        <button style="text-align: left; padding-left: 5px;" type="submit" id="authentication_select_method_submit_{$authentication_method.modname|lower}_{$authentication_method.method|lower}" class="btn btn-default btn-xs btn-block authentication_select_method_button" name="submit">
            {if isset($icon) && !empty($icon)}
                {if !$isFontAwesomeIcon}
                    <img src="{$icon}" height="12" />
                {else}
                    <i class="fa {$icon} fa-fw"></i>
                {/if}
            {/if}
            {$submit_text}
        </button>
    </div>
</form>