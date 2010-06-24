<h2>{gt text="Choose a module for your site's start page"}</h2>
{gt text="Module extensions database" assign=modulegt1}
{gt text="Note:" assign=modulegt2}
{assign value="<a href=\"http://community.zikula.org/module-Extensions-view.htm\">`$modulegt1`</a>" var=moduleinsert1}
{assign value="<strong>`$modulegt2`</strong>" var=moduleinsert2}
<p>{gt text='Modules are software that extends the functionality of a site. There is a wide choice of add-on modules available from the Zikula %1$s. Please choose a default module for your new site. %2$s You can easily change this later.' tag1=$moduleinsert1 tag2=$moduleinsert2}</p>
<form class="z-form" action="install.php?lang={$lang}" method="post">
    <div>
        <input type="hidden" name="action" value="selecttheme" />
        <input type="hidden" name="locale" value="{$locale}" />
        <input type="hidden" name="installtype" value="{$installtype}" />
        <fieldset>
            <legend>{gt text="Select start page"}</legend>
            <div class="z-formrow">
                <label for="defaultmodule">{gt text="Choose a module"}</label>
                <select id="defaultmodule" name="defaultmodule">
                    <option value="">{gt text="No start module (static front page)"}</option>
                    {html_select_modules type=user selected="Tour"}
                </select>
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            <input type="submit" value="{gt text="Next"}" class="z-btblue" />
        </div>
    </div>
</form>
