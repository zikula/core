<!--header start -->
<div id="header">
<div class="box-1">
<h3>{gt text='Welcome'} {usergetvar name="uname" uid=$userid|safetext} !</h3> 
<strong>{datetime}</strong>
<br />
<div class="zk-drop-1">
<ul id="z-user-drop-nav">
			
			<li>
				<a href="#" class="zk-drop" id="User">{gt text='My Account'}</a>
				<ul class="zkdropper" style="display:none;">
					<li><a href="{modurl modname=users type=user func=changeEmail}">{gt text='Change E-Mail'}</a></li>
					<li><a href="{modurl modname=users type=user func=changePassword}">{gt text='Change Password'}</a></li>
					<li><a href="{modurl modname=users type=user func=logout}">{gt text='Log Out'}</a></li>
				</ul>				
			</li>			
</ul>
</div>
</div>
<p id="quicknav">
{gt text='Quick'}:&nbsp;
<a href="#content">{gt text='Content'}</a>&nbsp;|&nbsp;
<a href="#zk-navi">{gt text='Navigation'}</a>&nbsp;|&nbsp;
<a href="#footer">{gt text='Footer'}</a>&nbsp;
</p>
<h1 id="site"><a href="{homepage}">{sitename}</a></h1>
<p id="slogan">{slogan}</p>
<div style="margin-top:14px;">
<!-- menu start -->
<!--[include file="includes/navitop.tpl"]-->		
<!-- menu end-->
</div>
</div>
<!-- header end -->