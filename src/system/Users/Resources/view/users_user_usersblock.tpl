<h2>{gt text="Personal custom block"}</h2>

<form action="{modurl modname='Users' type='user' func='updateusersblock'}" method="post">
    <div>
        <p>{gt text="Block content"}</p>
        <label for="usersblock_enable">{gt text="Enable your personal custom block"}</label>
        <input id="usersblock_enable" type="checkbox" name="ublockon" value="1"{if $ublockon} checked="checked"{/if} />
        <textarea id="usersblock_ublock" cols="80" rows="10" name="ublock">{$ublock|safetext}</textarea>
        <p>{gt text="Notice: Your personal custom block is a special block that will display in site pages when you are logged-in to the site. It is only displayed for you, and you choose what content it contains. One example of what you can do with the block is to use HTML code to include links to Web pages on this site or another site. Are there site pages that you frequently visit but that don't have links in the main menu? You can insert a link to them here, so that they are easily accessible for you. To see this block during your visits, don't forget to activate the 'Enable your personal custom block' checkbox. Afterwards, click 'Save' to save your changes."}</p>
        <input type="submit" value="{gt text="Save"}" />
    </div>
</form>
