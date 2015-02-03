{* purpose of this template: reusable editing of standard fields *}
{if (isset($obj.createdUserId) && $obj.createdUserId) || (isset($obj.updatedUserId) && $obj.updatedUserId)}
    {if isset($panel) && $panel eq true}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseStandardFields">{gt text='Creation and update'}</a></h3>
            </div>
            <div id="collapseStandardFields" class="panel-collapse collapse in">
                <div class="panel-body">
    {else}
        <fieldset class="standardfields">
    {/if}
        <legend>{gt text='Creation and update'}</legend>
        <ul>
        {if isset($obj.createdUserId) && $obj.createdUserId}
            {usergetvar name='uname' uid=$obj.createdUserId assign='username'}
            <li>{gt text='Created by %s' tag1=$username}</li>
            <li>{gt text='Created on %s' tag1=$obj.createdDate|dateformat}</li>
        {/if}
        {if isset($obj.updatedUserId) && $obj.updatedUserId}
            {usergetvar name='uname' uid=$obj.updatedUserId assign='username'}
            <li>{gt text='Updated by %s' tag1=$username}</li>
            <li>{gt text='Updated on %s' tag1=$obj.updatedDate|dateformat}</li>
        {/if}
        </ul>
    {if isset($panel) && $panel eq true}
                </div>
            </div>
        </div>
    {else}
        </fieldset>
    {/if}
{/if}
