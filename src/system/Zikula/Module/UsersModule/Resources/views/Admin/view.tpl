{pageaddvar name='javascript' value='system/Zikula/Module/UsersModule/Resources/public/js/Zikula.Users.Admin.View.js'}
{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text="Users list"}
</h3>

<div id="user-search-container" class="form hide">

    <div class="input-group">
        <input placeholder="{gt text="Search"}" id="user-search" class="form-control" size="25" maxlength="25" type="text" id="username" value="" />
        <span class="input-group-addon"><i class="fa fa-times pointer" id="user-search-discard"></i></span>
    </div>
</div>

<div id="user-search-min-char" class="hide">{gt text="Please enter at least 3 characters"}</div>

<p id="users-alphafilter">
    {pagerabc posvar="letter" forwardvars="sortby" printempty=true route='zikulausersmodule_admin_view'}
</p>

<table id="user-search-list" class="table table-bordered table-striped hide">
    <thead>
    <tr>
        <th>
            {gt text='User name'}
        </th>
        <th>
            {gt text='Internal ID'}
        </th>
        <th>
            {gt text='Registration date'}
        </th>
        <th>
            {gt text='Last login' sort='lastlogin'}
        </th>
        {if $canSeeGroups}
            <th>{gt text="User's groups"}</th>
        {/if}
        <th class="text-center">
            {gt text='Status'}
        </th>
        <th>
            {gt text="Actions"}
        </th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<div id="user-list">
    <table id="user-table" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>
                {sortlink __linktext='User name' sort='uname' currentsort=$sort sortdir=$sortdir route='zikulausersmodule_admin_view'}
            </th>
            <th>
                {sortlink __linktext='Internal ID' sort='uid' currentsort=$sort sortdir=$sortdir route='zikulausersmodule_admin_view'}
            </th>
            <th>
                {sortlink __linktext='Registration date' sort='user_regdate' currentsort=$sort sortdir=$sortdir route='zikulausersmodule_admin_view'}
            </th>
            <th>
                {sortlink __linktext='Last login' sort='lastlogin' currentsort=$sort sortdir=$sortdir route='zikulausersmodule_admin_view'}
            </th>
            {if $canSeeGroups}
                <th>{gt text="User's groups"}</th>
            {/if}
            <th class="text-center">
                {sortlink __linktext='Status' sort='activated' currentsort=$sort sortdir=$sortdir route='zikulausersmodule_admin_view'}
            </th>
            <th>
                {gt text="Actions"}
            </th>
        </tr>
        </thead>
        <tbody>
        {include file="Admin/userlist.tpl"}
        </tbody>
    </table>
    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulausersmodule_admin_view'}
</div>

{adminfooter}