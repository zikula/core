<title>{title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={charset}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1" />
<meta name="Author" content="{sitename}" />
<meta name="description" content="{slogan}" />
<meta name="keywords" content="{keywords}" />
<meta name="Copyright" content="Copyright (c) {'Y'|date} by {sitename}" />
<meta name="Robots" content="index,follow" />

<link rel="icon" type="image/png" href="{$imagepath}/favicon.png" />
<link rel="icon" type="image/x-icon" href="{$imagepath}/favicon.ico" /><!--[* W3C *]-->
<link rel="shortcut icon" type="image/ico" href="{$imagepath}/favicon.ico" /><!--[* IE *]-->
<link rel="alternate" href="{modurl modname='News' type='user' func='view' theme='rss'}" type="application/rss+xml" title="{sitename} {gt text='Main Feed'}" />
<link rel="stylesheet" href="{$stylepath}/style.css" type="text/css" media="screen,projection" />

<script type="text/javascript" src="javascript/ajax/proto_scriptaculous.combined.min.js"></script>
<script type="text/javascript" src="javascript/helpers/Zikula.js"></script>
<script type="text/javascript" src="javascript/livepipe/livepipe.combined.min.js"></script>
<script type="text/javascript" src="javascript/helpers/Zikula.UI.js"></script>
<script type="text/javascript" src="{$themepath}/js/admin.js"></script>

<script type="text/javascript" src="javascript/jquery/jquery.min.js"></script>
<script type="text/javascript" src="{$themepath}/js/jquery-ui-1.8.2.custom.min.js"></script>
<script type="text/javascript" src="{$themepath}/js/cookie.js"></script>

{browserhack condition="if IE"}
<style type="text/css">		
#zk-container {
	width:expression(document.body.clientWidth < 782? "780px" : document.body.clientWidth > 1262? "1260px" : "auto");
}
</style>
{/browserhack}
	<script type="text/javascript">
jQuery.noConflict();
jQuery(".draggable").draggable({cursor: "move"});
jQuery(document).ready(function() {
 
  // cookie period
  var days = 1;
 
  // load positions form cookies
  jQuery(".draggable").each( function( index ){
    jQuery(this).css( "left", 
      jQuery.cookie( "im_" + this.id + "_left") );
    jQuery(this).css( "top", 
      jQuery.cookie( "im_" + this.id + "_top") );
  });
 
  // make draggable, show, bind event
  jQuery(".draggable").draggable({cursor: "move"});
  jQuery('.draggable').show(); 
  jQuery('.draggable').bind('dragstop', savePos);
 
  // save positions into cookies
  function savePos( event, ui ){
    jQuery.cookie("im_" + this.id + "_left", 
      jQuery(this).css("left"), { path: '/', expires: days });
    jQuery.cookie("im_" + this.id + "_top", 
      jQuery(this).css("top"), { path: '/', expires: days });
  }
 
});
</script>
