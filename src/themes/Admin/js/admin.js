jQuery(document).ready(function() {

	// Navigation menu

	jQuery('ul#navigation').superfish({ 
		delay:       1000,
		animation:   {opacity:'show',height:'show'},
		speed:       'fast',
		autoArrows:  true,
		dropShadows: false
	});

	jQuery('ul#navigation li').hover(function(){
		jQuery(this).addClass('sfHover2');
	},
	function(){
		jQuery(this).removeClass('sfHover2');
	});
	
	// Live Search
	
	 
	//Hover states on the static widgets

	jQuery('.ui-state-default').hover(
		function() { jQuery(this).addClass('ui-state-hover'); }, 
		function() { jQuery(this).removeClass('ui-state-hover'); }
	);

	//Sortable portlets

	jQuery('.sortable .column').sortable({
		cursor: "move",
		connectWith: '.sortable .column',
		dropOnEmpty: false
	});

	jQuery(".column").disableSelection();

	//Sidebar only sortable boxes
	jQuery(".side_sort").sortable({
		axis: 'y',
		cursor: "move",
		connectWith: '.side_sort'
	});

	
	//Close/Open portlets
	jQuery(".portlet-header").hover(function() {
		jQuery(this).addClass("ui-portlet-hover");
	},
	function(){
		jQuery(this).removeClass("ui-portlet-hover");
	});

	jQuery(".portlet-header .ui-icon").click(function() {
		jQuery(this).toggleClass("ui-icon-circle-arrow-n");
		jQuery(this).parents(".portlet:first").find(".portlet-content").toggle();
	});


	// Sidebar close/open (with cookies)

	function close_sidebar() {
		
		jQuery("#sidebar").addClass('closed-sidebar');
		jQuery("#page_wrapper #page-content #page-content-wrapper").addClass("no-bg-image wrapper-full");
		jQuery("#open_sidebar").show();
		jQuery("#close_sidebar, .hide_sidebar").hide();
	}

	function open_sidebar() {
		jQuery("#sidebar").removeClass('closed-sidebar');
		jQuery("#page_wrapper #page-content #page-content-wrapper").removeClass("no-bg-image wrapper-full");
		jQuery("#open_sidebar").hide();
		jQuery("#close_sidebar, .hide_sidebar").show();
	}

	jQuery('#close_sidebar').click(function(){
		close_sidebar();
		if(jQuery.browser.safari) {
		    location.reload();
		}
		jQuery.cookie('sidebar', 'closed' );
			jQuery(this).addClass("active");
	});
	
	jQuery('#open_sidebar').click(function(){
		open_sidebar();
		if(jQuery.browser.safari) {
		    location.reload();
		}
		jQuery.cookie('sidebar', 'open' );
	});
	
	var sidebar = jQuery.cookie('sidebar');

		if (sidebar == 'closed') {
			close_sidebar();
	    };

		if (sidebar == 'open') {
			open_sidebar();
	    };

	
		
	/* Theme changer - set cookie */

    jQuery(function() {

        jQuery('a.set_theme').click(function() {
           	var theme_name = jQuery(this).attr("id");
			jQuery('body').append('<div id="theme_switcher" />');
			jQuery('#theme_switcher').fadeIn('fast');

			setTimeout(function () { 
				jQuery('#theme_switcher').fadeOut('fast');
			}, 2000);

			setTimeout(function () { 
			jQuery("link[title='style']").attr("href","themes/Admin/css/themes/" + theme_name + "/ui.css");
			}, 500);

			jQuery.cookie('theme', theme_name );

			jQuery('a.set_theme').removeClass("active");
			jQuery(this).addClass("active");
			
        });
		
		var theme = jQuery.cookie('theme');

		jQuery("a.set_theme[id="+ theme +"]").addClass("active");
	    
		if (theme == 'black') {
	        jQuery("link[title='style']").attr("href","themes/Admin/css/themes/black/ui.css");
	        
	    };

		if (theme == 'gray') {
	        jQuery("link[title='style']").attr("href","themes/Admin/css/themes/gray/ui.css");
	    };

		if (theme == 'gray_light') {
	        jQuery("link[title='style']").attr("href","themes/Admin/css/themes/gray_light/ui.css");
	    };
	    
		if (theme == 'blue') {
	        jQuery("link[title='style']").attr("href","themes/Admin/css/themes/blue/ui.css");
	    };
	    
		if (theme == 'green') {
	        jQuery("link[title='style']").attr("href","themes/Admin/css/themes/green/ui.css");
	    };
		if (theme == 'hot') {
	        jQuery("link[title='style']").attr("href","themes/Admin/css/themes/hot/ui.css");
	    };

    });
    
	/* Layout option - Change layout from fluid to fixed with set cookie */

    jQuery(function() {

		jQuery('.layout-options a').click(function(){
			var lay_id = jQuery(this).attr("id");
			jQuery('body').attr("class",lay_id);
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").addClass("fixed");
			jQuery.cookie('layout', lay_id );
			jQuery('.layout-options a').removeClass("active");
			jQuery(this).addClass("active");
		})
			
	    var lay_cookie = jQuery.cookie('layout');

		jQuery(".layout-options a[id="+ lay_cookie +"]").addClass("active");

		if (lay_cookie == 'layout100') {
			jQuery('body').attr("class","");
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").removeClass("fixed");
	    };

		if (lay_cookie == 'layout90') {
			jQuery('body').attr("class","layout90");
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").addClass("fixed");
	    };
	    
		if (lay_cookie == 'layout75') {
			jQuery('body').attr("class","layout75");
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").addClass("fixed");
	    };
	    
		if (lay_cookie == 'layout980') {
			jQuery('body').attr("class","layout980");
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").addClass("fixed");
	    };
	    
		if (lay_cookie == 'layout1280') {
			jQuery('body').attr("class","layout1280");
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").addClass("fixed");
	    };
	    
		if (lay_cookie == 'layout1400') {
			jQuery('body').attr("class","layout1400");
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").addClass("fixed");
	    };
	    
		if (lay_cookie == 'layout1600') {
			jQuery('body').attr("class","layout1600");
			jQuery("#page-layout, #page-header-wrapper, #sub-nav").addClass("fixed");
	    };

    });

	// Dialog			

	jQuery('#dialog').dialog({
		autoOpen: false,
		width: 600,
		bgiframe: false,
		modal: false,
		buttons: {
			"Ok": function() { 
				jQuery(this).dialog("close"); 
			}, 
			"Cancel": function() { 
				jQuery(this).dialog("close"); 
			} 
		}
	});

	// Modal Confirmation		

		jQuery("#modal_confirmation").dialog({
			autoOpen: false,
			bgiframe: true,
			resizable: false,
			width:500,
			modal: true,
			overlay: {
				backgroundColor: '#000',
				opacity: 0.5
			},
			buttons: {
				'Delete all items in recycle bin': function() {
					jQuery(this).dialog('close');
				},
				Cancel: function() {
					jQuery(this).dialog('close');
				}
			}
		});

	// Dialog Link

	jQuery('#dialog_link').click(function(){
		jQuery('#dialog').dialog('open');
		return false;
	});
	
	// Modal Confirmation Link

	jQuery('#modal_confirmation_link').click(function(){
		jQuery('#modal_confirmation').dialog('open');
		return false;
	});
	
	// Same height

	var sidebarHeight = jQuery("#sidebar").height();
	jQuery("#page-content-wrapper").css({"minHeight" : sidebarHeight });

	// Simple drop down menu

	var myIndex, myMenu, position, space=20;
	
	jQuery("div.sub").each(function(){
		jQuery(this).css('left', jQuery(this).parent().offset().left);
		jQuery(this).slideUp('fast');
	});
	
	jQuery(".drop-down li").hover(function(){
		jQuery("ul",this).slideDown('fast');
		
		//get the index, set the selector, add class
		myIndex = jQuery(".main1").index(this);
		myMenu = jQuery(".drop-down a.btn:eq("+myIndex+")");
	}, function(){
		jQuery("ul",this).slideUp('fast');
	});
jQuery(function() {
	jQuery("#accordion").accordion({
	});
});



});
