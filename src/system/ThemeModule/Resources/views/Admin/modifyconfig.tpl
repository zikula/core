{adminheader}
{pageaddvar name='javascript' value='system/ThemeModule/Resources/public/js/ZikulaThemeModule.Admin.ModifyConfig.js'}

<h3>
    <span class="fa fa-wrench"></span>
    {gt text='Settings'}
    {gt text='Theme settings' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_updateconfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='General settings'}</legend>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="themeswitcher_itemsperpage">{gt text='Items per page'}</label>
                <div class="col-sm-9">
                    <input id="themeswitcher_itemsperpage" type="text" class="form-control" name="itemsperpage" value="{$itemsperpage|safetext}" size="4" maxlength="4" tabindex="2" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_change">{gt text='Allow users to change themes'}</label>
                <div class="col-sm-9">
                    <input id="theme_change" name="theme_change" type="checkbox" value="1"{if $theme_change} checked="checked"{/if} />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="admintheme">{gt text='Admin theme'}</label>
                <div class="col-sm-9">
                    <select class="form-control" id="admintheme" name="admintheme">
                        <option value="">{gt text="Use site's theme"}</option>
                        {html_select_themes state='ThemeUtil::STATE_ACTIVE'|const filter='ThemeUtil::FILTER_ADMIN'|const selected=$admintheme}
                    </select>
                    <em class="sub help-block">{gt text='This theme will be used in the admin interface of Zikula.'}</em>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="alt_theme_name">{gt text='Theme for alternative site view'}</label>
                <div class="col-sm-9">
                    <p class="alert alert-info">{gt text='The alternate site view feature is deprecated and will be removed in Core-2.0.'}</p>
                    <select class="form-control" id="alt_theme_name" name="alt_theme_name">
                        <option value="">{gt text='Not set'}</option>
                        {html_select_themes state='ThemeUtil::STATE_ACTIVE'|const selected=$alt_theme_name|default:''}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="alt_theme_domain">{gt text='Domain for alternative site view'}</label>
                <div class="col-sm-9">
                    <input id="alt_theme_domain" type="text" class="form-control" name="alt_theme_domain" value="{$alt_theme_domain|default:''|safetext}" size="50" />
                </div>
            </div>
        </fieldset>
        {if $currentTheme->isTwigBased()}
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle fa-3x pull-left"></i>
                <div style="margin-left:5em">
                    {gt text="NOTICE: The theme currently selected (%s) is a <strong>Twig-based theme</strong> and therefore the compilation and caching functions below will do <strong>nothing at all</strong> (and will display errors if used)." tag1=$currentTheme->getName()}
                    {gt text="To force template reloading from cache on each pageload, change to %s in %s" tag1='<code>debug:true</code>' tag2='<code>app/config/custom_parameters.yml</code>'}
                </div>
            </div>
        {/if}
        <fieldset>
            <legend>{gt text='Compilation'}</legend>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_compile_check">{gt text='Check for updated version of theme templates'}</label>
                <div class="col-sm-9">
                    <input id="theme_compile_check" name="compile_check" type="checkbox" value="1"{if $compile_check eq 1} checked="checked"{/if} />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_force_compile">{gt text='Force re-compilation of theme templates'}</label>
                <div class="col-sm-9">
                    <input id="theme_force_compile" name="force_compile" type="checkbox" value="1"{if $force_compile eq 1} checked="checked"{/if} />
                    <a class="zikulathememodule-indented" href="{route name='zikulathememodule_admin_clearcompiled' csrftoken=$csrftoken}">{gt text='Delete compiled theme templates'}</a>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='Compiled render templates directory'}</label>
                <div class="col-sm-9">
                    <div class="form-control-static">
                        <em>{render->compile_dir}</em>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="render_compile_check">{gt text='Check for updated version of render templates'}</label>
                <div class="col-sm-9">
                    <input id="render_compile_check" type="checkbox" name="render_compile_check" value="1"{if $render_compile_check} checked="checked"{/if} />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="render_force_compile">{gt text='Force re-compilation of render templates'}</label>
                <div class="col-sm-9">
                    <input id="render_force_compile" type="checkbox" name="render_force_compile" value="1"{if $render_force_compile} checked="checked"{/if} />
                    <a class="zikulathememodule-indented" href="{route name='zikulathememodule_admin_renderclearcompiled' csrftoken=$csrftoken}">{gt text='Delete compiled render templates'}</a>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text='Caching'}</legend>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="enablecache">{gt text='Enable theme caching'}</label>
                <div class="col-sm-9">
                    <input id="enablecache" name="enablecache" type="checkbox" value="1"{if $enablecache eq 1} checked="checked"{/if} />
                    <a class="zikulathememodule-indented" href="{route name='zikulathememodule_admin_clearcache' csrftoken=$csrftoken}">{gt text='Delete cached theme pages'}</a>
                </div>
            </div>
            <div data-switch="enablecache" data-switch-value="1">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="cache_lifetime">{gt text='Length of time to keep cached theme pages'}</label>
                    <div class="col-sm-9">
                        <p class="help-block alert alert-info">{gt text='Notice: A cache lifetime of 0 will set the cache to continually regenerate; this is equivalent to no caching.'}<br />{gt text='A cache lifetime of -1 will set the cache output to never expire.'}</p>
                        <label for="cache_lifetime">{gt text='For homepage'}</label>
                        <span>
                            <input type="text" class="form-control" name="cache_lifetime" id="cache_lifetime" value="{$cache_lifetime|safetext}" size="6" tabindex="2" />
                            {gt text='seconds'}
                            <a class="zikulathememodule-indented" href="{route name='zikulathememodule_admin_clearcache' cacheid=homepage csrftoken=$csrftoken}">{gt text='Delete cached pages'}</a>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="cache_lifetime_mods">{gt text='For modules'}</label>
                    <div class="col-sm-9">
                        <span>
                            <input type="text" class="form-control" name="cache_lifetime_mods" id="cache_lifetime_mods" value="{$cache_lifetime_mods|safetext}" size="6" tabindex="2" />
                            {gt text='seconds'}
                        </span>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">{gt text='Modules to exclude from theme caching'}</label>
                    <div class="col-sm-9">
                        <div id="theme_nocache">
                            {foreach from=$mods key=modname item=moddisplayname}
                            <div class="z-formlist">
                                <input id="theme_nocache_{$modname|safetext}" type="checkbox" name="modulesnocache[]" value="{$modname|safetext}"{if isset($modulesnocache.$modname)} checked="checked"{/if} />
                                <label for="theme_nocache_{$modname|safetext}">{$moddisplayname|safetext}</label>
                                <a class="zikulathememodule-indented" href="{route name='zikulathememodule_admin_clearcache' cacheid=$modname csrftoken=$csrftoken}">{gt text='Delete cached pages'}</a>
                            </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{gt text='Cached templates directory'}</label>
                <div class="col-sm-9">
                    <div class="form-control-static">
                        <em>{render->cache_dir}</em>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="render_cache">{gt text='Enable render caching'}</label>
                <div class="col-sm-9">
                    <input id="render_cache" type="checkbox" name="render_cache" value="1"{if $render_cache} checked="checked"{/if} />
                    <a class="zikulathememodule-indented" href="{route name='zikulathememodule_admin_renderclearcache' csrftoken=$csrftoken}">{gt text='Delete cached render pages'}</a>
                </div>
            </div>
            <div data-switch="render_cache" data-switch-value="1">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="render_lifetime">{gt text='Length of time to keep cached render pages'}</label>
                    <div class="col-sm-9">
                        <span>
                            <input id="render_lifetime" type="text" class="form-control" name="render_lifetime" value="{$render_lifetime|safetext}" size="6" />
                            {gt text='seconds'}
                        </span>
                        <p class="help-block alert alert-info">{gt text='Notice: A cache lifetime of 0 will set the cache to continually regenerate; this is equivalent to no caching.'}<br />{gt text='Notice: A cache lifetime of -1 will set the cache output to never expire.'}</p>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text='CSS/JS optimisation'}</legend>
            <p class="help-block alert alert-info">{gt text='Notice: Combining and compressing JavaScript (JS) and CSS can considerably speed-up the performances of your site.'}</p>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="cssjscombine">{gt text='Enable CSS/JS combination'}</label>
                <div class="col-sm-9">
                    <input id="cssjscombine" name="cssjscombine" type="checkbox" value="1"{if $cssjscombine eq 1} checked="checked"{/if} />
                    <a class="zikulathememodule-indented" href="{route name='zikulathememodule_admin_clearcssjscombinecache' csrftoken=$csrftoken}">{gt text='Delete combination cache'}</a>
                </div>
            </div>
            <div data-switch="cssjscombine" data-switch-value="1">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="cssjscompress">{gt text='Use GZ compression'}</label>
                    <div class="col-sm-9">
                        <input id="cssjscompress" name="cssjscompress" type="checkbox" value="1"{if $cssjscompress eq 1} checked="checked"{ /if } />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="cssjsminify">{gt text='Minify CSS'}</label>
                    <div class="col-sm-9">
                        <input id="cssjsminify" name="cssjsminify" type="checkbox" value="1"{if $cssjsminify eq 1} checked="checked"{/if} />
                        <div data-switch="cssjsminify" data-switch-value="1">
                            <p class="alert alert-warning help-block">{gt text="The 'Minify CSS' option may require more PHP memory. If errors occur, you should increase the 'memory_limit' setting in your PHP installation's 'php.ini' configuration file. Alternatively, you should add the following entry to the '.htaccess' file in your site's web root (without the quotation marks): 'php_value memory_limit 64M'. 64M is just a suggested value. You should experiment to find the lowest value that resolves the problem."}</p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="cssjscombine_lifetime">{gt text='Length of time to keep combination cache'}</label>
                    <div class="col-sm-9">
                        <span>
                            <input type="text" class="form-control" name="cssjscombine_lifetime" id="cssjscombine_lifetime" value="{$cssjscombine_lifetime|safetext}" size="6" />
                            {gt text='seconds'}
                        </span>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text='Themes configurations'}</legend>
            <p class="help-block alert alert-info">{gt text='Notice: When edit the configuration of a Theme, the Theme Engine creates copies of its configuration files inside the Temporary folder when it cannot write on them directly. If you changed your mind and want to have your configuration inside your theme, make its .ini files writable and clear the temporary copies with the following link.'}</p>
            <a class="help-block" href="{route name='zikulathememodule_admin_clearconfig' csrftoken=$csrftoken}">{gt text='Delete theme configurations'}</a>
        </fieldset>
        <fieldset>
            <legend>{gt text='Filters'}</legend>
            <p class="help-block alert alert-info">{gt text="Notice: The 'trimwhitespace' output filter trims leading white space and blank lines from the template source code after it is interpreted, which cleans-up the code and saves bandwidth."}</p>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="trimwhitespace">{gt text="Use 'trimwhitespace' output filter"}</label>
                <div class="col-sm-9">
                    <input id="trimwhitespace" name="trimwhitespace" type="checkbox" value="1"{if $trimwhitespace eq 1} checked="checked"{/if} />
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text='Debug settings'}</legend>
            <p class="alert alert-warning help-block">{gt text='Warning! When auxiliary themes like RSS are used, enabling this option can corrupt the page output until you disable it again (for instance, with RSS, the feed will be broken).'}</p> 
            <div class="form-group">
                <label class="col-sm-3 control-label" for="render_expose_template">{gt text='Embed template information within comments inside the source code of pages'}</label>
                <div class="col-sm-9">
                    <input id="render_expose_template" type="checkbox" name="render_expose_template" value="1"{if $render_expose_template} checked="checked"{/if} />
                </div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
                <a class="btn btn-danger" href="{route name='zikulathememodule_theme_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
