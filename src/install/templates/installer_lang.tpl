{assign var="step" value=0}
{if not $installbySQL}
<h2>{gt text="Select language"}</h2>
{/if}
{phpfunctionexists func=mb_get_info assign=mbstring}
{if !$mbstring}
<div class="alert alert-danger">
    {gt text="FATAL ERROR: mbstring is not installed in PHP.  Zikula will not install without this extension."}
</div>
{else}
<form class="form-horizontal gap" role="form" id="form_lang" action="install.php?lang={$lang}" method="get">
    <div>
        <input type="hidden" name="action" value="requirements" />
        {if not $installbySQL}
        <fieldset>
            <legend>{gt text="Select language"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="lang">{gt text="Choose a language"}</label>
                <div class="col-lg-9">
                    {html_select_locales class="form-control" name=lang all=false installed=true selected=$lang id=lang class=form-control}
                </div>
            </div>
        </fieldset>
        {/if}
        <br />
        <div class="form-group"> 
            <div class="col-lg-offset-3 col-lg-9">
				<button type="submit" id="submit" class="btn btn-primary">
					<span class="fa fa-angle-double-right"></span> {gt text="Next"}
				</button>
			</div>
        </div>
    </div>
</form>
{/if}