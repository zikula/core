{gt text='Password changer' assign='templatetitle'}
{include file='users_user_menu.tpl'}

<p class="z-informationmsg">{gt text="To change your password, please enter your current password, and then enter a new password (you must enter it twice identically, to ensure that you have entered it correctly)."}</p>

<form id="changepassword" class="z-form" action="{modurl modname="Users" type="user" func="updatepassword"}" method="post">
    <div>
        <input type="hidden" id="changepasswordauthid" name="authid" value="{insert name="generateauthkey" module="Users"}" />
        <fieldset>
            <legend>{gt text="Change password"}</legend>
            <div class="z-formrow">
                <label for="oldpassword">{gt text="Current password"}</label>
                <input type="password" id="oldpassword" name="oldpassword" value="" />
            </div><input name="usernamehidden" id="usernamehidden" value="{user}" type="hidden" />
            <div class="z-formrow">
                <label for="newpassword">{gt text="New password"}</label>
                <input name="newpassword" id="newpassword" type="password" value="" />
                {if $use_password_strength_meter eq 1}
                {pageaddvar name='javascript' value='prototype'}
                {pageaddvar name='javascript' value='system/Users/javascript/Zikula.Users.PassMeter.js'}

                <script type="text/javascript">
                    var passmeter = new Zikula.Users.PassMeter('newpassword',{
                        username:'usernamehidden',
                        minLength: '{{$zcore.Users.minpass}}',
                        messages: {
                            username: '{{gt text="Password can not match the username, choose a different password."}}',
                            minLength: '{{gt text="The minimum length for user passwords is %s characters." tag1=$zcore.Users.minpass}}'
                        },
                        verdicts: [
                            '{{gt text="Weak"}}',
                            '{{gt text="Normal"}}',
                            '{{gt text="Strong"}}',
                            '{{gt text="Very Strong"}}'
                        ]
                    });
                </script>
                {/if}
            </div>
            <div class="z-formrow">
                <label for="newpasswordconfirm">{gt text="New password (repeat for verification)"}</label>
                <input type="password" id="newpasswordconfirm" name="newpasswordconfirm" value="" />
            </div>
            <div class="z-formrow">
                <label for="passreminder">{gt text="New password reminder"}</label>
                <input type="text" id="passreminder" name="passreminder" value="" size="25" maxlength="128" />
                <div class="z-sub z-formnote">{gt text="Enter a word or a phrase that will remind you of your password."}</div>
                <div class="z-formnote z-warningmsg">{gt text="Notice: Do not use a word or phrase that will allow others to guess your password! Do not include your password or any part of your password here!"}</div>
            </div>
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {button src='button_ok.gif' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
            <a href="{modurl modname='Users' type='user' func='main'}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
