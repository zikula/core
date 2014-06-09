{* purpose of this template: reusable display of standard fields *}
{if (isset($obj.createdUserId) && $obj.createdUserId) || (isset($obj.updatedUserId) && $obj.updatedUserId)}
    {if isset($panel) && $panel eq true}
        <h3 class="standardfields z-panel-header z-panel-indicator cursor-pointer">{gt text='Creation and update'}</h3>
        <div class="standardfields z-panel-content" style="display: none">
    {else}
        <h3 class="standardfields">{gt text='Creation and update'}</h3>
    {/if}
    <dl class="propertylist">
    {if isset($obj.createdUserId) && $obj.createdUserId}
        <dt>{gt text='Creation'}</dt>
        {usergetvar name='uname' uid=$obj.createdUserId assign='cr_uname'}
        {if $modvars.ZConfig.profilemodule ne ''}
            {* if we have a profile module link to the user profile *}
            {modurl modname=$modvars.ZConfig.profilemodule type='user' func='view' uname=$cr_uname assign='profileLink'}
            {assign var='profileLink' value=$profileLink|safetext}
            {assign var='profileLink' value="<a href=\"`$profileLink`\">`$cr_uname`</a>"}
        {else}
            {* else just show the user name *}
            {assign var='profileLink' value=$cr_uname}
        {/if}
        <dd>{gt text='Created by %1$s on %2$s' tag1=$profileLink tag2=$obj.createdDate|dateformat html=true}</dd>
    {/if}
    {if isset($obj.updatedUserId) && $obj.updatedUserId}
        <dt>{gt text='Last update'}</dt>
        {usergetvar name='uname' uid=$obj.updatedUserId assign='lu_uname'}
        {if $modvars.ZConfig.profilemodule ne ''}
            {* if we have a profile module link to the user profile *}
            {modurl modname=$modvars.ZConfig.profilemodule type='user' func='view' uname=$lu_uname assign='profileLink'}
            {assign var='profileLink' value=$profileLink|safetext}
            {assign var='profileLink' value="<a href=\"`$profileLink`\">`$lu_uname`</a>"}
        {else}
            {* else just show the user name *}
            {assign var='profileLink' value=$lu_uname}
        {/if}
        <dd>{gt text='Updated by %1$s on %2$s' tag1=$profileLink tag2=$obj.updatedDate|dateformat html=true}</dd>
    {/if}
    </dl>
    {if isset($panel) && $panel eq true}
        </div>
    {/if}
{/if}
