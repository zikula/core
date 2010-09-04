<h2>{gt text="Finish the installation"}</h2>
{assign value="<a href=\"http://community.zikula.org/module-Extensions-view.htm\">`$themedburltxt`</a>" var=themedburl}
<p>{gt text='The installation is almost finished. Click the button below to finish and go to the site administration panel.'}</p>
<form class="z-form" action="install.php?lang={$lang}" method="post">
    <div>
        <input type="hidden" name="action" value="gotosite" />
        <input type="hidden" name="locale" value="{$locale}" />
        <input type="hidden" name="defaulttheme" value="Andreas08" />
        <div class="z-buttons z-formbuttons">
            <input type="submit" value="{gt text="Finish installation and go to site"}" class="z-bt-ok" />
        </div>
    </div>
</form>
