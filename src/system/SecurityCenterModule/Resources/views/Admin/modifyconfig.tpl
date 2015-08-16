{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text='Settings'}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulasecuritycentermodule_admin_updateconfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='Automatic update settings'}</legend>

        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />

        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Check for updates'}</label>
            <div class="col-sm-9">
                <div id="securitycenter_updatecheck">
                    <input id="securitycenter_updatecheck_yes" type="radio" name="updatecheck" value="1"{if $modvars.ZConfig.updatecheck eq 1} checked="checked"{/if} />
                    <label for="securitycenter_updatecheck_yes">{gt text='Yes'}</label>
                    <input id="securitycenter_updatecheck_no" type="radio" name="updatecheck" value="0"{if $modvars.ZConfig.updatecheck ne 1} checked="checked"{/if} />
                    <label for="securitycenter_updatecheck_no">{gt text='No'}</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_updatefrequency">{gt text='How often'}</label>
            <div class="col-sm-9">
                <select class="form-control" id="securitycenter_updatefrequency" name="updatefrequency" size="1">
                    <option value="30"{if $modvars.ZConfig.updatefrequency eq 30} selected="selected"{/if}>{gt text='Monthly'}</option>
                    <option value="7"{if $modvars.ZConfig.updatefrequency eq 7} selected="selected"{/if}>{gt text='Weekly'}</option>
                    <option value="1"{if $modvars.ZConfig.updatefrequency eq 1} selected="selected"{/if}>{gt text='Daily'}</option>
                </select>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Host settings'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_keyexpiry">{gt text="Time limit for authorisation keys ('authkeys') in seconds (default: 0)"}</label>
            <div class="col-sm-9">
                <input id="securitycenter_keyexpiry" type="text" class="form-control" name="keyexpiry" value="{$modvars.ZConfig.keyexpiry|safetext}" size="10" maxlength="15" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text="Bind authkey to user agent ('UserAgent')"}</label>
            <div class="col-sm-9">
                <div id="securitycenter_sessionauthkeyua">
                    <input id="sessionauthkeyua1" type="radio" name="sessionauthkeyua" value="1"{if $modvars.ZConfig.sessionauthkeyua eq 1} checked="checked"{/if} />
                    <label for="sessionauthkeyua1">{gt text='Yes'}</label>
                    <input id="sessionauthkeyua0" type="radio" name="sessionauthkeyua" value="0"{if $modvars.ZConfig.sessionauthkeyua ne 1} checked="checked"{/if} />
                    <label for="sessionauthkeyua0">{gt text='No'}</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_secure_domain">{gt text='Secure host name'}</label>
            <div class="col-sm-9">
                <input id="securitycenter_secure_domain" type="text" class="form-control" name="secure_domain" value="{$modvars.ZConfig.secure_domain|safetext}" size="50" maxlength="100" />
                <p class="help-block alert alert-info">{gt text="Notice: If you use a different host name for HTTPS secure sessions and you insert an address in the 'Secure host name' box, make sure you include a trailing slash at the end of the address."}</p>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Cookies settings'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Sign cookies'}</label>
            <div class="col-sm-9">
                <div id="securitycenter_signcookies">
                    <input id="securitycenter_signcookies_yes" type="radio" name="signcookies" value="1"{if $modvars.ZConfig.signcookies eq 1} checked="checked"{/if} />
                    <label for="securitycenter_signcookies_yes">{gt text='Yes'}</label>
                    <input id="securitycenter_signcookies_no" type="radio" name="signcookies" value="0"{if $modvars.ZConfig.signcookies ne 1} checked="checked"{/if} />
                    <label for="securitycenter_signcookies_no">{gt text='No'}</label>
                </div>
            </div>
        </div>
        <div data-switch="signcookies" data-switch-value="1" class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_signingkey">{gt text='Signing key'}</label>
            <div class="col-sm-9">
                <input id="securitycenter_signingkey" name="signingkey" type="text" class="form-control" value="{$modvars.ZConfig.signingkey|safetext}" size="50" maxlength="100" />
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Session settings'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_seclevel">{gt text='Security level'} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime">(?)</a></label>
            <div class="col-sm-9">
                <select class="form-control" id="securitycenter_seclevel" name="seclevel" size="1">
                    <option value="High"{if $modvars.ZConfig.seclevel eq 'High'} selected="selected"{/if}>{gt text='High (user is logged-out after X minutes of inactivity)'}</option>
                    <option value="Medium"{if $modvars.ZConfig.seclevel eq 'Medium'} selected="selected"{/if}>{gt text="Medium (user is logged-out after X minutes of inactivity, unless 'Remember me' checkbox is activated during log-in)"}</option>
                    <option value="Low"{if $modvars.ZConfig.seclevel eq 'Low'} selected="selected"{/if}>{gt text='Low (user stays logged-in until he logs-out)'}</option>
                </select>
            </div>
        </div>
        <div data-switch="seclevel" data-switch-value="Medium" class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_secmeddays">{gt text='Automatically log user out after'}</label>
            <div class="col-sm-9">
                <input id="securitycenter_secmeddays" type="text" class="form-control" name="secmeddays" value="{$modvars.ZConfig.secmeddays|safetext}" size="4" />
                <em>{gt text="days (if 'Remember me' is activated)"}</em>
            </div>
        </div>
        <div data-switch="seclevel" data-switch-value="Medium,High"  class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_secinactivemins">{gt text='Expire session after'} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime">(?)</a></label>
            <div class="col-sm-9">
                <input id="securitycenter_secinactivemins" type="text" class="form-control" name="secinactivemins" value="{$modvars.ZConfig.secinactivemins|safetext}" size="4" />
                <em>{gt text='minutes of inactivity'}</em>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Store sessions'}</label>
            <div class="col-sm-9">
                <div id="securitycenter_sessionstoretofile">
                    <input id="securitycenter_sessionstoretofile_file" type="radio" name="sessionstoretofile" value="1"{if $modvars.ZConfig.sessionstoretofile eq 1} checked="checked"{/if} />
                    <label for="securitycenter_sessionstoretofile_file">{gt text='File'}</label>
                    <input id="securitycenter_sessionstoretofile_directory" type="radio" name="sessionstoretofile" value="0"{if $modvars.ZConfig.sessionstoretofile ne 1} checked="checked"{/if} />
                    <label for="securitycenter_sessionstoretofile_directory">{gt text='Database (recommended)'}</label>
                </div>
                <p class="help-block alert alert-info">{gt text='Notice: If you change this setting, you will be logged-out immediately and will have to log back in again.'}</p>
            </div>
        </div>

        <div data-switch="sessionstoretofile" data-switch-value="1" class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_sessionsavepath">{gt text='Path for saving session files'} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.save-path">(?)</a></label>
            <div class="col-sm-9">
                <input id="securitycenter_sessionsavepath" type="text" class="form-control" name="sessionsavepath" size="50" value="{$modvars.ZConfig.sessionsavepath|safetext}" />
                <p class="help-block alert alert-info">{gt text="Notice: If you change 'Where to save sessions' to 'File' then you must enter a path in the 'Path for saving session files' box above. The path must be writeable."}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_gc_probability">{gt text='Garbage collection probability'} <a href="http://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability">(?)</a></label>
            <div class="col-sm-9">
                <div class="input-group">
                    <input id="securitycenter_gc_probability" type="text" class="form-control" name="gc_probability" value="{$modvars.ZConfig.gc_probability|safetext}" size="4" maxlength="5" />
                    <span class="input-group-addon">{gt text='/10000'}</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='CSRF Token'}</label>
            <div class="col-sm-9">
                <div id="securitycenter_sessioncsrftokenonetime">
                    <input id="securitycenter_sessioncsrftokenonetime_persession" type="radio" name="sessioncsrftokenonetime" value="1"{if $modvars.ZConfig.sessioncsrftokenonetime eq 1} checked="checked"{/if} />
                    <label for="securitycenter_sessioncsrftokenonetime_persession">{gt text='Per session'}</label>
                    <input id="securitycenter_sessioncsrftokenonetime_onetime" type="radio" name="sessioncsrftokenonetime" value="0"{if $modvars.ZConfig.sessioncsrftokenonetime ne 1} checked="checked"{/if} />
                    <label for="securitycenter_sessioncsrftokenonetime_onetime">{gt text='One time use'}</label>
                </div>
                <p class="help-block alert alert-info">{gt text='One time CSRF protection may affect the browser back button but is more secure.'}</p>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Use sessions for anonymous guests'}</label>
            <div class="col-sm-9">
            <div id="securitycenter_anonymoussessions">
                <input id="anonymoussessions1" type="radio" name="anonymoussessions" value="1"{if $modvars.ZConfig.anonymoussessions eq 1} checked="checked"{/if} />
                <label for="anonymoussessions1">{gt text='Yes'}</label>
                <input id="anonymoussessions0" type="radio" name="anonymoussessions" value="0"{if $modvars.ZConfig.anonymoussessions ne 1} checked="checked"{/if} />
                <label for="anonymoussessions0">{gt text='No'}</label>
            </div>
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Periodically regenerate session ID'}</label>
            <div class="col-sm-9">
            <div id="securitycenter_sessionrandregenerate">
                <input id="sessionrandregenerate1" type="radio" name="sessionrandregenerate" value="1"{if $modvars.ZConfig.sessionrandregenerate eq 1} checked="checked"{/if} />
                <label for="sessionrandregenerate1">{gt text='Yes'}</label>
                <input id="sessionrandregenerate0" type="radio" name="sessionrandregenerate" value="0"{if $modvars.ZConfig.sessionrandregenerate ne 1} checked="checked"{/if} />
                <label for="sessionrandregenerate0">{gt text='No'}</label>
            </div>
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Regenerate session ID during log-in and log-out'}</label>
            <div class="col-sm-9">
            <div id="securitycenter_sessionregenerate">
                <input id="sessionregenerate1" type="radio" name="sessionregenerate" value="1"{if $modvars.ZConfig.sessionregenerate eq 1} checked="checked"{/if} />
                <label for="sessionregenerate1">{gt text='Yes'}</label>
                <input id="sessionregenerate0" type="radio" name="sessionregenerate" value="0"{if $modvars.ZConfig.sessionregenerate ne 1} checked="checked"{/if} />
                <label for="sessionregenerate0">{gt text='No'}</label>
            </div>
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_sessionregeneratefreq">{gt text='Regeneration probability'}</label>
            <div class="col-sm-9">
                <div class="input-group">
                    <input id="securitycenter_sessionregeneratefreq" type="text" class="form-control" name="sessionregeneratefreq" value="{$modvars.ZConfig.sessionregeneratefreq|safetext}" size="3" maxlength="3" />
                    <span class="input-group-addon">{gt text='% (0 to disable)'}</span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='IP checks on session (may cause problems for AOL users)'}</label>
            <div class="col-sm-9">
            <div id="securitycenter_sessionipcheck">
                <input id="sessionipcheck1" type="radio" name="sessionipcheck" value="1"{if $modvars.ZConfig.sessionipcheck eq 1} checked="checked"{/if} />
                <label for="sessionipcheck1">{gt text='Yes'}</label>
                <input id="sessionipcheck0" type="radio" name="sessionipcheck" value="0"{if $modvars.ZConfig.sessionipcheck ne 1} checked="checked"{/if} />
                <label for="sessionipcheck0">{gt text='No'}</label>
            </div>
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_sessionname">{gt text='Session cookie name'}</label>
            <div class="col-sm-9">
                <input id="securitycenter_sessionname" type="text" class="form-control" name="sessionname" value="{$modvars.ZConfig.sessionname|safetext}" size="20" />
                <p class="help-block alert alert-warning">{gt text="Notice: If you change the 'Session cookie name' setting, all registered users who are currently logged-in will then be logged-out automatically, and they will have to log back in again."}</p>
            </div>
        </div>
    </fieldset>
    <fieldset id="securitycenter_ids">
        <legend>{gt text='Intrusion Detection System'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label">{gt text='Use PHPIDS'}</label>
            <div class="col-sm-9">
                <div id="securitycenter_useids">
                    <input id="useidsyes" type="radio" name="useids" value="1"{if $modvars.ZConfig.useids eq 1} checked="checked"{/if} />
                    <label for="useidsyes">{gt text='Yes'}</label>
                    <input id="useidsno" type="radio" name="useids" value="0"{if $modvars.ZConfig.useids neq 1} checked="checked"{/if} />
                    <label for="useidsno">{gt text='No'}</label>
                </div>
            </div>
        </div>

        <div data-switch="useids" data-switch-value="1">
            <p class="col-sm-offset-3 col-sm-9 help-block alert alert-info">
                {gt text='PHPIDS performs many different checks which return an impact value for scoring the treated request. Depending on the sum of all impacts performed actions are selected.'}
                {gt text='Read more about this system on the <a href="http://phpids.org" title="PHPIDS homepage">PHPIDS homepage</a>.'}
            </p>
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='Block action'}</label>
                <div class="col-sm-9" id="securitycenter_idssoftblock">
                    <input id="idssoftblockyes" type="radio" name="idssoftblock" value="1"{if $modvars.ZConfig.idssoftblock eq 1} checked="checked"{/if} />
                    <label for="idssoftblockyes">{gt text='Warn only'}</label>
                    <input id="idssoftblockno" type="radio" name="idssoftblock" value="0"{if $modvars.ZConfig.idssoftblock neq 1} checked="checked"{/if} />
                    <label for="idssoftblockno">{gt text='Block'}</label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='Send email on block action'}</label>
                <div class="col-sm-9">
                    <div id="securitycenter_idsmail">
                        <input id="idsmailyes" type="radio" name="idsmail" value="1"{if $modvars.ZConfig.idsmail eq 1} checked="checked"{/if} />
                        <label for="idsmailyes">{gt text='Yes'}</label>
                        <input id="idsmailno" type="radio" name="idsmail" value="0"{if $modvars.ZConfig.idsmail neq 1} checked="checked"{/if} />
                        <label for="idsmailno">{gt text='No'}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_idsfilter">{gt text='Select filter rules to use'}</label>
                <div class="col-sm-9">
                    <select class="form-control" id="securitycenter_idsfilter" name="idsfilter">
                        <option value="xml"{if $modvars.ZConfig.idsfilter ne "json"} selected="selected"{/if}>{gt text='XML'}</option>
                        <option value="json"{if $modvars.ZConfig.idsfilter eq "json"} selected="selected"{/if}>{gt text='JSON'}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_idsrulepath">{gt text='IDS Rule path'}</label>
                <div class="col-sm-9">
                    <input id="securitycenter_idsrulepath" type="text" class="form-control" name="idsrulepath" size="3" value="{$modvars.ZConfig.idsrulepath|safetext}" />
                    {gt text='Default: config/phpids_zikula_default.xml'}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_impactthresholdone">{gt text='Minimum impact to log intrusion in the database'}</label>
                <div class="col-sm-9">
                    <input id="securitycenter_impactthresholdone" type="text" class="form-control" name="idsimpactthresholdone" size="3" value="{$modvars.ZConfig.idsimpactthresholdone|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_impactthresholdtwo">{gt text='Minimum impact to email the administrator'}</label>
                <div class="col-sm-9">
                    <input id="securitycenter_impactthresholdtwo" type="text" class="form-control" name="idsimpactthresholdtwo" size="3" value="{$modvars.ZConfig.idsimpactthresholdtwo|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_impactthresholdthree">{gt text='Minimum impact to block the request'}</label>
                <div class="col-sm-9">
                    <input id="securitycenter_impactthresholdthree" type="text" class="form-control" name="idsimpactthresholdthree" size="3" value="{$modvars.ZConfig.idsimpactthresholdthree|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_impactthresholdfour">{gt text='Minimum impact to kick the user (destroy the session)'}</label>
                <div class="col-sm-9">
                    <input id="securitycenter_impactthresholdfour" type="text" class="form-control" name="idsimpactthresholdfour" size="3" value="{$modvars.ZConfig.idsimpactthresholdfour|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_impactmode">{gt text='Select the way the impact thresholds are used in Zikula'}</label>
                <div class="col-sm-9">
                    <select class="form-control" id="securitycenter_impactmode" name="idsimpactmode">
                        <option value="1"{if $modvars.ZConfig.idsimpactmode ne 2 && $modvars.ZConfig.idsimpactmode ne 3} selected="selected"{/if}>{gt text='React on impact per request (uses the values from above)'}</option>
                        <option value="2"{if $modvars.ZConfig.idsimpactmode eq 2} selected="selected"{/if}>{gt text='React on impact sum per session [loose] (uses the values from above * 10)'}</option>
                        <option value="3"{if $modvars.ZConfig.idsimpactmode eq 3} selected="selected"{/if}>{gt text='React on impact sum per session [strict] (uses the values from above * 5)'}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_idshtmlfields">{gt text='Define which fields contain HTML and need preparation'}</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="securitycenter_idshtmlfields" name="idshtmlfields" cols="50" rows="8">{$idshtmlfields|safetext}</textarea>
                    <em class="help-block sub">{gt text='(Place each value on a separate line.)'}</em>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_idsjsonfields">{gt text='Define which fields contain JSON data and should be treated as such'}</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="securitycenter_idsjsonfields" name="idsjsonfields" cols="50" rows="8">{$idsjsonfields|safetext}</textarea>
                    <em class="help-block sub">{gt text='(Place each value on a separate line.)'}</em>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="securitycenter_idsexceptions">{gt text='Define which fields should not be monitored'}</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="securitycenter_idsexceptions" name="idsexceptions" cols="50" rows="8">{$idsexceptions|safetext}</textarea>
                    <em class="help-block sub">{gt text='(Place each value on a separate line.)'}</em>
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='Output filter settings'}</legend>
        <div class="form-group">
            <label class="col-sm-3 control-label" for="securitycenter_outputfilter">{gt text='Select output filter'}</label>
            <div class="col-sm-9">
                <select class="form-control" id="securitycenter_outputfilter" name="outputfilter">
                    <option value="0"{if $modvars.ZConfig.outputfilter eq 0} selected="selected"{/if}>{gt text='Use internal output filter only'}</option>
                    <option value="1"{if $modvars.ZConfig.outputfilter eq 1} selected="selected"{/if}>{gt text="Use 'HTML Purifier' + internal mechanism as output filter"}</option>
                </select>
            </div>
        </div>
    </fieldset>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
            <a class="btn btn-danger" href="{route name='zikulasecuritycentermodule_admin_index'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>
{adminfooter}
