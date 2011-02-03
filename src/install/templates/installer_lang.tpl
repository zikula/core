{assign var="step" value=0}
{if not $installbySQL}
<h2>{gt text="Select language"}</h2>
{/if}
{phpfunctionexists func=mb_get_info assign=mbstring}
{if !$mbstring}
<div class="z-errormsg">FATAL ERROR: mbstring is not installed in PHP.  Zikula will not install without this extension.</div>
{else}
<form id="lang_form" class="z-form z-gap" action="install.php?lang={$lang}" method="get">
    <div>
        <input type="hidden" name="action" value="requirements" />
        {if not $installbySQL}
        <fieldset>
            <legend>{gt text="Select language"}</legend>
            <div class="z-formrow">
                <label for="lang">{gt text="Choose a language"}</label>
                {html_select_locales name=lang all=false installed=true selected=$lang id=lang}
            </div>
        </fieldset>
        {/if}
        <div class="z-buttons z-center">
            <input type="submit" value="{gt text="Next"}" class="z-bt-ok" />
        </div>
    </div>
</form>
{/if}
