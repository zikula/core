                <div id="theme_footer">
                    <p>{gt text="Powered by"} <a href="http://zikula.org">Zikula</a>{if $modvars.Theme.enable_mobile_theme} | <a href="{modurl modname='Theme' type='User' func='enableMobileTheme'}">{gt text="Mobile version"}</a>{/if}</p>
                    {nocache}{pagerendertime}{/nocache}
                </div>
            </div>
    </body>
</html>
