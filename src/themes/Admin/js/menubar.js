/*
 * Admin UI flyout menu 
 *   - written for jQuery UI 1.9 milestone 2 using the widget factory
 *
 * Copyright (c) 2010 Michael Lang, http://nexul.com/
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 */
(function($) {
$.widget("ui.menubar", {
	_create: function(){
		var self = this;
		this.element.children("button, a").next("ul").each(function(i, elm){
			$(elm).flyoutmenu({
				select: self.options.select,
				input: $(elm).prev()
			}).hide().addClass("ui-menu-flyout");
		});
		this.element.children("button, a").each(function(i, elm){
			$(elm).click(function(event) {
				$(document).find(".ui-menu-flyout").hide();
				if ($(this).next().is("ul")){
					$(this).next().data("flyoutmenu").show();
					$(this).next().css({
						position:"absolute",
						top: 0,
						left: 0
					}).position({
						my: "left top",
						at: "left bottom",
						of: $(this)
					});
				}
				event.stopPropagation();
			}).button({
				icons :{secondary: ($(elm).next("ul").length>0)? 'ui-icon-triangle-1-s':''}
			});
		});
	}
});
}(jQuery));