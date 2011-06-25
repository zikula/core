{adminheader}
{pageaddvar name="javascript" value="javascript/ajax/prototype.js"}
{pageaddvar name="javascript" value="system/SecurityCenter/javascript/securitycenter_admin_modifyconfig.js"}
{pageaddvarblock}
<script type="text/javascript">
    // <![CDATA[
    function toggleIdsFields() {
        $('securitycenter_idsfields').hide();

        if ($('useidsyes').checked === true) {
            $('securitycenter_idsfields').show();
        }
    }

    document.observe('dom:loaded', function() {
        $('useidsyes').observe('click', toggleIdsFields, false);
        $('useidsyes').observe('keypress', toggleIdsFields, false);
        $('useidsno').observe('click', toggleIdsFields, false);
        $('useidsno').observe('keypress', toggleIdsFields, false);

        toggleIdsFields();
    });
    // ]]>
</script>
{/pageaddvarblock}

<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="SecurityCenter" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <fieldset>
            <legend>{gt text="Automatic update settings"}</legend>
            <div class="z-formrow">
                <label for="securitycenter_updatecheck">{gt text="Check for updates"}</label>
                <div id="securitycenter_updatecheck">
                    <input id="securitycenter_updatecheck_yes" type="radio" name="updatecheck" value="1"{if $modvars.ZConfig.updatecheck eq 1} checked="checked"{/if} />
                    <label for="securitycenter_updatecheck_yes">{gt text="Yes"}</label>
                    <input id="securitycenter_updatecheck_no" type="radio" name="updatecheck" value="0"{if $modvars.ZConfig.updatecheck ne 1} checked="checked"{/if} />
                    <label for="securitycenter_updatecheck_no">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_updatefrequency">{gt text="How often"}</label>
                <select id="securitycenter_updatefrequency" name="updatefrequency" size="1">
                    <option value="30"{if $modvars.ZConfig.updatefrequency eq 30} selected="selected"{/if}>{gt text="Monthly"}</option>
                    <option value="7"{if $modvars.ZConfig.updatefrequency eq 7} selected="selected"{/if}>{gt text="Weekly"}</option>
                    <option value="1"{if $modvars.ZConfig.updatefrequency eq 1} selected="selected"{/if}>{gt text="Daily"}</option>
                </select>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Host settings"}</legend>
            <div class="z-formrow">
                <label for="securitycenter_keyexpiry">{gt text="Time limit for authorisation keys ('authkeys') in seconds (default: 0)"}</label>
                <input id="securitycenter_keyexpiry" type="text" name="keyexpiry" value="{$modvars.ZConfig.keyexpiry|safetext}" size="10" maxlength="15" />
            </div>
            <div class="z-formrow">
                <label for="securitycenter_sessionauthkeyua">{gt text="Bind authkey to user agent ('UserAgent')"}</label>
                <div id="securitycenter_sessionauthkeyua">
                    <input id="sessionauthkeyua1" type="radio" name="sessionauthkeyua" value="1"{if $modvars.ZConfig.sessionauthkeyua eq 1} checked="checked"{/if} />
                    <label for="sessionauthkeyua1">{gt text="Yes"}</label>
                    <input id="sessionauthkeyua0" type="radio" name="sessionauthkeyua" value="0"{if $modvars.ZConfig.sessionauthkeyua ne 1} checked="checked"{/if} />
                    <label for="sessionauthkeyua0">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_secure_domain">{gt text="Secure host name"}</label>
                <input id="securitycenter_secure_domain" type="text" name="secure_domain" value="{$modvars.ZConfig.secure_domain|safetext}" size="50" maxlength="100" />
                <p id="securitycenter_sitesecureurl_container" class="z-formnote z-informationmsg">{gt text="Notice: If you use a different host name for HTTPS secure sessions and you insert an address in the 'Secure host name' box, make sure you include a trailing slash at the end of the address."}</p>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Cookies settings"}</legend>
            <div class="z-formrow">
                <label for="securitycenter_signcookies">{gt text="Sign cookies"}</label>
                <div id="securitycenter_signcookies">
                    <input id="securitycenter_signcookies_yes" type="radio" name="signcookies" value="1"{if $modvars.ZConfig.signcookies eq 1} checked="checked"{/if} />
                    <label for="securitycenter_signcookies_yes">{gt text="Yes"}</label>
                    <input id="securitycenter_signcookies_no" type="radio" name="signcookies" value="0"{if $modvars.ZConfig.signcookies ne 1} checked="checked"{/if} />
                    <label for="securitycenter_signcookies_no">{gt text="No"}</label>
                </div>
            </div>
            <div id="securitycenter_signingkey_container" class="z-formrow">
                <label for="securitycenter_signingkey">{gt text="Signing key"}</label>
                <input id="securitycenter_signingkey" name="signingkey" type="text" value="{$modvars.ZConfig.signingkey|safetext}" size="50" maxlength="100" />
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Session settings"}</legend>
            <div class="z-formrow">
                <label for="securitycenter_seclevel">{gt text="Security level"} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime">(?)</a></label>
                <select id="securitycenter_seclevel" name="seclevel" size="1">
                    <option value="High"{if $modvars.ZConfig.seclevel eq 'High'} selected="selected"{/if}>{gt text="High (user is logged-out after X minutes of inactivity)"}</option>
                    <option value="Medium"{if $modvars.ZConfig.seclevel eq 'Medium'} selected="selected"{/if}>{gt text="Medium (user is logged-out after X minutes of inactivity, unless 'Remember me' checkbox is activated during log-in)"}</option>
                    <option value="Low"{if $modvars.ZConfig.seclevel eq 'Low'} selected="selected"{/if}>{gt text="Low (user stays logged-in until user logs-out)"}</option>
                </select>
            </div>
            <div id="securitycenter_seclevel_secmeddays_container">
                <div class="z-formrow">
                    <label for="securitycenter_secmeddays">{gt text="Automatically log user out after"}</label>
                    <div>
                        <input id="securitycenter_secmeddays" type="text" name="secmeddays" value="{$modvars.ZConfig.secmeddays|safetext}" size="4" />
                        <em>{gt text="days (if 'Remember me' is activated)"}</em>
                    </div>
                </div>
            </div>
            <div id="securitycenter_seclevel_secinactivemins_container">
                <div class="z-formrow">
                    <label for="securitycenter_secinactivemins">{gt text="Expire session after"} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime">(?)</a></label>
                    <div>
                        <input id="securitycenter_secinactivemins" type="text" name="secinactivemins" value="{$modvars.ZConfig.secinactivemins|safetext}" size="4" />
                        <em>{gt text="minutes of inactivity"}</em>
                    </div>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_sessionstoretofile">{gt text="Store sessions"}</label>
                <div id="securitycenter_sessionstoretofile">
                    <input id="securitycenter_sessionstoretofile_file" type="radio" name="sessionstoretofile" value="1"{if $modvars.ZConfig.sessionstoretofile eq 1} checked="checked"{/if} />
                    <label for="securitycenter_sessionstoretofile_file">{gt text="File"}</label>
                    <input id="securitycenter_sessionstoretofile_directory" type="radio" name="sessionstoretofile" value="0"{if $modvars.ZConfig.sessionstoretofile ne 1} checked="checked"{/if} />
                    <label for="securitycenter_sessionstoretofile_directory">{gt text="Database (recommended)"}</label>
                </div>
                <p id="securitycenter_wheretosavesessionswarning_container" class="z-formnote z-informationmsg">{gt text="Notice: If you change this setting, you will be logged-out immediately and will have to log back in again."}</p>
            </div>

            <div id="securitycenter_sessionsavepath_container" class="z-formrow">
                <label for="securitycenter_sessionsavepath">{gt text="Path for saving session files"} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.save-path">(?)</a></label>
                <input id="securitycenter_sessionsavepath" type="text" name="sessionsavepath" size="50" value="{$modvars.ZConfig.sessionsavepath|safetext}" />
                <p id="securitycenter_sessionfilessavepathwarning_container" class="z-formnote z-informationmsg">{gt text="Notice: If you change 'Where to save sessions' to 'File' then you must enter a path in the 'Path for saving session files' box above. The path must be writeable."}</p>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_gc_probability">{gt text="Garbage collection probability"} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability">(?)</a></label>
                <div>
                    <input id="securitycenter_gc_probability" type="text" name="gc_probability" value="{$modvars.ZConfig.gc_probability|safetext}" size="4" maxlength="5" />
                    <em>{gt text="/10000"}</em>
                </div>
            </div>

            <div class="z-formrow">
                <label for="securitycenter_sessioncsrftokenonetime">{gt text="CSRF Token"}</label>
                <div id="securitycenter_sessioncsrftokenonetime">
                    <input id="securitycenter_sessioncsrftokenonetime_persession" type="radio" name="sessioncsrftokenonetime" value="1"{if $modvars.ZConfig.sessioncsrftokenonetime eq 1} checked="checked"{/if} />
                    <label for="securitycenter_sessioncsrftokenonetime_persession">{gt text="Per session"}</label>
                    <input id="securitycenter_sessioncsrftokenonetime_onetime" type="radio" name="sessioncsrftokenonetime" value="0"{if $modvars.ZConfig.sessioncsrftokenonetime ne 1} checked="checked"{/if} />
                    <label for="securitycenter_sessioncsrftokenonetime_onetime">{gt text="One time use"}</label>
                </div>
                <p id="securitycenter_sessioncsrftokenonetime_container" class="z-formnote z-informationmsg">{gt text="One time CSRF protection may affect the browser back button but it more secure."}</p>
            </div>

            <div class="z-formrow">
                <label for="securitycenter_anonymoussessions">{gt text="Use sessions for anonymous guests"}</label>
                <div id="securitycenter_anonymoussessions">
                    <input id="anonymoussessions1" type="radio" name="anonymoussessions" value="1"{if $modvars.ZConfig.anonymoussessions eq 1} checked="checked"{/if} />
                    <label for="anonymoussessions1">{gt text="Yes"}</label>
                    <input id="anonymoussessions0" type="radio" name="anonymoussessions" value="0"{if $modvars.ZConfig.anonymoussessions ne 1} checked="checked"{/if} />
                    <label for="anonymoussessions0">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_sessionrandregenerate">{gt text="Periodically regenerate session ID"}</label>
                <div id="securitycenter_sessionrandregenerate">
                    <input id="sessionrandregenerate1" type="radio" name="sessionrandregenerate" value="1"{if $modvars.ZConfig.sessionrandregenerate eq 1} checked="checked"{/if} />
                    <label for="sessionrandregenerate1">{gt text="Yes"}</label>
                    <input id="sessionrandregenerate0" type="radio" name="sessionrandregenerate" value="0"{if $modvars.ZConfig.sessionrandregenerate ne 1} checked="checked"{/if} />
                    <label for="sessionrandregenerate0">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_sessionregenerate">{gt text="Regenerate session ID during log-in and log-out"}</label>
                <div id="securitycenter_sessionregenerate">
                    <input id="sessionregenerate1" type="radio" name="sessionregenerate" value="1"{if $modvars.ZConfig.sessionregenerate eq 1} checked="checked"{/if} />
                    <label for="sessionregenerate1">{gt text="Yes"}</label>
                    <input id="sessionregenerate0" type="radio" name="sessionregenerate" value="0"{if $modvars.ZConfig.sessionregenerate ne 1} checked="checked"{/if} />
                    <label for="sessionregenerate0">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_sessionregeneratefreq">{gt text="Regeneration probability"}</label>
                <div>
                    <input id="securitycenter_sessionregeneratefreq" type="text" name="sessionregeneratefreq" value="{$modvars.ZConfig.sessionregeneratefreq|safetext}" size="3" maxlength="3" />
                    <em>{gt text="% (0 to disable)"}</em>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_sessionipcheck">{gt text="IP checks on session (may cause problems for AOL users)"}</label>
                <div id="securitycenter_sessionipcheck">
                    <input id="sessionipcheck1" type="radio" name="sessionipcheck" value="1"{if $modvars.ZConfig.sessionipcheck eq 1} checked="checked"{/if} />
                    <label for="sessionipcheck1">{gt text="Yes"}</label>
                    <input id="sessionipcheck0" type="radio" name="sessionipcheck" value="0"{if $modvars.ZConfig.sessionipcheck ne 1} checked="checked"{/if} />
                    <label for="sessionipcheck0">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="securitycenter_sessionname">{gt text="Session cookie name"}</label>
                <input id="securitycenter_sessionname" type="text" name="sessionname" value="{$modvars.ZConfig.sessionname|safetext}" size="20" />
                <p id="securitycenter_sessionnamewarning_container" class="z-formnote z-warningmsg">{gt text="Notice: If you change the 'Session cookie name' setting, all registered users who are currently logged-in will then be logged-out automatically, and they will have to log back in again."}</p>
            </div>
        </fieldset>
        <fieldset id="securitycenter_ids">
            <legend>{gt text="Intrusion Detection System"}</legend>
            <div class="z-formrow">
                <label for="securitycenter_useids">{gt text="Use PHPIDS"}</label>
                <div id="securitycenter_useids">
                    <input id="useidsyes" type="radio" name="useids" value="1"{if $modvars.ZConfig.useids eq 1} checked="checked"{/if} />
                    <label for="useidsyes">{gt text="Yes"}</label>
                    <input id="useidsno" type="radio" name="useids" value="0"{if $modvars.ZConfig.useids neq 1} checked="checked"{/if} />
                    <label for="useidsno">{gt text="No"}</label>
                </div>
            </div>

            <div id="securitycenter_idsfields">
                <p class="z-formnote z-informationmsg">
                    {gt text="PHPIDS performs many different checks which return an impact value for scoring the treated request. Depending on the sum of all impacts performed actions are selected."}
                    {gt text="Read more about this system on the <a href=\"http://phpids.org\" title=\"PHPIDS homepage\">PHPIDS homepage</a>."}
                </p>
                <div class="z-formrow">
                    <label for="securitycenter_idssoftblock">{gt text="Block action"}</label>
                    <div id="securitycenter_idssoftblock">
                        <input id="idssoftblockyes" type="radio" name="idssoftblock" value="1"{if $modvars.ZConfig.idssoftblock eq 1} checked="checked"{/if} />
                        <label for="idssoftblockyes">{gt text="Warn only"}</label>
                        <input id="idssoftblockno" type="radio" name="idssoftblock" value="0"{if $modvars.ZConfig.idssoftblock neq 1} checked="checked"{/if} />
                        <label for="idssoftblockno">{gt text="Block"}</label>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_idsmail">{gt text="Send email on block action"}</label>
                    <div id="securitycenter_idsmail">
                        <input id="idsmailyes" type="radio" name="idsmail" value="1"{if $modvars.ZConfig.idsmail eq 1} checked="checked"{/if} />
                        <label for="idsmailyes">{gt text="Yes"}</label>
                        <input id="idsmailno" type="radio" name="idsmail" value="0"{if $modvars.ZConfig.idsmail neq 1} checked="checked"{/if} />
                        <label for="idsmailno">{gt text="No"}</label>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_idsfilter">{gt text="Select filter rules to use"}</label>
                    <select id="securitycenter_idsfilter" name="idsfilter">
                        <option value="xml"{if $modvars.ZConfig.idsfilter ne "json"} selected="selected"{/if}>{gt text="XML"}</option>
                        <option value="json"{if $modvars.ZConfig.idsfilter eq "json"} selected="selected"{/if}>{gt text="JSON"}</option>
                    </select>
                </div>
                <div class="z-formrow">
                    {gt text="Default: config/phpids_zikula_default.xml"}
                    <label for="securitycenter_idsrulepath">{gt text="IDS Rule path"}</label>
                    <input id="securitycenter_idsrulepath" type="text" name="idsrulepath" size="3" value="{$modvars.ZConfig.idsrulepath|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_impactthresholdone">{gt text="Minimum impact to log intrusion in the database"}</label>
                    <input id="securitycenter_impactthresholdone" type="text" name="idsimpactthresholdone" size="3" value="{$modvars.ZConfig.idsimpactthresholdone|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_impactthresholdtwo">{gt text="Minimum impact to email the administrator"}</label>
                    <input id="securitycenter_impactthresholdtwo" type="text" name="idsimpactthresholdtwo" size="3" value="{$modvars.ZConfig.idsimpactthresholdtwo|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_impactthresholdthree">{gt text="Minimum impact to block the request"}</label>
                    <input id="securitycenter_impactthresholdthree" type="text" name="idsimpactthresholdthree" size="3" value="{$modvars.ZConfig.idsimpactthresholdthree|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_impactthresholdfour">{gt text="Minimum impact to kick the user (destroy the session)"}</label>
                    <input id="securitycenter_impactthresholdfour" type="text" name="idsimpactthresholdfour" size="3" value="{$modvars.ZConfig.idsimpactthresholdfour|safetext}" />
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_impactmode">{gt text="Select the way the impact thresholds are used in Zikula"}</label>
                    <select id="securitycenter_impactmode" name="idsimpactmode">
                        <option value="1"{if $modvars.ZConfig.idsimpactmode ne 2 && $modvars.ZConfig.idsimpactmode ne 3} selected="selected"{/if}>{gt text="React on impact per request (uses the values from above)"}</option>
                        <option value="2"{if $modvars.ZConfig.idsimpactmode eq 2} selected="selected"{/if}>{gt text="React on impact sum per session [loose] (uses the values from above * 10)"}</option>
                        <option value="3"{if $modvars.ZConfig.idsimpactmode eq 3} selected="selected"{/if}>{gt text="React on impact sum per session [strict] (uses the values from above * 5)"}</option>
                    </select>
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_idshtmlfields">{gt text="Define which fields contain HTML and need preparation"}</label>
                    <textarea id="securitycenter_idshtmlfields" name="idshtmlfields" cols="50" rows="8">{$idshtmlfields|safetext}</textarea>
                    <em class="z-formnote z-sub">{gt text='(Place each value on a separate line.)'}</em>
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_idsjsonfields">{gt text="Define which fields contain JSON data and should be treated as such"}</label>
                    <textarea id="securitycenter_idsjsonfields" name="idsjsonfields" cols="50" rows="8">{$idsjsonfields|safetext}</textarea>
                    <em class="z-formnote z-sub">{gt text='(Place each value on a separate line.)'}</em>
                </div>
                <div class="z-formrow">
                    <label for="securitycenter_idsexceptions">{gt text="Define which fields should not be monitored"}</label>
                    <textarea id="securitycenter_idsexceptions" name="idsexceptions" cols="50" rows="8">{$idsexceptions|safetext}</textarea>
                    <em class="z-formnote z-sub">{gt text='(Place each value on a separate line.)'}</em>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Output filter settings"}</legend>
            <div class="z-formrow">
                <label for="securitycenter_outputfilter">{gt text="Select output filter"}</label>
                <select id="securitycenter_outputfilter" name="outputfilter">
                    <option value="0"{if $modvars.ZConfig.outputfilter eq 0} selected="selected"{/if}>{gt text="Use internal output filter only"}</option>
                    <option value="1"{if $modvars.ZConfig.outputfilter eq 1} selected="selected"{/if}>{gt text="Use 'HTML Purifier' + internal mechanism as output filter"}</option>
                </select>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname='SecurityCenter' type='admin' func='main'}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}