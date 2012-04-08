{assign var="step" value=1}
<h2>{gt text="Check system requirements"}</h2>
<form class="z-form" action="install.php{if not $installbySQL}?lang={$lang}{/if}" method="post">
    <div>
        <input type="hidden" name="locale" value="{$locale}" />
        <fieldset>
            <legend>{gt text="PHP"}</legend>
            <ul class="systemrequirements">
                {phpversion assign="phpversion"}
                {if $checks.phpsatisfied}
                    <li class="passed">{gt text="Your PHP version is %s."  tag1=$phpversion}</li>
                {else}
                    <li class="failed">{gt text="You have got a problem! Your PHP version is %s, which does not satisfy the Zikula system requirement of version 5.3.2 or later." tag1=$phpversion}</li>{assign var=checkfailed value=true}
                {/if}

                {* PHP 5.3.0 or greater requires date.timezone to be set in php.ini *}
                {if $checks.datetimezone}
                    <li class="passed">{gt text="php.ini: date.timezone is set to %s"  tag1=$checks.datetimezone}</li>
                {else}
                    <li class="failed">{gt text="date.timezone is currently not set.  It needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin."}</li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.register_globals}
                    <li class="passed">{gt text="PHP register_globals = Off"}</li>
                {else}
                    <li class="failed">{gt text="PHP register_globals = On and must be turned off in php.ini, or .htaccess"}</li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.magic_quotes_gpc}
                    <li class="passed">{gt text="PHP magic_quotes_gpc = Off"}</li>
                {else}
                    <li class="failed">{gt text="PHP magic_quotes_gpc = On and must be turned off in php.ini"}</li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.pdo}
                    <li class="passed">{gt text="PDO extension loaded."}</li>
                {else}
                    <li class="failed">{gt text="You PHP installation doesn't have the PDO extension loaded."}</li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.phptokens}
                    <li class="passed">{gt text="Your PHP installation has the necessary token functions available."}</li>
                {else}
                    <li class="failed">{gt text="You have got a problem! Your PHP installation does not have the token functions available, but they are necessary for Zikula's output system."}</li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.mbstring}
                    <li class="passed">{gt text="Your PHP installation has the multi-byte string functions available."}</li>
                {else}
                    <li class="failed">{gt text="Your PHP installation does not have the multi-byte string functions available. Zikula needs this to handle multi-byte character sets."}</li>
                {/if}

                {if $checks.pcreUnicodePropertiesEnabled}
                    <li class="passed">{gt text="Your PHP installation's PCRE library has Unicode property support enabled."}</li>
                {else}
                    <li class="failed">{gt text="Your PHP installation's PCRE library does not have Unicode property support enabled. Zikula needs this to handle multi-byte character sets in regular expressions. The PCRE library used with PHP must be compiled with the '--enable-unicode-properties' option."}</li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.json_encode}
                    <li class="passed">{gt text="Your PHP installation has the JSON functions available."}</li>
                {else}
                    <li class="failed">{gt text="Your PHP installation does not have the JSON functions available. Zikula needs this to handle AJAX requests."}</li>
                {/if}
            </ul>
        </fieldset>

        <fieldset>
            <legend>{gt text="Personal configuration file"}</legend>
            <ul class="systemrequirements">
                {if $checks.config_personal_config_php eq true}
                <li class="passed">{gt text="'%s' is not present.  This is OK." tag1="config/personal_config.php"}</li>
                {else}
                <li class="failed">{gt text="'%s' has been found. This is not OK: please rename this file before continuing the installation process." tag1="config/personal_config.php"}</li>{assign var=checkfailed value=true}
                {/if}
            </ul>
        </fieldset>

        <fieldset>
            <legend>{gt text="File system permissions"}</legend>
            {assign var="files" value=$checks.files}
            <ul class="systemrequirements">
              {foreach from=$files item="file"}
                {if $file.writable}
                <li class="passed">{gt text="%s is writeable." tag1=$file.filename}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the permissions are set correctly for the installation process." tag1=$file.filename}</li>{assign var=checkfailed value=true}
                {/if}
              {/foreach}
            </ul>
        </fieldset>
        <div class="z-buttons z-center">
            {if $checkfailed neq true}
            <input type="hidden" name="action" value="dbinformation" />
            <input type="submit" value="{gt text="Next"}" class="z-bt-ok" />
            {else}
            <input type="hidden" name="action" value="requirements" />
            <input type="submit" value="{gt text="Check again"}" class="z-bt-ok" />
            {/if}
        </div>
    </div>
</form>
