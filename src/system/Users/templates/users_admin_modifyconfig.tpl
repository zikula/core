{ajaxheader modname='Users' filename='users_admin_modifyconfig.js' noscriptaculous=true effects=true}
{include file='users_admin_menu.tpl'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=configure.png set=icons/large __alt='Settings'}</div>

    <h2>{gt text="Settings"}</h2>

    <form class="z-form" id="users_modifyconfig_form" action="{modurl modname='Users' type='admin' func='updateconfig'}" method="post">
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="users_anonymous">{gt text="Name displayed for anonymous user"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_anonymous"{if isset($errorFields.users_anonymous)} class="z-form-error"{/if} type="text" name="config[anonymous]" value="{$config.anonymous|safehtml}" size="20" maxlength="20" />
                <em class="z-formnote z-sub">{gt text="Anonymous users are visitors to your site who have not logged in."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_itemsperpage">{gt text="Number of items displayed per page"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_itemsperpage"{if isset($errorFields.users_itemsperpage)} class="z-form-error"{/if} type="text" name="config[itemsperpage]" size="3" value="{$config.itemsperpage|safetext}" />
                <em class="z-formnote z-sub">{gt text="When lists are displayed (for example, lists of users, lists of registrations) this option controls how many items are displayed at one time."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_avatarpath">{gt text="Path to user's avatar images"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_avatarpath"{if isset($errorFields.users_avatarpath)} class="z-form-error"{/if} type="text" name="config[avatarpath]" value="{$config.avatarpath|default:'images/avatar'|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_allowgravatars">{gt text="Allow globally recognized avatars"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_allowgravatars">
                    <input id="allowgravatarsyes" type="radio" name="config[allowgravatars]" value="1" {if $config.allowgravatars eq 1} checked="checked"{/if} />
                    <label for="allowgravatarsyes">{gt text="Yes"}</label>
                    <input id="allowgravatarsno" type="radio" name="config[allowgravatars]" value="0" {if $config.allowgravatars eq 0} checked="checked"{/if} />
                    <label for="allowgravatarsno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_gravatarimage">{gt text="Default gravatar image"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_gravatarimage"{if isset($errorFields.users_anonymous)} class="z-form-error"{/if} type="text" name="config[gravatarimage]" value="{$config.gravatarimage|default:'gravatar.png'|safetext}" size="50" maxlength="255" />
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Account page settings"}</legend>
            <div class="z-formrow">
                <label for="users_accountdisplaygraphics">{gt text="Display graphics on user's account page"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_accountdisplaygraphics">
                    <input id="users_accountdisplaygraphics_yes" type="radio" name="config[accountdisplaygraphics]" value="1" {if $config.accountdisplaygraphics eq 1}checked="checked" {/if} />
                    <label for="users_accountdisplaygraphics_yes">{gt text="Yes"}</label>
                    <input id="users_accountdisplaygraphics_no" type="radio" name="config[accountdisplaygraphics]" value="0" {if $config.accountdisplaygraphics neq 1}checked="checked" {/if} />
                    <label for="users_accountdisplaygraphics_no">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_userimg">{gt text="Path to account page images"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_userimg"{if isset($errorFields.users_userimg)} class="z-form-error"{/if} type="text" name="config[userimg]" value="{$config.userimg|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_accountitemsperpage">{gt text="Number of links per page"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_accountitemsperpage"{if isset($errorFields.users_accountitemsperpage)} class="z-form-error"{/if} type="text" name="config[accountitemsperpage]" size="3" value="{$config.accountitemsperpage|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="users_accountitemsperrow">{gt text="Number of links per row"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_accountitemsperrow"{if isset($errorFields.users_accountitemsperrow)} class="z-form-error"{/if} type="text" name="config[accountitemsperrow]" size="3" value="{$config.accountitemsperrow|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="users_changepassword">{gt text="Users module handles password maintenance"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_changepassword">
                    <input id="changepasswordyes" type="radio" name="config[changepassword]" value="1" {if $config.changepassword eq 1} checked="checked"{/if} />
                    <label for="changepasswordyes">{gt text="Yes"}</label>
                    <input id="changepasswordno" type="radio" name="config[changepassword]" value="0" {if $config.changepassword eq 0} checked="checked"{/if} />
                    <label for="changepasswordno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_changeemail">{gt text="Users module handles e-mail address maintenance"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_changeemail">
                    <input id="changeemailyes" type="radio" name="config[changeemail]" value="1" {if $config.changeemail eq 1} checked="checked"{/if} />
                    <label for="changeemailyes">{gt text="Yes"}</label>
                    <input id="changeemailno" type="radio" name="config[changeemail]" value="0" {if $config.changeemail eq 0} checked="checked"{/if} />
                    <label for="changeemailno">{gt text="No"}</label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User credential settings"}</legend>
            <div class="z-formrow">
                <label for="users_loginviaoption">{gt text="Credential required for user log-in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_loginviaoption">
                    <input id="users_loginviausername" type="radio" name="config[loginviaoption]" value="0" {if $config.loginviaoption eq 0}checked="checked" {/if}/>
                    <label for="users_loginviausername">{gt text="User name"}</label>
                    <input id="users_loginviaemail" type="radio" name="config[loginviaoption]" value="1" {if $config.loginviaoption neq 0}checked="checked" {/if}/>
                    <label for="users_loginviaemail">{gt text="E-mail address"}</label>
                    <div class="z-formnote z-warningmsg">{gt text="Notice: If the 'Credential required for user log-in' is set to 'E-mail address', then the 'New e-mail addresses must be unique' option below must be set to 'Yes'."}</div>
                    <div class="z-formnote z-warningmsg">{gt text="Notice: If the 'New e-mail addresses must be unique' option was set to 'no' at some point, then user accounts with duplicate e-mail addresses might exist in the system. They will experience difficulties logging in if this option is set to 'e-mail address'."}</div>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_reg_uniemail">{gt text="New e-mail addresses must be unique"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_reg_uniemail">
                    <input id="reg_uniemailyes" type="radio" name="config[reg_uniemail]" value="1" {if $config.reg_uniemail eq 1} checked="checked"{/if} />
                    <label for="reg_uniemailyes">{gt text="Yes"}</label>
                    <input id="reg_uniemailno" type="radio" name="config[reg_uniemail]" value="0" {if $config.reg_uniemail eq 0} checked="checked"{/if} />
                    <label for="reg_uniemailno">{gt text="No"}</label>
                </div>
                <em class="z-formnote z-sub">{gt text="If set to yes, then e-mail addresses entered for new registrations and for e-mail address change requests cannot already be in use by another user account or registration."}</em>
                <div class="z-formnote z-warningmsg">{gt text="Notice: If this option was set to 'no' at some point, then user accounts or registrations with duplicate e-mail addresses might exist in the system. Setting this option to 'yes' will not affect those accounts or registrations."}</div>
            </div>
            <div class="z-formrow">
                <label for="users_minpass">{gt text="Minimum length for user passwords"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_minpass"{if isset($errorFields.users_minpass)} class="z-form-error"{/if} type="text" name="config[minpass]" value="{$config.minpass|safehtml}" size="2" maxlength="2" />
                <em class="z-formnote z-sub">{gt text="This affects both passwords created during registration, as well as passwords modified by users or administrators."} {gt text="Enter an integer greater than zero."}</em>
            </div>
            <div class="z-formrow">
                <label for="hash_method">{gt text="Password hashing method"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <select id="hash_method" name="config[hash_method]">
                    <option value="sha1" {if $config.hash_method eq 'sha1'} selected="selected"{/if}>SHA1</option>
                    <option value="sha256" {if $config.hash_method eq 'sha256'} selected="selected"{/if}>SHA256</option>
                </select>
                <em class="z-formnote z-sub">{gt text="The default hashing method is 'SHA256'."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_use_password_strength_meter">{gt text="Show password strength meter"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_use_password_strength_meter">
                    <input id="use_password_strength_meter_yes" type="radio" name="config[use_password_strength_meter]" value="1" {if $config.use_password_strength_meter eq 1}checked="checked" {/if} />
                    <label for="use_password_strength_meter_yes">{gt text="Yes"}</label>
                    <input id="use_password_strength_meter_no" type="radio" name="config[use_password_strength_meter]" value="0" {if $config.use_password_strength_meter neq 1}checked="checked" {/if} />
                    <label for="use_password_strength_meter_no">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_chgemail_expiredays">{gt text="E-mail address verifications expire in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div>
                    <input id="users_chgemail_expiredays"{if isset($errorFields.chgemail_expiredays)} class="z-form-error"{/if} type="text" name="config[chgemail_expiredays]" value="{$config.chgemail_expiredays|default:0}" maxlength="3" />
                    <label for="users_chgemail_expiredays">{gt text="days"}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="Enter the number of days a user's request to change e-mail addresses should be kept while waiting for verification. Enter zero (0) for no expiration."}</em>
                <div class="z-warningmsg z-formnote">{gt text="Changing this setting will affect all requests to change e-mail addresses currently pending verification."}</div>
            </div>
            <div class="z-formrow">
                <label for="users_chgpass_expiredays">{gt text="Password reset requests expire in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div>
                    <input id="users_chgpass_expiredays"{if isset($errorFields.chgpass_expiredays)} class="z-form-error"{/if} type="text" name="config[chgpass_expiredays]" value="{$config.chgpass_expiredays|default:0}" maxlength="3" />
                    <label for="users_chgpass_expiredays">{gt text="days"}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="This setting only affects users who have not established security question responses. Enter the number of days a user's request to reset a password should be kept while waiting for verification. Enter zero (0) for no expiration."}</em>
                <div class="z-warningmsg z-formnote">{gt text="Changing this setting will affect all password change requests currently pending verification."}</div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Registration settings"}</legend>
            {if !$legal}
            <div class="z-formrow">
                <div class="z-informationmsg">{gt text="Attention: It is possible to force users to accept a 'terms of use' and/or a 'privacy policy', but the Legal module must be installed and available."}</div>
            </div>
            {/if}
            <div class="z-formrow">
                <label for="users_reg_allowreg">{gt text="Allow new user account registrations"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_reg_allowreg">
                    <input id="users_reg_allowregyes" type="radio" name="config[reg_allowreg]" value="1" {if $config.reg_allowreg eq 1} checked="checked"{/if} />
                    <label for="users_reg_allowregyes">{gt text="Yes"}</label>
                    <input id="users_reg_allowregno" type="radio" name="config[reg_allowreg]" value="0" {if $config.reg_allowreg eq 0} checked="checked"{/if} />
                    <label for="users_reg_allowregno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow" id="users_reg_allowreg_wrap">
                <label for="users_noregreasons">{gt text="Statement displayed if registration disabled"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <textarea id="users_noregreasons" name="config[reg_noregreasons]" cols="45" rows="10">{$config.reg_noregreasons|safehtml}</textarea>
            </div>
            <div class="z-formrow">
                <label for="users_reg_notifyemail">{gt text="E-mail address to notify of registrations"}</label>
                <input id="users_reg_notifyemail" type="text" name="config[reg_notifyemail]" value="{$config.reg_notifyemail|safetext}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="A notification is sent to this e-mail address for each registration. Leave blank for no notifications."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_minage">{gt text="Minimum age permitted to register"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <input id="users_minage"{if isset($errorFields.users_minage)} class="z-form-error"{/if} type="text" name="config[minage]" value="{$config.minage|safetext}" size="2" maxlength="2" />
                <em class="z-formnote z-sub">{gt text="Enter a positive integer, or 0 for no age check."}</em>
            </div>
            {if $profile}
            <div class="z-formrow">
                <label for="users_reg_optitems">{gt text="Show profile module properties"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_reg_optitems">
                    <input id="reg_optitemsyes" type="radio" name="config[reg_optitems]" value="1" {if $config.reg_optitems eq 1} checked="checked"{/if} />
                    <label for="reg_optitemsyes">{gt text="Yes"}</label>
                    <input id="reg_optitemsno" type="radio" name="config[reg_optitems]" value="0" {if $config.reg_optitems eq 0} checked="checked"{/if} />
                    <label for="reg_optitemsno">{gt text="No"}</label>
                </div>
            </div>
            {/if}
            <div class="z-formrow">
                <label for="users_moderation">{gt text="User registration is moderated"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="users_moderation">
                    <input id="users_moderationyes" type="radio" name="config[moderation]" value="1" {if $config.moderation eq 1} checked="checked"{/if} />
                    <label for="users_moderationyes">{gt text="Yes"}</label>
                    <input id="users_moderationno" type="radio" name="config[moderation]" value="0" {if $config.moderation eq 0} checked="checked"{/if} />
                    <label for="users_moderationno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow" id="users_reg_verifyemail">
                <label>{gt text="Verify e-mail address during registration"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="users_reg_verifyemail2" type="radio" name="config[reg_verifyemail]" value="2" {if ($config.reg_verifyemail eq 2) || $config.reg_verifyemail eq 1} checked="checked"{/if} />
                    <label for="users_reg_verifyemail2">{gt text="Yes. User chooses password, then activates account via e-mail"}</label>
                </div>
                <div class="z-formlist">
                    <input id="users_reg_verifyemail0" type="radio" name="config[reg_verifyemail]" value="0" {if $config.reg_verifyemail eq 0} checked="checked"{/if}/>
                    <label for="users_reg_verifyemail0">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_reg_expiredays">{gt text="Registrations pending verification expire in"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div>
                    <input id="users_reg_expiredays"{if isset($errorFields.reg_expiredays)} class="z-form-error"{/if} type="text" name="config[reg_expiredays]" value="{$config.reg_expiredays|default:0}" maxlength="3" />
                    <label for="users_reg_expiredays">{gt text="days"}</label>
                </div>
                <em class="z-sub z-formnote">{gt text="Enter the number of days a registration record should be kept while waiting for e-mail address verification. (Unverified registrations will be deleted the specified number of days after sending an e-mail verification message.) Enter zero (0) for no expiration (no automatic deletion)."}</em>
                <div class="z-informationmsg z-formnote">{gt text="If registration is moderated and applications must be approved before verification, then registrations will not expire until the specified number of days after approval."}</div>
                <div class="z-warningmsg z-formnote">{gt text="Changing this setting will affect all registrations currently pending e-mail address verification."}</div>
            </div>
            <div class="z-formrow" id="users_moderation_order_wrap">
                <label>{gt text="Order that approval and verification occur"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="users_moderation_order0" type="radio" name="config[moderation_order]" value="0" {if $config.moderation_order eq 0} checked="checked"{/if} />
                    <label for="users_moderation_order0">{gt text="Registration applications must be approved before users verify their e-mail address."}</label>
                </div>
                <div class="z-formlist">
                    <input id="users_moderation_order1" type="radio" name="config[moderation_order]" value="1" {if $config.moderation_order eq 1} checked="checked"{/if} />
                    <label for="users_moderation_order1">{gt text="Users must verify their e-mail address before their application is approved."}</label>
                </div>
                <div class="z-formlist">
                    <input id="users_moderation_order2" type="radio" name="config[moderation_order]" value="2" {if $config.moderation_order eq 2} checked="checked"{/if} />
                    <label for="users_moderation_order2">{gt text="Application approval and e-mail address verification can occur in any order."}</label>
                </div>
            </div>

            {if $legal}
            <div class="z-formrow">
                <label>{gt text="User has to accept the 'Terms of use'"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="legal_termsofuse">
                    <input id="legal_termsofuseyes" type="radio" name="config[termsofuse]" value="1" {if $tou_active} checked="checked"{/if} />
                    <label for="legal_termsofuseyes">{gt text="Yes"}</label>
                    <input id="legal_termsofuseno" type="radio" name="config[termsofuse]" value="0" {if !$tou_active} checked="checked"{/if} />
                    <label for="legal_termsofuseno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label>{gt text="User has to accept the 'Privacy policy'"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="legal_privacypolicy">
                    <input id="legal_privacypolicyyes" type="radio" name="config[privacypolicy]" value="1" {if $pp_active} checked="checked"{/if} />
                    <label for="legal_privacypolicyyes">{gt text="Yes"}</label>
                    <input id="legal_privacypolicyno" type="radio" name="config[privacypolicy]" value="0" {if !$pp_active} checked="checked"{/if} />
                    <label for="legal_privacypolicyno">{gt text="No"}</label>
                </div>
            </div>
            {/if}

            <div class="z-formrow">
                <label for="users_reg_question">{gt text="Spam protection question"}</label>
                <input id="users_reg_question" name="config[reg_question]" value="{$config.reg_question|safehtml}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="You can set a question to be answered at registration time, to protect the site against spam automated registrations by bots and scripts."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_reg_answer">{gt text="Spam protection answer"}<span id="users_reg_answer_mandatory" class="z-form-mandatory-flag z-hide">{gt text="*"}</span></label>
                <input id="users_reg_answer"{if $errorFields.users_reg_answer} class="z-form-error"{/if} name="config[reg_answer]" value="{if !empty($config.reg_question)}{$config.reg_answer|safehtml}{/if}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="Registering users will have to provide this response when answering the spam protection question. It is required if a spam protection question is provided."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_reg_Illegalusername">{gt text="Reserved user names"}</label>
                <input id="users_reg_Illegalusername" type="text" name="config[reg_Illegalusername]" value="{$config.reg_Illegalusername|safetext}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">
                    {gt text="Separate each user name with a space."}<br />
                    {gt text="Each user name on this list is not allowed to be chosen by someone registering for a new account."}
                </em>
            </div>
            <div class="z-formrow">
                <label for="users_reg_Illegaluseragents">{gt text="Banned user agents"}</label>
                <textarea id="users_reg_Illegaluseragents" name="config[reg_Illegaluseragents]" cols="45" rows="2">{$config.reg_Illegaluseragents|safehtml}</textarea>
                <em class="z-formnote z-sub">
                    {gt text="Separate each user agent string with a comma."}<br />
                    {gt text="Each item on this list is a browser user agent identification string. If a user attempts to register a new account using a browser whose user agent string begins with one on this list, then the user is not allowed to begin the registration process."}
                </em>
            </div>
            <div class="z-formrow">
                <label for="users_reg_Illegaldomains">{gt text="Banned e-mail address domains"}</label>
                <textarea id="users_reg_Illegaldomains" name="config[reg_Illegaldomains]" cols="45" rows="2">{$config.reg_Illegaldomains|safehtml}</textarea>
                <em class="z-formnote z-sub">
                    {gt text="Separate each domain with a comma."}<br />
                    {gt text="Each item on this list is an e-mail address domain (the part after the '@'). E-mail addresses on new registrations or on an existing user's change of e-mail address requests are not allowed to have any domain on this list."}
                </em>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User log-in settings"}</legend>
            <div class="z-formrow">
                <label>{gt text="WCAG-compliant log-in and log-out"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div id="login_redirect">
                    <input id="login_redirectyes" type="radio" name="config[login_redirect]" value="1" {if $config.login_redirect eq 1}checked="checked" {/if}/>
                    <label for="login_redirectyes">{gt text="Yes"}</label>
                    <input id="login_redirectno" type="radio" name="config[login_redirect]" value="0" {if $config.login_redirect neq 1}checked="checked" {/if}/>
                    <label for="login_redirectno">{gt text="No"}</label>
                    <em class="z-sub">{gt text="Notice: Uses meta refresh."}</em>
                </div>
            </div>
            <div class="z-formrow">
                <label>{gt text="Failed login displays inactive status"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="users_login_displayinactive_yes" type="radio" name="config[login_displayinactive]" value="1" {if $config.login_displayinactive eq 1} checked="checked"{/if} />
                    <label for="users_login_displayinactive_yes">{gt text="Yes. The log-in error message will indicate that the user account is inactive."}</label>
                </div>
                <div class="z-formlist">
                    <input id="users_login_displayinactive_no" type="radio" name="config[login_displayinactive]" value="0" {if $config.login_displayinactive eq 0} checked="checked"{/if}/>
                    <label for="users_login_displayinactive_no">{gt text="No. A generic error message is displayed."}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label>{gt text="Failed login displays verification status"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="users_login_displayverify_yes" type="radio" name="config[login_displayverify]" value="1" {if $config.login_displayverify eq 1} checked="checked"{/if} />
                    <label for="users_login_displayverify_yes">{gt text="Yes. The log-in error message will indicate that the registration is pending verification."}</label>
                </div>
                <div class="z-formlist">
                    <input id="users_login_displayverify_no" type="radio" name="config[login_displayverify]" value="0" {if $config.login_displayverify eq 0} checked="checked"{/if}/>
                    <label for="users_login_displayverify_no">{gt text="No. A generic error message is displayed."}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label>{gt text="Failed login displays approval status"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <div class="z-formlist">
                    <input id="users_login_displayapproval_yes" type="radio" name="config[login_displayapproval]" value="1" {if $config.login_displayapproval eq 1} checked="checked"{/if} />
                    <label for="users_login_displayapproval_yes">{gt text="Yes. The log-in error message will indicate that the registration is pending approval."}</label>
                </div>
                <div class="z-formlist">
                    <input id="users_login_displayapproval_no" type="radio" name="config[login_displayapproval]" value="0" {if $config.login_displayapproval eq 0} checked="checked"{/if}/>
                    <label for="users_login_displayapproval_no">{gt text="No. A generic error message is displayed."}</label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Authentication Module settings"}</legend>
            <div class="z-formrow">
                <label for="users_default_authmodule">{gt text="Default user authentication (login) module"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                <select id="users_default_authmodule" name="config[default_authmodule]">
                    {foreach from=$authmodules item='authmodule'}
                    <option id="users_default_authmodule_{$authmodule.name}" value="{$authmodule.name}"{if $config.default_authmodule == $authmodule.name} selected="selected"{/if}>{$authmodule.displayname}</option>
                    {foreachelse}
                    <option id="users_default_authmodule_Users" value="Users" selected="selected">Users manager</option>
                    {/foreach}
                </select>
            </div>
        </fieldset>

        <div class="z-formbuttons z-buttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Users type=admin func=view}" title="{gt text='Cancel'}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
        </div>
    </form>
</div>
