{assign var="step" value=0}
{if not $installbySQL}
<h2>{gt text="Select language"}</h2>
{/if}
{phpfunctionexists func=mb_get_info assign=mbstring}
{if !$mbstring}
<div class="alert alert-danger">FATAL ERROR: mbstring is not installed in PHP.  Zikula will not install without this extension.</div>
{else}
<form class="form-horizontal" role="form" id="form_lang" class="z-form gap" action="install.php?lang={$lang}" method="get">
    <div>
        <input type="hidden" name="action" value="requirements" />
        {if not $installbySQL}
        <fieldset>
            <legend>{gt text="Select language"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="lang">{gt text="Choose a language"}</label>
                <div class="col-lg-9">
                {html_select_locales name=lang all=false installed=true selected=$lang id=lang}
            </div>
        </div>
        </fieldset>
        {/if}
        <br />
        <div class="btn-group">            
            <button type="submit" id="submit" class="btn btn-default btn-primary">{gt text="Next"}</button>
        </div>
    </div>
</form>
{/if}
<br />
<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%;">
    <span class="sr-only">20% {gt text="Complete"}</span>
    </div>
</div>
