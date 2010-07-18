<h2>{gt text="Select default theme"}</h2>
{gt text="Theme extensions database" assign=themedburltxt}
{assign value="<a href=\"http://community.zikula.org/module-Extensions-view.htm\">`$themedburltxt`</a>" var=themedburl}
<p>{gt text='Themes control the visual presentation of a site. Zikula ships with a small selection of themes, but many more are available from the %s. Please select a default theme for your new site (you can easily choose another later).' tag1=$themedburl}</p>
<form class="z-form" action="install.php?lang={$lang}" method="post">
    <div>
        <input type="hidden" name="action" value="gotosite" />
        <input type="hidden" name="locale" value="{$locale}" />
        <input type="hidden" name="installtype" value="{$installtype}" />
        <fieldset>
            <legend>{gt text="Select theme"}</legend>
            <div class="z-formrow">
                {themelist}
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            <input type="submit" value="{gt text="Next"}" class="z-bt-ok" />
        </div>
    </div>
</form>
