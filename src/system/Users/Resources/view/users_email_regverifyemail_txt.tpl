{gt text="Verify your e-mail address for %s." tag1=$sitename assign='subject'}
{modurl modname='Users' type='user' func='verifyRegistration' uname=$reginfo.uname verifycode=$verifycode|urlencode fqurl=true assign='verificationurl'}
{gt text='Welcome to %1$s!' tag1=$sitename}

{gt text='Hello! This e-mail address (\'%1$s\') has been used to register an account on \'%2$s\' (%3$s).' tag1=$reginfo.email tag2=$sitename tag3=$siteurl}

{gt text="If you did not request a new user account at this web site, please either contact our site administrator, or simply disregard this message."}

{gt text="If you did request a new user account, then your request is waiting for you to verify your e-mail address with us."}
{if !$reginfo.isapproved}{gt text="Your request is also waiting for administrator approval."}
{if $approvalorder == 'Users_Constant::APPROVAL_AFTER'|constant}{gt text="We will not be able to approve your request until after you have completed this verification step."}{/if}
{gt text="Once both this verification step is complete and an administrator has approved your request, you will be able to log in with your user name."}{/if}

{gt text="Please click on the following link to complete the e-mail address check: "}{$verificationurl}

{gt text="If you are not able to click on the above link, you can copy it into your browser."}

{gt text="Your verification code is:"} {$verifycode}

{if !$reginfo.isapproved}{gt text="Once verified and your account has been approved, then you will be able to log in."}{elseif empty($reginfo.pass)}{gt text="Once verified, you will be able to choose a password and then log in."}{else}{gt text="Once verified, you will be able to log in."}{/if}
{gt text="Your account details are as follows:"}

{gt text="User name: %s" tag1=$reginfo.uname}
{if !empty($createdpassword)}{gt text="Password: %s" tag1=$createdpassword}{elseif !empty($reginfo.passreminder)}{gt text="Password reminder: %s" tag1=$reginfo.passreminder}{/if}

{if !empty($createdpassword)}{gt text="(This is the only time you will receive your password. Please keep it in a safe place.)"}{/if}

{if !$reginfo.isapproved}{gt text="Remember: both this verification step and approval from an administrator must be completed before you can log in."} {gt text="You will receive an additional e-mail message once an administrator has reviewed your request."}{/if}
