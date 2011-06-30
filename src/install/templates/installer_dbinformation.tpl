{assign var="step" value=2}
<h2>{gt text="Enter database information"}</h2>
{if $dbconnectfailed or $dbconnectmissing or $dbinvalidprefix or $dbinvalidname or $dbdumpfailed or $dbexists}
<div class="z-errormsg">
    {if $dbconnectmissing}{gt text="Error! Some of the required information was not entered. Please check your entries and try again."}<br />{$reason}
    {elseif $dbinvalidprefix}{gt text="Error! Invalid table prefix. Please use only letters or numbers."}<br />{$reason}
    {elseif $dbinvalidname}{gt text="Error! Invalid database name. Please use only letters, numbers, '-' or '_' with a maximum of 64 characters."}<br />{$reason}
    {elseif $dbconnectfailed}{gt text="Error! Could not connect to the database. Please check that you have entered the correct database information and try again."}<br />{$reason}
    {elseif $dbdumpfailed}{gt text="Error! Could not dump the database. Please check that the file zikulacms.sql is located within the folder install/sql and it is readable."}<br />{$reason}
    {elseif $dbexists}{gt text="Error! The database exists and contain tables. Please delete all tables before to proceed."}<br />{$reason}
    {/if}
</div>
{/if}
<form id="dbinformation_form" class="z-form" action="install.php{if not $installbySQL}?lang={$lang}{/if}" method="post">
    <div>
        <input type="hidden" name="action" value="processBDInfo" />
        <input type="hidden" name="locale" value="{$locale}" />
        <fieldset>
            <legend>{gt text="Database information"}</legend>
            <div class="z-formrow">
                <label for="dbdriver">{gt text="Database type"}</label>
                {dbtypes name=dbdriver selectedValue=$dbdriver id=dbdriver}
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
                <p class="z-formnote z-informationmsg">
                    <strong>{gt text="Please ensure the database is in UTF8 format."}</strong>
                </p>
            </div>

        </fieldset>
        <div class="z-buttons z-center">
            <input type="submit" value="{gt text="Next"}" class="z-bt-ok" />
        </div>
    </div>
</form>
