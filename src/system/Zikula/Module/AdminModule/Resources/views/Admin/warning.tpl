<div id="z-adminwarning">
    <h2>{gt text='Stop!' domain='zikula'}</h2>
    <ul>
        {if $zrcexists}
            <li>{gt text="The Zikula recovery console tool (file 'zrc.php') is present in the site webroot, but must be removed before you can access the site admin panel."}</li>
        {/if}
    </ul>
    <p><strong><a href="{$adminpanellink|safetext}">{gt text='Continue'}</a></strong></p>
</div>
