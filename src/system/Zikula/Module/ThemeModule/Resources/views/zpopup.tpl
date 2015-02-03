{pageaddvar name='javascript' value='zikula'}

{capture assign='zdebugoutput'}{include file='zdebug.tpl'}{/capture}

{pageaddvarblock name='header'}
<script type="text/javascript">
    ( function($) {
        $(document).ready(function() {
            var consoleTitle = Zikula.__('Zikula Console')

            _dbg_console = window.open("", consoleTitle, "width={{$zdebugwidth}},height={{$zdebugheight}},resizable,scrollbars=yes");

            _dbg_console.document.write('<html><head><title>' + consoleTitle + '</title><link type="text/css" href="' + Zikula.Config.baseURL + 'style/zdebug.css" rel="stylesheet" /></head><body id="zpopup"><div id="debugcontent">&nbsp;</div></body class="donotremovemeorthepopupwillbreak"></html>');

            _dbg_console.document.close();
    
            _dbg_console.document.getElementById('debugcontent').innerHTML = '{{$zdebugoutput|replace:' style="display: none;"':''|escape:javascript}}';
        });
    })(jQuery);
</script>
{/pageaddvarblock}
