{ajaxheader modname='Users' filename='users_admin_modifyconfig.js' noscriptaculous=true effects=true}
{include file='users_admin_menu.htm'}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=configure.gif set=icons/large __alt='Settings'}</div>

    <h2>{gt text="Settings"}</h2>

    <form class="z-form" action="{modurl modname='Users' type='admin' func='updateconfig'}" method="post">
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="users_userimg">{gt text="Path to account panel images"}</label>
                <input id="users_userimg" type="text" name="config[userimg]" value="{$config.userimg|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_avatarpath">{gt text="Path to user's avatar images"}</label>
                <input id="users_avatarpath" type="text" name="config[avatarpath]" value="{$config.avatarpath|default:'images/avatar'|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_allowgravatars">{gt text="Allow globally recognized avatars"}</label>
                <div id="users_allowgravatars">
                    <input id="allowgravatarsyes" type="radio" name="config[allowgravatars]" value="1" {if $config.allowgravatars eq 1} checked="checked"{/if} />
                    <label for="allowgravatarsyes">{gt text="Yes"}</label>
                    <input id="allowgravatarsno" type="radio" name="config[allowgravatars]" value="0" {if $config.allowgravatars eq 0} checked="checked"{/if} />
                    <label for="allowgravatarsno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_gravatarimage">{gt text="Default gravatar image"}</label>
                <input id="users_gravatarimage" type="text" name="config[gravatarimage]" value="{$config.gravatarimage|default:'gravatar.gif'|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_itemsperpage">{gt text="Links per page"}</label>
                <input id="users_itemsperpage" type="text" name="config[itemsperpage]" size="3" value="{$config.itemsperpage|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="users_minage">{gt text="Minimum age for new user registration (0 for no age check)"}</label>
                <input id="users_minage" type="text" name="config[minage]" value="{$config.minage|safetext}" size="2" maxlength="2" />
            </div>
            <div class="z-formrow">
                <label for="users_minpass">{gt text="Minimum length for user passwords"}</label>
                <input id="users_minpass" type="text" name="config[minpass]" value="{$config.minpass|safehtml}" size="2" maxlength="2" />
            </div>
            <div class="z-formrow">
                <label for="anonymous">{gt text="Name for anonymous user"}</label>
                <input id="anonymous" type="text" name="config[anonymous]" value="{$config.anonymous|safehtml}" size="20" maxlength="20" />
            </div>
            <div class="z-formrow">
                <label for="users_loginviaoption">{gt text="Credential required for user log-in"}</label>
                <div id="users_loginviaoption">
                    <input id="users_loginviausername" type="radio" name="config[loginviaoption]" value="0" {if $config.loginviaoption eq 0}checked="checked" {/if}/>
                    <label for="users_loginviausername">{gt text="User name"}</label>
                    <input id="users_loginviaemail" type="radio" name="config[loginviaoption]" value="1" {if $config.loginviaoption neq 0}checked="checked" {/if}/>
                    <label for="users_loginviaemail">{gt text="E-mail address"}</label>
                    <div class="z-formnote z-warningmsg">{gt text="Notice: If the 'Credential required for user log-in' is set to 'E-mail address', then the 'Each e-mail address only registered once' option below must be set to 'Yes'."}</div>
                </div>
            </div>
            <div class="z-formrow">
                <label for="hash_method">{gt text="Password hashing method (default: SHA256)"}</label>
                <select id="hash_method" name="config[hash_method]">
                    <option value="sha1" {if $config.hash_method eq 'sha1'} selected="selected"{/if}>SHA1</option>
                    <option value="sha256" {if $config.hash_method eq 'sha256'} selected="selected"{/if}>SHA256</option>
                </select>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User account settings"}</legend>
            <div class="z-formrow">
                <label for="users_accountdisplaygraphics">{gt text="Display graphics in user's account panel"}</label>
                <div>
                    <input id="users_accountdisplaygraphics" name="config[accountdisplaygraphics]" type="checkbox" value="1" {if $config.accountdisplaygraphics eq 1}checked="checked" {/if}/>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_accountitemsperpage">{gt text="Links per page"}</label>
                <input id="users_accountitemsperpage" type="text" name="config[accountitemsperpage]" size="3" value="{$config.accountitemsperpage|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="users_accountitemsperrow">{gt text="Links per row"}</label>
                <input id="users_accountitemsperrow" type="text" name="config[accountitemsperrow]" size="3" value="{$config.accountitemsperrow|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="users_changepassword">{gt text="Users module handles password maintenance"}</label>
                <div id="users_changepassword">
                    <input id="changepasswordyes" type="radio" name="config[changepassword]" value="1" {if $config.changepassword eq 1} checked="checked"{/if} />
                    <label for="changepasswordyes">{gt text="Yes"}</label>
                    <input id="changepasswordno" type="radio" name="config[changepassword]" value="0" {if $config.changepassword eq 0} checked="checked"{/if} />
                    <label for="changepasswordno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_changeemail">{gt text="Users module handles e-mail address maintenance"}</label>
                <div id="users_changeemail">
                    <input id="changeemailyes" type="radio" name="config[changeemail]" value="1" {if $config.changeemail eq 1} checked="checked"{/if} />
                    <label for="changeemailyes">{gt text="Yes"}</label>
                    <input id="changeemailno" type="radio" name="config[changeemail]" value="0" {if $config.changeemail eq 0} checked="checked"{/if} />
                    <label for="changeemailno">{gt text="No"}</label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User registration"}</legend>
            {if !$legal}
            <div class="z-formrow">
                <p class="z-formnote z-informationmsg">{gt text="Attention: It is possible to force users to accept a 'terms of use' and/or a 'privacy policy', but the Legal module must be installed and available."}</p>
            </div>
            {/if}
            <div class="z-formrow">
                <label for="users_reg_allowreg">{gt text="Allow new user account registrations"}</label>
                <div id="users_reg_allowreg">
                    <input id="reg_allowregyes" type="radio" name="config[reg_allowreg]" value="1" {if $config.reg_allowreg eq 1} checked="checked"{/if} />
                    <label for="reg_allowregyes">{gt text="Yes"}</label>
                    <input id="reg_allowregno" type="radio" name="config[reg_allowreg]" value="0" {if $config.reg_allowreg eq 0} checked="checked"{/if} />
                    <label for="reg_allowregno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow" id="users_reg_allowreg_wrap">
                <label for="users_noregreasons">{gt text="Statement displayed if registration disabled"}</label>
                <textarea id="users_noregreasons" name="config[reg_noregreasons]" cols="45" rows="10">{$config.reg_noregreasons|safehtml}</textarea>
            </div>
            {if $profile}
            <div class="z-formrow">
                <label for="users_reg_optitems">{gt text="Show profile module properties"}</label>
                <div id="users_reg_optitems">
                    <input id="reg_optitemsyes" type="radio" name="config[reg_optitems]" value="1" {if $config.reg_optitems eq 1} checked="checked"{/if} />
                    <label for="reg_optitemsyes">{gt text="Yes"}</label>
                    <input id="reg_optitemsno" type="radio" name="config[reg_optitems]" value="0" {if $config.reg_optitems eq 0} checked="checked"{/if} />
                    <label for="reg_optitemsno">{gt text="No"}</label>
                </div>
            </div>
            {/if}
            <div class="z-formrow">
                <label for="users_moderation">{gt text="User registration is moderated"}</label>
                <div id="users_moderation">
                    <input id="moderationyes" type="radio" name="config[moderation]" value="1" {if $config.moderation eq 1} checked="checked"{/if} />
                    <label for="moderationyes">{gt text="Yes"}</label>
                    <input id="moderationno" type="radio" name="config[moderation]" value="0" {if $config.moderation eq 0} checked="checked"{/if} />
                    <label for="moderationno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="users_reg_uniemail">{gt text="Each e-mail address can only be registered once"}</label>
                <div id="users_reg_uniemail">
                    <input id="reg_uniemailyes" type="radio" name="config[reg_uniemail]" value="1" {if $config.reg_uniemail eq 1} checked="checked"{/if} />
                    <label for="reg_uniemailyes">{gt text="Yes"}</label>
                    <input id="reg_uniemailno" type="radio" name="config[reg_uniemail]" value="0" {if $config.reg_uniemail eq 0} checked="checked"{/if} />
                    <label for="reg_uniemailno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow" id="reg_verifyemail">
                <label>{gt text="Verify e-mail address during registration"}</label>
                <div class="z-formlist">
                    <input id="reg_verifyemail2" type="radio" name="config[reg_verifyemail]" value="2" {if $config.reg_verifyemail eq 2} checked="checked"{/if} />
                    <label for="reg_verifyemail2">{gt text="Yes. User chooses password, then activates account via e-mail"}</label>
                </div>
                <div class="z-formlist">
                    <input id="reg_verifyemail0" type="radio" name="config[reg_verifyemail]" value="0" {if $config.reg_verifyemail eq 0} checked="checked"{/if}/>
                    <label for="reg_verifyemail0">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow" id="moderation_order_wrap">
                <label>{gt text="Moderation/verification order"}</label>
                <div class="z-formlist">
                    <input id="moderation_order0" type="radio" name="config[moderation_order]" value="0" {if $config.moderation_order eq 0} checked="checked"{/if} />
                    <label for="moderation_order0">{gt text="Registration applications must be approved before users verify their e-mail address."}</label>
                </div>
                <div class="z-formlist">
                    <input id="moderation_order1" type="radio" name="config[moderation_order]" value="1" {if $config.moderation_order eq 1} checked="checked"{/if} />
                    <label for="moderation_order1">{gt text="Users must verify their e-mail address before their application is approved."}</label>
                </div>
                <div class="z-formlist">
                    <input id="moderation_order2" type="radio" name="config[moderation_order]" value="1" {if $config.moderation_order eq 2} checked="checked"{/if} />
                    <label for="moderation_order2">{gt text="Application approval and e-mail address verification can occur in any order."}</label>
                </div>
            </div>

            {if $legal}
            <div class="z-formrow">
                <label>{gt text="User has to accept the 'Terms of use'"}</label>
                <div id="legal_termsofuse">
                    <input id="legal_termsofuseyes" type="radio" name="config[termsofuse]" value="1" {if $tou_active} checked="checked"{/if} />
                    <label for="legal_termsofuseyes">{gt text="Yes"}</label>
                    <input id="legal_termsofuseno" type="radio" name="config[termsofuse]" value="0" {if !$tou_active} checked="checked"{/if} />
                    <label for="legal_termsofuseno">{gt text="No"}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label>{gt text="User has to accept the 'Privacy policy'"}</label>
                <div id="legal_privacypolicy">
                    <input id="legal_privacypolicyyes" type="radio" name="config[privacypolicy]" value="1" {if $pp_active} checked="checked"{/if} />
                    <label for="legal_privacypolicyyes">{gt text="Yes"}</label>
                    <input id="legal_privacypolicyno" type="radio" name="config[privacypolicy]" value="0" {if !$pp_active} checked="checked"{/if} />
                    <label for="legal_privacypolicyno">{gt text="No"}</label>
                </div>
            </div>
            {/if}

            <div class="z-formrow">
                <label for="users_reg_Illegalusername">{gt text="Reserved user names (space-separated)"}</label>
                <input id="users_reg_Illegalusername" type="text" name="config[reg_Illegalusername]" value="{$config.reg_Illegalusername|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_reg_notifyemail">{gt text="E-mail address to notify of new user registrations (blank for none)"}</label>
                <input id="users_reg_notifyemail" type="text" name="config[reg_notifyemail]" value="{$config.reg_notifyemail|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_reg_Illegaluseragents">{gt text="Banned user agents (comma-separated)"}</label>
                <input id="users_reg_Illegaluseragents" type="text" name="config[reg_Illegaluseragents]" value="{$config.reg_Illegaluseragents|safetext}" size="50" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="users_reg_Illegaldomains">{gt text="Banned domains (comma-separated)"}</label>
                <textarea id="users_reg_Illegaldomains" name="config[reg_Illegaldomains]" cols="45" rows="10">{$config.reg_Illegaldomains|safehtml}</textarea>
            </div>
            <div class="z-formrow">
                <label for="users_reg_question">{gt text="Spam protection question"}</label>
                <input id="users_reg_question" name="config[reg_question]" value="{$config.reg_question|safehtml}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="Notice: You can set a question to be answered at registration time, to protect the site against spam automated registrations by bots and scripts."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_reg_answer">{gt text="Spam protection answer"}</label>
                <input id="users_reg_answer" name="config[reg_answer]" value="{$config.reg_answer|safehtml}" size="50" maxlength="255" />
                <em class="z-formnote z-sub">{gt text="Notice: Registering users will have to provide this response when answering the spam protection question."}</em>
            </div>
            <div class="z-formrow">
                <label for="users_use_password_strength_meter">{gt text="Show password strength meter during registration"}</label>
                <div id="users_use_password_strength_meter">
                    <input id="use_password_strength_meter_yes" type="radio" name="config[use_password_strength_meter]" value="1" {if $config.use_password_strength_meter eq 1}checked="checked" {/if} />
                    <label for="use_password_strength_meter_yes">{gt text="Yes"}</label>
                    <input id="use_password_strength_meter_no" type="radio" name="config[use_password_strength_meter]" value="0" {if $config.use_password_strength_meter neq 1}checked="checked" {/if} />
                    <label for="use_password_strength_meter_no">{gt text="No"}</label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="User log-in settings"}</legend>
            <div class="z-formrow">
                <label>{gt text="WCAG-compliant log-in and log-out"}</label>
                <div class="z-formlist">
                    <input id="login_redirectyes" type="radio" name="config[login_redirect]" value="1" {if $config.login_redirect eq 1}checked="checked" {/if}/>
                    <label for="login_redirectyes">{gt text="Yes"}</label>
                </div>
                <div class="z-formlist">
                    <input id="login_redirectno" type="radio" name="config[login_redirect]" value="0" {if $config.login_redirect neq 1}checked="checked" {/if}/>
                    <label for="login_redirectno">{gt text="No"}</label>
                    <em>{gt text="Notice: Uses meta refresh."}</em>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>{gt text="Authentication Module settings"}</legend>
            <div class="z-formrow">
                <label for="users_default_authmodule">{gt text="Default user authentication (login) module"}</label>
                <select id="users_default_authmodule" name="config[default_authmodule]">
                {foreach from=$authmodules item='authmodule'}
                    <option id="users_default_authmodule_{$authmodule.name}" value="{$authmodule.name}"{if $config.default_authmodule == $authmodule.name} selected="selected"{/if}>{$authmodule.displayname}</option>
                {* {foreachelse}
                    <option id="users_default_authmodule_Users" value="Users" selected="selected">Users manager</option>
                *} {/foreach}
                </select>
            </div>
        </fieldset>

        {modcallhooks hookobject=module hookaction=modifyconfig module=Users}
        <div class="z-formbuttons z-buttons">
            {button src=button_ok.gif set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Users type=admin func=view}" title="{gt text='Cancel'}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text='Cancel'}</a>
        </div>
    </form>
</div>
