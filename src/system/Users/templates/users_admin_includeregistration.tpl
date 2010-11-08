{strip}
{gt text='\'Terms of use\'' assign='touTextString'}
{gt text='\'Privacy policy\'' assign='ppTextString'}
{if $touActive && $ppActive}
{gt text='%1$s and %2$s' tag1=$touTextString tag2=$ppTextString assign=touppText}
{elseif $touActive}
{assign var='touppText' value=$touTextString}
{elseif $ppActive}
{assign var='touppText' value=$ppTextString}
{/if}
{/strip}

<table class="z-datatable">
    <thead>
        <tr>
            <th colspan="2">{gt text='Registration data'}</th>
        </tr>
    </thead>
    <tbody>
        <tr class="{cycle values='z-odd,z-even' reset=true}">
            <td class="z-right z-bold z-w30">{gt text='User name'}</td>
            <td>{$reginfo.uname}</td>
        </tr>
        <tr class="{cycle values='z-odd,z-even'}">
            <td class="z-right z-bold">{gt text='E-mail'}</td>
            <td>{if !empty($reginfo.email)}<a href="mailto:{$reginfo.email|urlencode}">{$reginfo.email|safetext}</a>{else}---{/if}</td>
        </tr>
        {if !isset($reginfo.pass) || empty($reginfo.pass)}
        <tr class="{cycle values='z-odd,z-even'}">
            <td class="z-right z-bold">{gt text='Password'}</td>
            <td><div class="z-informationmsg">{gt text='Because a password is not set for this registration, the e-mail verification process cannot be skipped. It must be completed so that the user can establish a password before the user account is created.'}</div></td>
        </tr>
        {/if}
    </tbody>
</table>

{configgetvar name='profilemodule' assign='profilemodule'}
{if $profilemodule}
<table class="z-datatable" summary="{$templatetitle}">
    <thead>
        <tr>
            <th colspan="2">{gt text='Profile data'}</th>
        </tr>
    </thead>
    <tbody>
        {modfunc modname=$profilemodule type='form' func='display' userinfo=$reginfo}
    </tbody>
</table>
{/if}

<table class="z-datatable" summary="{gt text='Registration Record Status'}">
    <thead>
        <tr>
            <th colspan="2">{gt text='Registration Status'}</th>
        </tr>
    </thead>
    <tbody>
        <tr class="{cycle values='z-odd,z-even' reset=true}">
            <td class="z-right z-bold z-w30">{gt text='E-mail verification'}</td>
            <td>{if !isset($reginfo.isverified) || empty($reginfo.isverified) || !$reginfo.isverified}{if !isset($reginfo.verificationsent) || empty($reginfo.verificationsent)}{img modname='core' set='icons/extrasmall' src='mail_delete.gif' __title='E-mail verification not sent; awating approval' __alt='E-mail verification not sent; awating approval'} {gt text='Verification e-mail message not yet sent to the user'}{else}{img modname='core' set='icons/extrasmall' src='redled.gif' __title='Pending verification of e-mail address' __alt='Pending verification of e-mail address'} {gt text='Not yet verified'}{/if}{else}{img modname='core' set='icons/extrasmall' src='greenled.gif' __title='Verified' __alt='Verified'} {gt text='Verification complete'} <span class="z-sub">{gt text='(or verification was not required when the registration was completed)'}</span>{/if}</td>
        </tr>
        <tr class="{cycle values='z-odd,z-even'}">
            <td class="z-right z-bold">{gt text='Expires'}</td>
            <td>{if $reginfo.isverified}{gt text='Never, registration is verified'}{elseif empty($reginfo.verificationsent)}{gt text='Expiration date will be set when the verification e-mail is sent'}{elseif !isset($reginfo.validuntil) || empty($reginfo.validuntil)}{gt text='Never'}{else}{$reginfo.validuntil}{/if}</td>
        </tr>
        <tr class="{cycle values='z-odd,z-even'}">
            <td class="z-right z-bold">{gt text='Administrator approval'}</td>
            <td>{if !isset($reginfo.isapproved) || empty($reginfo.isapproved) || !$reginfo.isapproved}{img modname='core' set='icons/extrasmall' src='redled.gif' __title='Pending approval' __alt='Pending approval'} {gt text='Not yet approved'}{else}{img modname='core' set='icons/extrasmall' src='greenled.gif' __title='Approved' __alt='Approved'} {gt text='Approved'} <span class="z-sub">{gt text='(or approval was not required when the registration was completed)'}</span>{/if}</td>
        </tr>
        {if $touActive || $ppActive}
        <tr class="{cycle values='z-odd,z-even'}">
            <td class="z-right z-bold">{$touppText}</td>
            <td>{if !isset($reginfo.agreetoterms) || empty($reginfo.agreetoterms) || !$reginfo.agreetoterms}{img modname='core' set='icons/extrasmall' src='redled.gif' __title='Not yet accepted' __alt='Not yet accepted'} {gt text='Not yet accepted (will be asked to accept)'}{else}{img modname='core' set='icons/extrasmall' src='greenled.gif' __title='Accepted' __alt='Accepted'} {gt text='Accepted'}{/if}</td>
        </tr>
        {/if}
    </tbody>
</table>
