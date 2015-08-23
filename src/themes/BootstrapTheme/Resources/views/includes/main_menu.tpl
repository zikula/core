 <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="{homepage}">{$modvars.ZConfig.sitename}</a>
        </div>
        <div class="navbar-collapse collapse">
        {if $pagetype eq 'admin'}
          <ul class="nav navbar-nav">
            <li><a href="{homepage}">{gt text='Home'}</a></li>
            {checkpermission component='ZikulaSettingsModule::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
            {if $okAccess}
            <li><a href="{modurl modname='ZikulaSettingsModule' type='admin' func='index'}">{gt text="Settings"}</a></li>
            {/if}
            {checkpermission component='ZikulaExtensionsModule::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
            {if $okAccess}
            <li><a href="{modurl modname='ZikulaExtensionsModule' type='admin' func='index'}">{gt text="Extensions"}</a></li>
            {/if}
            {checkpermission component='ZikulaBlocksModule::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
            {if $okAccess}
            <li><a href="{modurl modname='ZikulaBlocksModule' type='admin' func='index'}">{gt text="Blocks"}</a></li>
            {/if}
            {checkpermission component='ZikulaUsersModule::' instance='::' level='ACCESS_MODERATE' assign='okAccess'}
            {if $okAccess}
            <li><a href="{modurl modname='ZikulaUsersModule' type='admin' func='index'}">{gt text="Users"}</a></li>
            {/if}
            {checkpermission component='ZikulaGroupsModule::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
            {if $okAccess}
            <li><a href="{modurl modname='ZikulaGroupsModule' type='admin' func='index'}">{gt text="Groups"}</a></li>
            {/if}
            {checkpermission component='ZikulaPermissionsModule::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
            {if $okAccess}
            <li><a href="{modurl modname='ZikulaPermissionsModule' type='admin' func='index'}">{gt text="Permission rules"}</a></li>
            {/if}
            {checkpermission component='ZikulaThemeModule::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
            {if $okAccess}
            <li><a href="{modurl modname='ZikulaThemeModule' type='admin' func='index'}">{gt text="Themes"}</a></li>
            {/if}
          </ul>
        {else}
          <ul class="nav navbar-nav">
            <li class="active"><a href="{homepage}" title="{gt text="Go to the site's home page"}">{gt text='Home'}</a></li>
            <li><a href="{modurl modname='ZikulaUsersModule' type='user' func='main'}" title="{gt text='Go to your account panel'}">{gt text="My Account"}</a></li>
            <li><a href="{modurl modname='ZikulaSearchModule' type='user' func='main'}" title="{gt text='Search this site'}">{gt text="Site search"}</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="#">Action</a></li>
                <li><a href="#">Another action</a></li>
                <li><a href="#">Something else here</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Nav header</li>
                <li><a href="#">Separated link</a></li>
                <li><a href="#">One more separated link</a></li>
              </ul>
            </li>
          </ul>
          <form class="navbar-form navbar-right">
            <div class="form-group">
              <input type="text" placeholder="Email" class="form-control">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Sign in</button>
          </form>
        {/if}
        </div><!--/.navbar-collapse -->
      </div>
    </div>
        
       {* {blockposition name=topnav assign=topnavblock}
    {if empty($topnavblock)}
    <ul class="pull-left">
        <li></li>
        <li></li>
        <li></li>
    </ul>
    {else}
    {$topnavblock}
    {/if} *}
