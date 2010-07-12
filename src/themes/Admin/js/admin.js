jQuery.noConflict();
jQuery(function(){		 
	initZMenu();
	initZfeedmenu();
	jQuery('#tabs').tabs();	
	});
function initZMenu() {
  jQuery('#z-menu ul').hide();
  jQuery('#z-menu ul:first').show();
  jQuery('#z-menu li a').click(
  function() {
  var checkElement = jQuery(this).next();
  if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
  return false;
  }
  if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
  jQuery('#z-menu ul:visible').slideUp('normal');
  checkElement.slideDown('normal');
  return false;
  }});}	
  function initZfeedmenu() {
  jQuery('#z-feedmenu ul').hide();
  jQuery('#z-feedmenu ul:first').show();
  jQuery('#z-feedmenu li a').click(
  function() {
  var checkElement = jQuery(this).next();
  if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
  return false;
  }
  if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
  jQuery('#z-feedmenu ul:visible').slideUp('normal');
  checkElement.slideDown('normal');
  return false;
  }
  }
  );
  }	
jQuery(function(){
		//all hover and click logic for buttons
		jQuery(".fg-button:not(.ui-state-disabled)")
		.hover(
			function(){ 
				jQuery(this).addClass("ui-state-hover"); 
			},
			function(){ 
				jQuery(this).removeClass("ui-state-hover"); 
			}
		)
		.mousedown(function(){
				jQuery(this).parents('.fg-buttonset-single:first').find(".fg-button.ui-state-active").removeClass("ui-state-active");
				if( jQuery(this).is('.ui-state-active.fg-button-toggleable, .fg-buttonset-multi .ui-state-active') ){ jQuery(this).removeClass("ui-state-active"); }
				else { jQuery(this).addClass("ui-state-active"); }	
		})
		.mouseup(function(){
			if(! jQuery(this).is('.fg-button-toggleable, .fg-buttonset-single .fg-button,  .fg-buttonset-multi .fg-button') ){
				jQuery(this).removeClass("ui-state-active");
			}
		});
	});
	
jQuery(function() {
		jQuery("button, input:submit, a", ".visit-site").button();		
		jQuery("a", ".visit-site").click(function() { return true; });
	});	
jQuery(function() {
		jQuery("button, input:submit, a", ".visit-zk").button();		
		jQuery("a", ".visit-zk").click(function() { return true; });
	});	
jQuery('#dialog_link, ul#icons li').hover(
		function() { jQuery(this).addClass('ui-state-hover'); }, 
		function() { jQuery(this).removeClass('ui-state-hover'); }
	);
jQuery(function(){
    	// BUTTON
    	jQuery('.fg-button').hover(
    		function(){ jQuery(this).removeClass('ui-state-default').addClass('ui-state-focus'); },
    		function(){ jQuery(this).removeClass('ui-state-focus').addClass('ui-state-default'); }
    	);    	
    });
jQuery(function() {
		
		jQuery("#toolbar").menubar({
			select: function(event, ui) {
				var anchor = ui.item.children().first();
				jQuery("#log").append("<div>Selected " + anchor.attr("id") + ":" + anchor.text() + "</div>");
				jQuery(this).data("flyoutmenu").hide();
			}
		});
		jQuery("#settings-menu-button").button( "option", "icons", {
			primary:'ui-icon-gear',
			secondary:jQuery("#settings-menu-button").button( "option", "icons").secondary
		});
			jQuery("#modules-menu-button").button( "option", "icons", {
			primary:'ui-icon-newwin',
			secondary:jQuery("#modules-menu-button").button( "option", "icons").secondary
		});
			jQuery("#user-menu-button").button( "option", "icons", {
			primary:'ui-icon-person',
			secondary:jQuery("#user-menu-button").button( "option", "icons").secondary
		});
		jQuery("#layout-menu-button").button( "option", "icons", {
			primary:'ui-icon-image',
			secondary:jQuery("#layout-menu-button").button( "option", "icons").secondary
		});
		jQuery("#home-menu-button").button( "option", "icons", {
			primary:'ui-icon-home',
			secondary:jQuery("#home-menu-button").button( "option", "icons").secondary
		});	
		jQuery("#document-menu-button").button( "option", "icons", {
			primary:'ui-icon-document',
			secondary:jQuery("#document-menu-button").button( "option", "icons").secondary
		});
		jQuery("#top-button").button( "option", "icons", {
			primary:'ui-icon-arrowthick-1-n',
			secondary:jQuery("#top-button").button( "option", "icons").secondary
		});
		
		 
	});
jQuery(function()
		{
			// Call stylesheet init so that all stylesheet changing functions 
			// will work.
			jQuery.stylesheetInit();
			
			// This code loops through the stylesheets when you click the link with 
			// an ID of "toggler" below.
			jQuery('#toggler').bind(
				'click',
				function(e)
				{
					jQuery.stylesheetToggle();
					return false;
				}
			);
			
			// When one of the styleswitch links is clicked then switch the stylesheet to
			// the one matching the value of that links rel attribute.
			jQuery('.styleswitch').bind(
				'click',
				function(e)
				{
					jQuery.stylesheetSwitch(this.getAttribute('rel'));
					return false;
				}
			);
		}
	);
