<br />
<div class="z-form">
    <fieldset>
        <legend>{gt text='Compilation'}</legend>
            <ul>
                <li><a href="index.php?module=theme&amp;type=admin&amp;func=clear_compiled&amp;authid={insert name="generateauthkey" module="Theme"}">{gt text='Delete compiled theme templates'}</a></li>
		        <li><a href="index.php?module=theme&amp;type=admin&amp;func=render_clear_compiled&amp;authid={insert name="generateauthkey" module="Theme"}">{gt text='Delete compiled render templates'}</a></li>
            </ul>				
    </fieldset>
	<fieldset>
        <legend>{gt text='Caching'}</legend>
            <ul>
		        <li><a href="index.php?module=theme&amp;type=admin&amp;func=clear_cache&amp;authid={insert name="generateauthkey" module="Theme"}">{gt text='Delete cached theme pages'}</a></li>
		        <li><a href="index.php?module=theme&amp;type=admin&amp;func=render_clear_cache&amp;authid={insert name="generateauthkey" module="Theme"}">{gt text='Delete cached render pages'}</a></li>			
		    </ul>
    </fieldset>
	<fieldset>
        <legend>{gt text='JS/CSS'}</legend>
            <ul>
		        <li><a href="index.php?module=theme&amp;type=admin&amp;func=clear_cssjscombinecache&amp;authid={insert name="generateauthkey" module="Theme"}">{gt text='Delete combination cache'}</a></li>	
		    </ul>
    </fieldset>
    <fieldset>
        <legend>{gt text='Check Folders'}</legend>
            <ul>
		        <li><a href="index.php?module=sysinfo&amp;type=admin&amp;func=ztemp">{gt text='Check Z-Temp'}</a></li>
		        <li><a href="index.php?module=sysinfo&amp;type=admin&amp;func=filesystem">{gt text='Check Folders'}</a></li>			
		    </ul>
    </fieldset>
</div>