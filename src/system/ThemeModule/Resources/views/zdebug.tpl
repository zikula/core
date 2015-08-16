{if !$zdebugpopup}
    {ajaxheader ui=true}
    {pageaddvar name='stylesheet' value='style/zdebug.css'}
    {pageaddvarblock name='header'}
    <script type="text/javascript">
        var zdebug_console = null;
        var zdebug_panels = []

        document.observe('dom:loaded', function() {
            // create the debug window
            zdebug_console = new Zikula.UI.Window($('zdebug_window'), {
                title: Zikula.__('Zikula Debug Console'),
                className: 'z-window zdebug',
                resizable: true,
                destroyOnClose: true,
                position: [function() {
                    return document.viewport.getWidth() - zdebug_console.window.container.getWidth() - 5;
                }, 5],
                width: {{$zdebugwidth}},
                height: {{$zdebugheight}}
            });
            zdebug_console.open();

            // scroll the console with the window
            Event.observe(window, 'scroll', function() {
                zdebug_console.ensureInBounds();
            });

            // create the panels
            var zdebug_options = {
                activeClassName: 'zdebug-active',
                headerClassName: 'zdebug-dataheader',
                headerSelector: '.zdebug-label',
                minheight: ($$('#zdebug_tplvars .zdebug-label:first-child')[0]).getContentHeight()
            };
            zdebug_panels.push(new Zikula.UI.Panels('zdebug_tplvars', zdebug_options));
            zdebug_panels.push(new Zikula.UI.Panels('zdebug_sessionvars', zdebug_options));
            zdebug_panels.push(new Zikula.UI.Panels('zdebug_configvars', zdebug_options));
        });
    </script>
    {/pageaddvarblock}
{/if}

{assign_debug_info}
{debugenvironment}

<div id="zdebug_window" style="display: none;">
    <h3>{gt text='Rendering engine debug console'}</h3>
    <p>{gt text='zdebug plugin found at line <strong>%1$s</strong> in template <strong>%3$s/%2$s</strong>'  tag1=$_line tag2=$_template|default:'-' tag3=$_path}</p>
    <dl class='zdebug-summary'>
        <dt>Smarty</dt>
          <dd>v{$_smartyversion}</dd>
        <dt>{gt text='ZikulaThemeModule'}</dt>
          <dd>v{$_themeversion}</dd>

        <dt class="zdebug-clearer">{gt text='Compile check'}:</dt>
          <dd>{$_compile_check}</dd>
        <dt>{gt text='Force compilation'}:</dt>
          <dd>{$_force_compile}</dd>

        <dt class="zdebug-clearer">{gt text='URL'}:</dt>
          <dd>{$_baseurl}</dd>
        <dt>{gt text='URI'}:</dt>
          <dd>{$_baseuri}</dd>
    </dl>

    {if $_debug_tpls}
    {* only filled when $smarty->debugging is enabled *}
    <h4>{gt text='Included templates and configuration files (load time in seconds)'}</h4>
    <ul>
    {section name='templates' loop=$_debug_tpls}
        <li class="{cycle values='zdebug-even,zdebug-odd'}">
            <tt>
                {section name='indent' loop=$_debug_tpls[templates].depth}&nbsp;&nbsp;&nbsp;{/section}
                <span style="color: {if $_debug_tpls[templates].type eq 'template'}brown{elseif $_debug_tpls[templates].type eq 'insert'}black{else}green{/if};">
                    {$_debug_tpls[templates].filename|safehtml}
                </span>
                {if isset($_debug_tpls[templates].exec_time)}
                    <span class="sub">({$_debug_tpls[templates].exec_time|string_format:"%.5f"}){if %templates.index% eq 0} (total){/if}</span>
                {/if}
            </tt>
        </li>
    {/section}
    </ul>
    {/if}

    <h4>{gt text='Assigned template variables'}</h4>
    <div id="zdebug_tplvars" class="zdebug-vars">
    {section name='vars' loop=$_debug_keys}
        {if $_debug_keys[vars] neq 'zikula_view' and $_debug_keys[vars]|strpos:'zdebug' !== 0}
        <div class="{cycle values='zdebug-even,zdebug-odd'}">
            <span class="zdebug-label">
                <tt>{ldelim}${$_debug_keys[vars]}{rdelim}</tt>
            </span>
            <span class="zdebug-content">
                <tt><!--raw-->{$_debug_vals[vars]|@zdebug_print_var:0:1000}<!--/raw--></tt>
            </span>
        </div>
        {/if}
    {sectionelse}
        <tt>{gt text='No template variables assigned.'}</tt>
    {/section}
    </div>

    <h4>{gt text='Zikula session variables'}</h4>
    <div id="zdebug_sessionvars" class="zdebug-vars">
    {section name='vars' loop=$_ZSession_keys}
        {if $_ZSession_keys[vars] neq '__forms'}
        <div class="{cycle values='zdebug-even,zdebug-odd'}">
            <span class="zdebug-label">
                <tt>{ldelim}${$_ZSession_keys[vars]}{rdelim}</tt>
            </span>
            <span class="zdebug-content">
                <tt><!--raw-->{$_ZSession_vals[vars]|@debug_print_var:0:1000}<!--/raw--></tt>
            </span>
        </div>
        {/if}
    {sectionelse}
        <tt>({gt text='none'})</tt>
    {/section}
    </div>

    <h4>{gt text='Assigned configuration file variables (outer template scope)'}</h4>
    <div id="zdebug_configvars" class="zdebug-vars">
    {section name='config_vars' loop=$_debug_config_keys}
        <div class="{cycle values='zdebug-even,zdebug-odd'}">
            <span class="zdebug-label">
                <tt>{ldelim}#{$_debug_config_keys[config_vars]}#{rdelim}</tt>
            </span>
            <span class="zdebug-content">
                <tt><!--raw-->{$_debug_config_vals[config_vars]|@debug_print_var:0:1000}<!--/raw--></tt>
            </span>
        </div>
    {sectionelse}
        <tt>{gt text='No configuration variables assigned.'}</tt>
    {/section}
    </div>
</div>
