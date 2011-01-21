<h2>{gt text="Check system requirements"}</h2>
<form class="z-form" action="install.php{if not $installbySQL}?lang={$lang}{/if}" method="post">
    <div>
        <input type="hidden" name="locale" value="{$locale}" />
        <fieldset>
            <legend>{gt text="PHP"}</legend>
            <ul class="systemrequirements">
                {phpversion assign="phpversion"}
                {versioncompare minversion="5.2.6" assign="phpsatisfied"}
                {if $phpsatisfied}
                <li class="passed">{gt text="Your PHP version is %s."  tag1=$phpversion}</li>
                {else}
                <li class="failed">{gt text="You have got a problem! Your PHP version is %s, which does not satisfy the Zikula system requirement of version 5.2.6 or later." tag1=$phpversion}</li>{assign var=checkfailed value=true}
                {/if}
                {* PHP 5.3.0 or greater requires date.timezone to be set in php.ini *}
                {versioncompare minversion="5.3.0" assign="checkdatetimezone"}
                {ini_get varname="date.timezone" assign="datetimezone"}
                {if $checkdatetimezone}
                {if $datetimezone}
                <li class="passed">{gt text="php.ini: date.timezone is set to %s"  tag1=$datetimezone}</li>
                {else}
                <li class="failed">{gt text="date.timezone is currently not set.  It needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin."}</li>{assign var=checkfailed value=true}
                {/if}
                {else}
                <li class="passed">{gt text="date.timezone not needed for php version %s."  tag1=$phpversion}</li>
                {/if}
                {extension_loaded extension="pdo" assign="pdo"}
                {if $pdo}
                <li class="passed">{gt text="PDO extension loaded."}</li>
                {else}
                <li class="failed">{gt text="You PHP installation doesn't have the PDO extension loaded."}</li>{assign var=checkfailed value=true}
                {/if}
                {phpfunctionexists func="token_get_all" assign="phptokens"}
                {if $phptokens}
                <li class="passed">{gt text="Your PHP installation has the necessary token functions available."}</li>
                {else}
                <li class="failed">{gt text="You have got a problem! Your PHP installation does not have the token functions available, but they are necessary for Zikula's output system."}</li>{assign var=checkfailed value=true}
                {/if}
                {phpfunctionexists func="mb_get_info" assign="mbstring"}
                {if $mbstring}
                <li class="passed">{gt text="Your PHP installation has the multi-byte string functions available."}</li>
                {else}
                <li class="failed">{gt text="Your PHP installation does not have the multi-byte string functions available. Zikula needs this to handle multi-byte character sets."}</li>
                {/if}
                {php}
                    $isEnabled = @preg_match('/^\p{L}+$/u', 'TheseAreLetters');
                    $this->assign('pcreUnicodePropertiesEnabled', (isset($isEnabled) && (bool)$isEnabled));
                {/php}
                {if $pcreUnicodePropertiesEnabled}
                <li class="passed">{gt text="Your PHP installation's PCRE library has Unicode property support enabled."}</li>
                {else}
                <li class="failed">{gt text="Your PHP installation's PCRE library does not have Unicode property support enabled. Zikula needs this to handle multi-byte character sets in regular expressions. The PCRE library used with PHP must be compiled with the '--enable-unicode-properties' option."}</li>{assign var=checkfailed value=true}
                {/if}
                {phpfunctionexists func="json_encode" assign="json_encode"}
                {if $json_encode}
                <li class="passed">{gt text="Your PHP installation has the JSON functions available."}</li>
                {else}
                <li class="failed">{gt text="Your PHP installation does not have the JSON functions available. Zikula needs this to handle AJAX requests."}</li>
                {/if}
            </ul>
        </fieldset>
        <fieldset>
            <legend>{gt text="Personal configuration file"}</legend>
            <ul class="systemrequirements">
                {fileexists file="config/personal_config.php" assign="file"}
                {if $file neq true}
                <li class="passed">{gt text="'%s' is not present.  This is OK." tag1="config/personal_config.php"}</li>
                {else}
                <li class="failed">{gt text="'%s' has been found. This is not OK: please rename this file before continuing the installation process." tag1="config/personal_config.php"}</li>{assign var=checkfailed value=true}
                {/if}
            </ul>
        </fieldset>
        <fieldset>
            <legend>{gt text="File system permissions"}</legend>
            <ul class="systemrequirements">
                {iswriteable file="config/config.php" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s is writeable." tag1="config/config.php"}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1="config/config.php"}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$datadir`" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s/ is writeable." tag1=$datadir}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1=$datadir}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$temp`" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s/ is writeable." tag1=$temp}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1=$temp}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$temp`/error_logs" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s/ is writeable." tag1="$temp/error_logs"}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1="$temp/error_logs"}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$temp`/view_compiled" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s is writeable." tag1="$temp/view_compiled"}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1="$temp/view_compiled"}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$temp`/view_cache" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s/ is writeable." tag1="$temp/view_cache"}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1="$temp/view_cache"}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$temp`/Theme_compiled" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s/ is writeable." tag1="$temp/Theme_compiled"}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1="$temp/Theme_compiled"}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$temp`/Theme_cache" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s/ is writeable." tag1="$temp/Theme_cache"}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1="$temp/Theme_cache"}</li>{assign var=checkfailed value=true}
                {/if}
                {iswriteable file="`$temp`/Theme_Config" assign="file"}
                {if $file}
                <li class="passed">{gt text="%s/ is writeable." tag1="$temp/Theme_Config"}</li>
                {else}
                <li class="failed">{gt text="You have a problem! '%s' is not writeable. Please ensure that the file permissions are set correctly for the installation process." tag1="$temp/Theme_Config"}</li>{assign var=checkfailed value=true}
                {/if}
            </ul>
        </fieldset>
        <div class="z-buttons z-formbuttons">
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
