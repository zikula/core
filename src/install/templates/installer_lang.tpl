<p>{gt text="Welcome to the Zikula installer script. This script will set-up the Zikula database and will guide you through choosing the various options for installing your new site. You will be walked through a series of pages. Each page constitutes one step in the installation process. The entire process commonly takes about ten minutes. If you have questions or problems, please visit the project support forums for help."}</p>
{if not $installbySQL}
<h2>{gt text="Select language"}</h2>
{/if}
{phpfunctionexists func=mb_get_info assign=mbstring}
{if !$mbstring}
<div class="z-errormsg">FATAL ERROR: mbstring is not installed in PHP.  Zikula will not install without this extension.</div>
{else}
<form id="lang_form" class="z-form" action="install.php?lang={$lang}" method="get">
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
        <div class="z-buttons z-formbuttons">
            <input type="submit" value="{gt text="Next"}" class="z-bt-ok" />
        </div>
    </div>
</form>
{/if}
