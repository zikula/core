<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/vendor/Smarty/Smarty.class.php';
require_once dirname(__FILE__) . '/../../../src/lib/render/Renderer.php';

/**
 * @backupGlobals enabled
 */

class RendererTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider providerZPrefilterEscapeScript
     */
    public function testZPrefilterEscapeScript($tplSource, $expected) {
        $smarty = null; // dummy value
        $actual = z_prefilter_escape_script($tplSource, $smarty);
        $this->assertEquals($expected, $actual);
    }

    public function providerZPrefilterEscapeScript() {
        return array(
// uses NOWDOC syntax! (http://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.nowdoc)
array(
// DATA SET #0
<<<'SOURCE'
{if isset($_smarty_debug_output) and $_smarty_debug_output eq "html"}
    {$debugoutput}
{else}
<script type="text/javascript">
	if( self.name == '' ) {
	   var title = 'Console';
	} else {
	   var title = 'Console_' + self.name;
	}
	_dbg_console = window.open("",title.value,"width=680,height=600,resizable,scrollbars=yes");
 	_dbg_console.document.write('<html><body><div id="debugcontent"></div></body class="donotremovemeorthepopupwillbreak"></html>');
	_dbg_console.document.close();
    _dbg_console.document.getElementById('debugcontent').innerHTML = '{{$debugoutput|escape:javascript}}';

</script>
{/if}
SOURCE
,<<<'EXPECTED'
{if isset($_smarty_debug_output) and $_smarty_debug_output eq "html"}
    {$debugoutput}
{else}
<script type="text/javascript">{literal}
	if( self.name == '' ) {
	   var title = 'Console';
	} else {
	   var title = 'Console_' + self.name;
	}
	_dbg_console = window.open("",title.value,"width=680,height=600,resizable,scrollbars=yes");
 	_dbg_console.document.write('<html><body><div id="debugcontent"></div></body class="donotremovemeorthepopupwillbreak"></html>');
	_dbg_console.document.close();
    _dbg_console.document.getElementById('debugcontent').innerHTML = '{/literal}{$debugoutput|escape:javascript}{literal}';

{/literal}</script>
{/if}
EXPECTED
),

// DATA SET #1
array(
<<<'SOURCE'
{if isset($_smarty_debug_output) and $_smarty_debug_output eq "html"}
    {$debugoutput}
{else}
<script type="text/javascript"{if $deferScript} defer="defer"{/if}>
	if( self.name == '' ) {
	   var title = 'Console';
	} else {
	   var title = 'Console_' + self.name;
	}
	_dbg_console = window.open("",title.value,"width=680,height=600,resizable,scrollbars=yes");
 	_dbg_console.document.write('<html><body><div id="debugcontent"></div></body class="donotremovemeorthepopupwillbreak"></html>');
	_dbg_console.document.close();
    _dbg_console.document.getElementById('debugcontent').innerHTML = '{{$debugoutput|escape:javascript}}';

</script>
{/if}
SOURCE
,<<<'EXPECTED'
{if isset($_smarty_debug_output) and $_smarty_debug_output eq "html"}
    {$debugoutput}
{else}
<script type="text/javascript"{if $deferScript} defer="defer"{/if}>{literal}
	if( self.name == '' ) {
	   var title = 'Console';
	} else {
	   var title = 'Console_' + self.name;
	}
	_dbg_console = window.open("",title.value,"width=680,height=600,resizable,scrollbars=yes");
 	_dbg_console.document.write('<html><body><div id="debugcontent"></div></body class="donotremovemeorthepopupwillbreak"></html>');
	_dbg_console.document.close();
    _dbg_console.document.getElementById('debugcontent').innerHTML = '{/literal}{$debugoutput|escape:javascript}{literal}';

{/literal}</script>
{/if}
EXPECTED
),

// DATA SET #2
array(
<<<'SOURCE'
<script type="text/javascript" src="{$scriptSrcFile}" charset="{$myCharset}"></script>
SOURCE
,<<<'EXPECTED'
<script type="text/javascript" src="{$scriptSrcFile}" charset="{$myCharset}">{literal}{/literal}</script>
EXPECTED
),

// DATA SET #3
array(
<<<'SOURCE'
<script newattribute="newattributevalue" type="text/javascript">
    // JavaScript source
</script>
SOURCE
,<<<'EXPECTED'
<script newattribute="newattributevalue" type="text/javascript">{literal}
    // JavaScript source
{/literal}</script>
EXPECTED
)
        );
    }
}

