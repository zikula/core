<h2>{gt text="Enter database information"}</h2>
{if $dbcreatefailed or $dbconnectfailed or $dbconnectmissing or $dbinvalidprefix or $dbdumpfailed or $dbexists}
<div class="z-errormsg">
    {if $dbconnectmissing}{gt text="Error! Some of the required information was not entered. Please check your entries and try again."}<br />{$reason}
    {elseif $dbinvalidprefix}{gt text="Error! Invalid table prefix. Please use only letters or numbers."}<br />{$reason}
    {elseif $dbconnectfailed}{gt text="Error! Could not connect to the database. Please check that you have entered the correct database information and try again."}<br />{$reason}
    {elseif $dbcreatefailed}{gt text="Error! Could not create database. Please check that you have entered the correct database information and try again."}<br />{$reason}
    {elseif $dbdumpfailed}{gt text="Error! Could not dump the database. Please check that the file zikulacms.sql is located within the folder install/sql and it is readable."}<br />{$reason}
    {elseif $dbexists}{gt text="Error! The database exists and contain tables. Please delete all tables before to proceed."}<br />{$reason}
    {/if}
</div>
{/if}
<form class="z-form" action="install.php{if not $installbySQL}?lang={$lang}{/if}" method="post">
    <div>
        <input type="hidden" name="action" value="installtype" />
        <input type="hidden" name="locale" value="{$locale}" />
        <fieldset>
            <legend>{gt text="Database information"}</legend>
            <div class="z-formrow">
                <label for="dbtype">{gt text="Database type"}</label>
                {dbtypes name=dbtype selectedValue=$dbtype id=dbtype}
            </div>
            <div class="z-formrow">
                <label for="dbtabletype">{gt text="Database table type (MySQL only)"}</label>
                <select name="dbtabletype" id="dbtabletype">
                    <option value="myisam"{if $dbtabletype eq myisam} selected="selected"{/if}>MyISAM</option>
                    <option value="innodb"{if $dbtabletype eq innodb} selected="selected"{/if}>InnoDB</option>
                </select>
            </div>
            <div class="z-formrow">
                <label for="dbhost">{gt text="Host"}</label>
                <input type="text" name="dbhost" id="dbhost" maxlength="80" value="{$dbhost|default:'localhost'}" />
            </div>
            <div class="z-formrow">
                <label for="dbusername">{gt text="User name"}</label>
                <input type="text" name="dbusername" id="dbusername" maxlength="80" value="{$dbusername}" />
            </div>
            <div class="z-formrow">
                <label for="dbpassword">{gt text="Password"}</label>
                <input type="password" name="dbpassword" id="dbpassword" maxlength="80" value="{$dbpassword}" />
            </div>
            <div class="z-formrow">
                <label for="dbname">{gt text="Database name"}</label>
                <input type="text" name="dbname" id="dbname" maxlength="80" value="{$dbname}" />
            </div>
            <div class="z-formrow">
                <label for="dbprefix">{gt text="Table prefix (for table sharing)"}</label>
                <input type="text" name="dbprefix" id="dbprefix" maxlength="40" value="{$dbprefix|default:'z'}" />
            </div>
            <div class="z-formrow">
                <label for="createdb">{gt text="Let this installer create the database."}</label>
                <input type="checkbox" name="createdb" id="createdb" value="1" />
                <p class="z-formnote z-informationmsg">
                    {gt text="Requires that the user details above have database CREATE privileges."}
                    {gt text="If not you will need to create the database manually through your hosting control panel."}
                    <strong>{gt text="If you create your database manually you must create it as UTF8."}</strong>
                </p>
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            <input type="submit" value="{gt text="Next"}" class="z-bt-ok" />
        </div>
    </div>
</form>
