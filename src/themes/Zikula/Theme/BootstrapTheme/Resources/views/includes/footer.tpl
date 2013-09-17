       <div class="container">
            <hr />
            <footer>
                <p>{gt text="Powered by"} <a href="http://zikula.org">Zikula</a>{if $modvars.ZikulaThemeModule.enable_mobile_theme} | <a href="{modurl modname='ZikulaThemeModule' type='User' func='enableMobileTheme'}">{gt text="Mobile version"}</a>{/if}</p>
                {nocache}{pagerendertime}{/nocache}
            </footer>
        </div> <!-- /container -->
    </body>
</html>
