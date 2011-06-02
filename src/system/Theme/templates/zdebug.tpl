{* zdebug.tpl, based on debug.tpl, last updated version 2.0.1 *}
{ajaxheader noscriptaculous=true}
{pageaddvar name='javascript' value='javascript/helpers/Zikula.zdebug.js'}
{assign_debug_info}
{debugenvironment}
{capture name='debugoutput' assign='debugoutput'}
    <table border="0" width="100%">
    <tr bgcolor="#cccccc">
        <th colspan="2" style="text-align: left;">{gt text="Rendering engine debug console"}</th>
    </tr>
    <tr bgcolor="#eeeeee">
        <td colspan="2">
            {gt text='zdebug plugin found at line <strong>%1$s</strong> in template <strong>%3$s/%2$s</strong>'  tag1=$_line tag2=$_template|default:'-' tag3=$_path}
        </td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td colspan="2">
            Smarty <strong>v{$_smartyversion}</strong>,
            {gt text="Theme"} <strong>v{$_themeversion}</strong>
        </td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td colspan="2">
            {gt text="Compile check"}: <strong>{$_compile_check}</strong>,
            {gt text="Force compilation"}: <strong>{$_force_compile}</strong>
        </td>
    </tr>
    <tr bgcolor="#eeeeee">
        <td colspan="2">
            {gt text="URL"}: <strong>{$_baseurl}</strong>,
            {gt text="URI"}: <strong>{$_baseuri}</strong>
        </td>
    </tr>
    <tr bgcolor="#cccccc">
        <td colspan="2">
            <strong>{gt text="Included templates and configuration files (load time in seconds)"}:</strong>
        </td>
    </tr>
    {section name=templates loop=$_debug_tpls}
        <tr bgcolor="{cycle values='#eeeeee,#fafafa'}">
            <td colspan="2">
                <tt>{section name=indent loop=$_debug_tpls[templates].depth}&nbsp;&nbsp;&nbsp;{/section}<span style="color: {if $_debug_tpls[templates].type eq "template"}brown{elseif $_debug_tpls[templates].type eq "insert"}black{else}green{/if};">{$_debug_tpls[templates].filename|safehtml}</span>{if isset($_debug_tpls[templates].exec_time)} <span style="font-size: 90%;"><em>({$_debug_tpls[templates].exec_time|string_format:"%.5f"}){if %templates.index% eq 0} (total){/if}</em></span>{/if}</tt>
            </td>
        </tr>
    {sectionelse}
        <tr bgcolor="#eeeeee">
            <td colspan="2">
                <tt><em>{gt text="No templates included"}</em></tt>
            </td>
        </tr>
    {/section}
    <tr bgcolor="#cccccc">
        <td colspan="2">
            <b>{gt text="Assigned template variables"}:</b>
        </td>
    </tr>
    {section name=vars loop=$_debug_keys}
        {if $_debug_keys[vars] neq 'zikula_view'}
        <tr bgcolor="{cycle values='#eeeeee,#fafafa'}">
            <td valign="top">
                <tt style="color: blue;">{ldelim}${$_debug_keys[vars]}{rdelim}</tt>
            </td>
            <td nowrap="nowrap">
                <tt style="color: green;white-space:pre"><!--raw-->{$_debug_vals[vars]|@zdebug_print_var:0:1000}<!--/raw--></tt>
            </td>
        </tr>
        {/if}
    {sectionelse}
        <tr bgcolor="#eeeeee">
            <td colspan="2">
                <tt><em>{gt text="No template variables assigned"}</em></tt>
            </td>
        </tr>
    {/section}

    <tr bgcolor="#cccccc">
        <td colspan="2">
            <strong>{gt text="Zikula session variables"}:</strong>
        </td>
    </tr>
    {section name=vars loop=$_ZSession_keys}
        {if $_ZSession_keys[vars] neq '__forms'}
        <tr bgcolor="{cycle values='#eeeeee,#fafafa'}">
            <td style="vertical-align: top; color: blue;">
                <tt>{ldelim}${$_ZSession_keys[vars]}{rdelim}</tt>
            </td>
            <td nowrap="nowrap" style="color: green;">
                <tt style="white-space:pre;"><!--raw-->{$_ZSession_vals[vars]|@debug_print_var:0:1000}<!--/raw--></tt>
            </td>
        </tr>
        {/if}
    {sectionelse}
        <tr bgcolor="{cycle values='#eeeeee,#fafafa'}" colspan="2">
            <td style="vertical-align: top; font-style: italic;">
                ({gt text="none"})
            </td>
        </tr>
    {/section}

    <tr bgcolor="#cccccc">
        <td colspan="2">
            <strong>{gt text="Assigned configuration file variables (outer template scope)"}:</strong>
        </td>
    </tr>
    {section name=config_vars loop=$_debug_config_keys}
        <tr bgcolor="{cycle values='#eeeeee,#fafafa'}">
            <td valign="top">
                <tt style="color: maroon;">{ldelim}#{$_debug_config_keys[config_vars]}#{rdelim}</tt>
            </td>
            <td>
                <tt style="color: green;white-space:pre;"><!--raw-->{$_debug_config_vals[config_vars]|@debug_print_var:0:1000}<!--/raw--></tt>
            </td>
        </tr>
    {sectionelse}
        <tr bgcolor="#eeeeee">
            <td colspan="2">
                <tt><em>{gt text="No configuration variables assigned"}</em></tt>
            </td>
        </tr>
    {/section}
    </table>
{/capture}


{if isset($_smarty_debug_output) and $_smarty_debug_output eq "html"}
    {$debugoutput}
{else}
{pageaddvarblock}
    {*
    <script type="text/javascript">
        if( self.name == '' ) {
           var title = 'Zikula Console';
        } else {
           var title = 'Zikula Console -' + self.name;
        }
        _dbg_console = window.open("", title, "width=680,height=600,resizable,scrollbars=yes");
        _dbg_console.document.write('<html><head><title>'+title+'</title></head><body><div id="debugcontent"></div></body class="donotremovemeorthepopupwillbreak"></html>');
        _dbg_console.document.close();
        _dbg_console.document.getElementById('debugcontent').innerHTML = '{{$debugoutput|escape:javascript}}';
    </script>
    *}
    <script type="text/javascript">
        var _zdebug_console = null;
        document.observe("dom:loaded", function() {
            _zdebug_console = new Zikula.zdebug(self.name, '{{$debugoutput|escape:javascript}}');
            _zdebug_console.showConsole();
        });
    </script>
{/pageaddvarblock}
{/if}
