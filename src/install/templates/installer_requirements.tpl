{assign var="step" value=1}
<h2>{gt text="Check system requirements"}</h2>
<form class="form-horizontal" id="form_require" role="form" action="install.php{if not $installbySQL}?lang={$lang}{/if}" method="post">
    <div>
        <input type="hidden" name="locale" value="{$locale}" />
        <fieldset>
            <legend>{gt text="PHP"}</legend>
            <ul class="systemrequirements">
                {phpversion assign="phpversion"}
                {if $checks.phpsatisfied}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="Your PHP version is %s."  tag1=$phpversion}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="You have got a problem! Your PHP version is %s, which does not satisfy the Zikula system requirement of version 5.3.2 or later." tag1=$phpversion} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}

                {* PHP 5.3.0 or greater requires date.timezone to be set in php.ini *}
                {if $checks.datetimezone}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="php.ini: date.timezone is set to %s"  tag1=$checks.datetimezone}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="date.timezone is currently not set.  It needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin."} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.register_globals}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="PHP register_globals = Off"}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="PHP register_globals = On and must be turned off in php.ini, or .htaccess"} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.magic_quotes_gpc}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="PHP magic_quotes_gpc = Off"}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="PHP magic_quotes_gpc = On and must be turned off in php.ini"} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.pdo}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="PDO extension loaded."}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="You PHP installation doesn't have the PDO extension loaded."} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.phptokens}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="Your PHP installation has the necessary token functions available."}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="You have got a problem! Your PHP installation does not have the token functions available, but they are necessary for Zikula's output system."} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.mbstring}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="Your PHP installation has the multi-byte string functions available."}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="Your PHP installation does not have the multi-byte string functions available. Zikula needs this to handle multi-byte character sets."} <span class="label label-danger">{gt text="Error"}</span></li>
                {/if}

                {if $checks.pcreUnicodePropertiesEnabled}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="Your PHP installation's PCRE library has Unicode property support enabled."}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="Your PHP installation's PCRE library does not have Unicode property support enabled. Zikula needs this to handle multi-byte character sets in regular expressions. The PCRE library used with PHP must be compiled with the '--enable-unicode-properties' option."} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}

                {if $checks.json_encode}
                    <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="Your PHP installation has the JSON functions available."}</li>
                {else}
                    <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="Your PHP installation does not have the JSON functions available. Zikula needs this to handle AJAX requests."} <span class="label label-danger">{gt text="Error"}</span></li>
                {/if}
            </ul>
        </fieldset>

        <fieldset>
            <legend>{gt text="Personal configuration files"}</legend>
            <ul class="systemrequirements">
                {if $checks.config_personal_config_php eq true}
                <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="'%s' is not present.  This is OK." tag1="config/personal_config.php"}</li>
                {else}
                <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="'%s' has been found. This is not OK: please rename this file before continuing the installation process." tag1="config/personal_config.php"} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}
                {if $checks.custom_parameters_yml eq true}
                <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="'%s' is not present.  This is OK." tag1="app/config/custom_parameters.yml"}</li>
                {else}
                <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="'%s' has been found. This is not OK: please rename this file before continuing the installation process." tag1="app/config/custom_parameters.yml"} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}
            </ul>
        </fieldset>

        <fieldset>
            <legend>{gt text="File system permissions"}</legend>
            {assign var="files" value=$checks.files}
            <ul class="systemrequirements">
              {foreach from=$files item="file"}
                {if $file.writable}
                <li><span class="glyphicon glyphicon-ok glyphicon-green"></span> {gt text="%s is writeable." tag1=$file.filename}</li>
                {else}
                <li><span class="glyphicon glyphicon-remove glyphicon-red"></span> {gt text="You have a problem! '%s' is not writeable. Please ensure that the permissions are set correctly for the installation process." tag1=$file.filename} <span class="label label-danger">{gt text="Error"}</span></li>{assign var=checkfailed value=true}
                {/if}
              {/foreach}
            </ul>
        </fieldset>
        <div class="form-group"id="test">
            {if $checkfailed neq true}
            <input type="hidden" name="action" value="dbinformation" />
            <input type="submit" value="{gt text="Next"}" class="btn btn-default btn-success" />
            {else}
            <br />
            <input type="hidden" name="action" value="requirements" />
            <button type="submit" id="submit"  onclick="$('#ZikulaOverlay').show();" class="btn btn-default btn-danger">{gt text="Check again"}</button>
            {/if}
        </div>
    </div>
</form>
<br />
<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%;">
    <span class="sr-only">40% {gt text="Complete"}</span>
    </div>
</div>
