<div id="zk-navi">
                <ul>
				    <li>
					<a href="{homepage}">{gt text='Home'}</a>
					</li>					
				    {modavailable modname=Pages assign=pages}
					    {if $pages}
                            <li>
					            <a href="{modurl modname=Pages type=admin}">{gt text='Pages'}</a>
                            </li>
					{/if}
                    <li>
                        <a href="{modurl modname=Settings type=admin}">{gt text="Settings"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Modules type=admin}">{gt text="Modules"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Blocks type=admin}">{gt text="Blocks"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Users type=admin}">{gt text="Users"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Groups type=admin}">{gt text="Groups"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Permissions type=admin}">{gt text="Permissions"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Theme type=admin}">{gt text="Themes"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Categories type=admin}">{gt text="Categories"}</a>
                    </li>
                </ul>
            </div>
           