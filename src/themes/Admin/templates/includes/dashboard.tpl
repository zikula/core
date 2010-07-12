<div id="dashboard">
    <h2 class="ico_dashboard">{gt text=Informations}</h2>
        <div class="clearfix">
            <div class="zk-dashboard z-marginleft">
			<h3>Counters &amp; Todo</h3>
                    <ul>
                        <li>News Articles: <span class="z-btblue">115</span></li>
                        <li>Posts: <span class="z-btblue">15000</span></li>					
                        <li>Comments: <span class="z-btblue">340</span></li>
                        <li>News Drafts: <span class="z-btblue">33</span></li>
                        <li>Things to do: <span class="z-btblue">31</span></li>
                        <li>ezComments w for aproval: <span class="z-btblue">20</span></li>
                    </ul>
            </div>
            <div class="zk-dashboard z-marginleft" style="width:120px;">
                <h3>Stats</h3>
                    <ul>
                        <li>Online:<br />
                            {blockshow module="Users" blockname="Online" block="5"}
                        </li>					
                        <li>Newest User:<br />
                            <span class="z-btblue">Zikulalover</span></li>
                        <li>Images: <span class="z-btblue">344</span></li>
                        <li>To do: <span class="z-btblue">10</span></li>				
                    </ul>
            </div>
            <div id="z-feedmenuholder" class="z-feedmenuholder z-marginleft">
                <h2 class="ico_smallfeeds">{gt text='Stay in Touch'}</h2>
				
                        <ul id="z-feedmenu">
                            <li>
							<a href="#" class="ico_news">{gt text='Community'}</a>
					<ul>						
						<li><a href="#">foo</a></li>
						<li><a href="#">bar</a></li>
					</ul>
					<a href="#" class="ico_pages">{gt text='Extensions'}</a>
					<ul>
						<li><a href="#">bar</a></li>
					</ul>
					<a href="#" class="ico_user">{gt text='Blog'}</a>
					<ul>
						<li><a href="#">foo </a></li>
					</ul>
							
							</li>
                        </ul>
                    
            </div>	
        </div>
			</div> 
        <div id="tasks" class="clearfix">
            <h2 class="ico_tasks">{gt text='Fast Tasks'}</h2>
                <ul>
                    <li class="tasks_first_li"><a href="#"><img src="{$themepath}/img/icons/themes.gif" alt="themes" /><span>{gt text='Themes'}</span></a></li>
                    <li><a href="#"><img src="{$themepath}/img/icons/blocks.gif" alt="Blocks" /><span>{gt text='Blocks'}</span></a></li>					
                    <li><a href="#"><img src="{$themepath}/img/icons/users.gif" alt="Users" /><span>{gt text='Users'}</span></a></li>
                    <li><a href="#"><img src="{$themepath}/img/icons/category.gif" alt="Categories" /><span>{gt text='Categories'}</span></a></li>                    
					<li><a href="#"><img src="{$themepath}/img/icons/password.gif" alt="Security" /><span>{gt text='Permissions'}</span></a></li>
                    <li><a href="#"><img src="{$themepath}/img/icons/security.gif" alt="Security" /><span>{gt text='Security'}</span></a></li>                    
					<li><a href="#"><img src="{$themepath}/img/icons/settings.gif" alt="Settings" /><span>{gt text='Settings'}</span></a></li>
                
				</ul>
        </div>
        <div id="zk-dash-right" class="right" >
            <h2 class="ico_sidebar">{gt text='Quick n clean'}</h2>
                <!--[include file="includes/navi-dash-right.tpl"]-->				
</div>