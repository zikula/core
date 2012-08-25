{adminheader}
{ajaxheader modname=Theme filename=theme_admin_modifyconfig.js noscriptaculous=true effects=true}

<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="Theme" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="themeswitcher_itemsperpage">{gt text="Items per page"}</label>
                <input id="themeswitcher_itemsperpage" type="text" name="itemsperpage" value="{$itemsperpage|safetext}" size="4" maxlength="4" tabindex="2" />
            </div>
            <div class="z-formrow">
                <label for="theme_change">{gt text="Allow users to change themes"}</label>
                <input id="theme_change" name="theme_change" type="checkbox" value="1" {if $theme_change}checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="enable_mobile_theme">{gt text="Enable mobile theme"}</label>
                <input id="enable_mobile_theme" name="enable_mobile_theme" type="checkbox" value="1" {if $enable_mobile_theme}checked="checked"{/if} />
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Compilation"}</legend>
            <div class="z-formrow">
                <label for="theme_compile_check">{gt text="Check for updated version of theme templates"}</label>
                <input id="theme_compile_check" name="compile_check" type="checkbox" value="1" {if $compile_check eq 1}checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="theme_force_compile">{gt text="Force re-compilation of theme templates"}</label>
                <input id="theme_force_compile" name="force_compile" type="checkbox" value="1" {if $force_compile eq 1}checked="checked"{/if} />
                <a class="z-indented" href="{modurl modname=Theme type=admin func=clear_compiled csrftoken=$csrftoken}">{gt text="Delete compiled theme templates"}</a>
            </div>
            <div class="z-formrow">
                <label for="render_compile_dir">{gt text="Compiled render templates directory"}</label>
                <span id="render_compile_dir"><em>{render->compile_dir}</em></span>
            </div>
            <div class="z-formrow">
                <label for="render_compile_check">{gt text="Check for updated version of render templates"}</label>
                <input id="render_compile_check" type="checkbox" name="render_compile_check" value="1"{if $render_compile_check}checked="checked"{/if} />
            </div>
            <div class="z-formrow">
                <label for="render_force_compile">{gt text="Force re-compilation of render templates"}</label>
                <input id="render_force_compile" type="checkbox" name="render_force_compile" value="1"{if $render_force_compile}checked="checked"{/if} />
                <a class="z-indented" href="{modurl modname="Theme" type="admin" func="render_clear_compiled" csrftoken=$csrftoken}">{gt text="Delete compiled render templates"}</a>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Caching"}</legend>
            <div class="z-formrow">
                <label for="enablecache">{gt text="Enable theme caching"}</label>
                <input id="enablecache" name="enablecache" type="checkbox" value="1" {if $enablecache eq 1}checked="checked"{/if} />
                <a class="z-indented" href="{modurl modname=Theme type=admin func=clear_cache csrftoken=$csrftoken}">{gt text="Delete cached theme pages"}</a>
            </div>
            <div id="theme_caching">
                <div class="z-formrow">
                    <label for="cache_lifetime">{gt text="Length of time to keep cached theme pages"}</label>
                    <p class="z-formnote z-informationmsg">{gt text="Notice: A cache lifetime of 0 will set the cache to continually regenerate; this is equivalent to no caching."}<br />{gt text="A cache lifetime of -1 will set the cache output to never expire."}</p>
                    <label for="cache_lifetime">{gt text="For homepage"}</label>
                    <span>
                        <input type="text" name="cache_lifetime" id="cache_lifetime" value="{$cache_lifetime|safetext}" size="6" tabindex="2" />
                        {gt text="seconds"}
                        <a class="z-indented" href="{modurl modname=Theme type=admin func=clear_cache cacheid=homepage csrftoken=$csrftoken}">{gt text="Delete cached pages"}</a>
                    </span>
                </div>
                <div class="z-formrow">
                    <label for="cache_lifetime_mods">{gt text="For modules"}</label>
                    <span>
                        <input type="text" name="cache_lifetime_mods" id="cache_lifetime_mods" value="{$cache_lifetime_mods|safetext}" size="6" tabindex="2" />
                        {gt text="seconds"}
                    </span>

                </div>
                <div class="z-formrow">
                    <label for="theme_nocache">{gt text="Modules to exclude from theme caching"}</label>
                    <div id="theme_nocache">
                        {foreach from=$mods key=modname item=moddisplayname}
                        <div class="z-formlist">
                            <input id="theme_nocache_{$modname|safetext}" type="checkbox" name="modulesnocache[]" value="{$modname|safetext}"{if isset($modulesnocache.$modname)} checked="checked"{/if} />
                            <label for="theme_nocache_{$modname|safetext}">{$moddisplayname|safetext}</label>
                            <a class="z-indented" href="{modurl modname=Theme type=admin func=clear_cache cacheid=$modname csrftoken=$csrftoken}">{gt text="Delete cached pages"}</a>
                        </div>
                        {/foreach}
                    </div>
                </div>
            </div>
            <div class="z-formrow">
                <label for="render_cache_dir">{gt text="Cached templates directory"}</label>
                <span id="render_cache_dir"><em>{render->cache_dir}</em></span>
            </div>
            <div class="z-formrow">
                <label for="render_cache">{gt text="Enable render caching"}</label>
                <input id="render_cache" type="checkbox" name="render_cache" value="1"{if $render_cache}checked="checked"{/if} />
                <a class="z-indented" href="{modurl modname="Theme" type="admin" func="render_clear_cache"  csrftoken=$csrftoken}">{gt text="Delete cached render pages"}</a>
            </div>
            <div id="render_lifetime_container">
                <div class="z-formrow">
                    <label for="render_lifetime">{gt text="Length of time to keep cached render pages"}</label>
                    <span>
                        <input id="render_lifetime" type="text" name="render_lifetime" value="{$render_lifetime|safetext}" size="6" />
                        {gt text="seconds"}
                    </span>
                    <p class="z-formnote z-informationmsg">{gt text="Notice: A cache lifetime of 0 will set the cache to continually regenerate; this is equivalent to no caching."}<br />{gt text="Notice: A cache lifetime of -1 will set the cache output to never expire."}</p>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="CSS/JS optimisation"}</legend>
            <p class="z-formnote z-informationmsg">{gt text="Notice: Combining and compressing JavaScript (JS) and CSS can considerably speed-up the performances of your site."}</p>
            <div class="z-formrow">
                <label for="cssjscombine">{gt text="Enable CSS/JS combination"}</label>
                <input id="cssjscombine" name="cssjscombine" type="checkbox" value="1" {if $cssjscombine eq 1}checked="checked"{ /if } />
                <a class="z-indented" href="{modurl modname=Theme type=admin func=clear_cssjscombinecache csrftoken=$csrftoken}">{gt text="Delete combination cache"}</a>
            </div>
            <div id="theme_cssjscombine">
                <div class="z-formrow">
                    <label for="cssjscompress">{gt text="Use GZ compression"}</label>
                    <input id="cssjscompress" name="cssjscompress" type="checkbox" value="1" {if $cssjscompress eq 1}checked="checked"{ /if } />
                </div>
                <div class="z-formrow">
                    <label for="cssjsminify">{gt text="Minify CSS"}</label>
                    <input id="cssjsminify" name="cssjsminify" type="checkbox" value="1" {if $cssjsminify eq 1}checked="checked"{ /if } />
                    <div id="theme_cssjsminify">
                        <p class="z-warningmsg z-formnote">{gt text="The 'Minify CSS' option may require more PHP memory. If errors occur, you should increase the 'memory_limit' setting in your PHP installation's 'php.ini' configuration file. Alternatively, you should add the following entry to the '.htaccess' file in your site's web root (without the quotation marks): 'php_value memory_limit 64M'. 64M is just a suggested value. You should experiment to find the lowest value that resolves the problem."}</p>
                    </div>
                </div>
                <div class="z-formrow">
                    <label for="cssjscombine_lifetime">{gt text="Length of time to keep combination cache"}</label>
                    <span>
                        <input type="text" name="cssjscombine_lifetime" id="cssjscombine_lifetime" value="{$cssjscombine_lifetime|safetext}" size="6" />
                        {gt text="seconds"}
                    </span>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Themes configurations"}</legend>
            <p class="z-formnote z-informationmsg">{gt text="Notice: When edit the configuration of a Theme, the Theme Engine creates copies of its configuration files inside the Temporary folder when it cannot write on them directly. If you changed your mind and want to have your configuration inside your theme, make its .ini files writable and clear the temporary copies with the following link."}</p>
            <div class="z-formrow">
                <a class="z-formnote" href="{modurl modname="Theme" type="admin" func="clear_config" csrftoken=$csrftoken}">{gt text="Delete theme configurations"}</a>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Filters"}</legend>
            <p class="z-formnote z-informationmsg">{gt text="Notice: The 'trimwhitespace' output filter trims leading white space and blank lines from the template source code after it is interpreted, which cleans-up the code and saves bandwidth."}</p>
            <div class="z-formrow">
                <label for="trimwhitespace">{gt text="Use 'trimwhitespace' output filter"}</label>
                <input id="trimwhitespace" name="trimwhitespace" type="checkbox" value="1" {if $trimwhitespace eq 1}checked="checked"{/if} />
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Debug settings"}</legend>
            <div class="z-formrow">
                <label for="render_expose_template">{gt text="Embed template information within comments inside the source code of pages"}</label>
                <input id="render_expose_template" type="checkbox" name="render_expose_template" value="1"{if $render_expose_template}checked="checked"{/if} />
                <p class="z-warningmsg z-formnote">{gt text="Warning! When auxiliary themes like RSS are used, enabling this option can corrupt the page output until you disable it again (for instance, with RSS, the feed will be broken)."}</p>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}