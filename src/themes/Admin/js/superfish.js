/*
 * Superfish v1.4.8 - jQuery menu widget
 * Copyright (c) 2008 Joel Birch
 *
 * Dual licensed under the MIT and GPL licenses:
 * 	http://www.opensource.org/licenses/mit-license.html
 * 	http://www.gnu.org/licenses/gpl.html
 *
 * CHANGELOG: http://users.tpg.com.au/j_birch/plugins/superfish/changelog.txt
 */
 jQuery.noConflict();

;(function(jQuery){
	jQuery.fn.superfish = function(op){

		var sf = jQuery.fn.superfish,
			c = sf.c,
			jQueryarrow = jQuery(['<span class="',c.arrowClass,'"> &#187;</span>'].join('')),
			over = function(){
				var jQueryjQuery = jQuery(this), menu = getMenu(jQueryjQuery);
				clearTimeout(menu.sfTimer);
				jQueryjQuery.showSuperfishUl().siblings().hideSuperfishUl();
			},
			out = function(){
				var jQueryjQuery = jQuery(this), menu = getMenu(jQueryjQuery), o = sf.op;
				clearTimeout(menu.sfTimer);
				menu.sfTimer=setTimeout(function(){
					o.retainPath=(jQuery.inArray(jQueryjQuery[0],o.jQuerypath)>-1);
					jQueryjQuery.hideSuperfishUl();
					if (o.jQuerypath.length && jQueryjQuery.parents(['li.',o.hoverClass].join('')).length<1){over.call(o.jQuerypath);}
				},o.delay);	
			},
			getMenu = function(jQuerymenu){
				var menu = jQuerymenu.parents(['ul.',c.menuClass,':first'].join(''))[0];
				sf.op = sf.o[menu.serial];
				return menu;
			},
			addArrow = function(jQuerya){ jQuerya.addClass(c.anchorClass).append(jQueryarrow.clone()); };
			
		return this.each(function() {
			var s = this.serial = sf.o.length;
			var o = jQuery.extend({},sf.defaults,op);
			o.jQuerypath = jQuery('li.'+o.pathClass,this).slice(0,o.pathLevels).each(function(){
				jQuery(this).addClass([o.hoverClass,c.bcClass].join(' '))
					.filter('li:has(ul)').removeClass(o.pathClass);
			});
			sf.o[s] = sf.op = o;
			
			jQuery('li:has(ul)',this)[(jQuery.fn.hoverIntent && !o.disableHI) ? 'hoverIntent' : 'hover'](over,out).each(function() {
				if (o.autoArrows) addArrow( jQuery('>a:first-child',this) );
			})
			.not('.'+c.bcClass)
				.hideSuperfishUl();
			
			var jQuerya = jQuery('a',this);
			jQuerya.each(function(i){
				var jQueryli = jQuerya.eq(i).parents('li');
				jQuerya.eq(i).focus(function(){over.call(jQueryli);}).blur(function(){out.call(jQueryli);});
			});
			o.onInit.call(this);
			
		}).each(function() {
			var menuClasses = [c.menuClass];
			if (sf.op.dropShadows  && !(jQuery.browser.msie && jQuery.browser.version < 7)) menuClasses.push(c.shadowClass);
			jQuery(this).addClass(menuClasses.join(' '));
		});
	};

	var sf = jQuery.fn.superfish;
	sf.o = [];
	sf.op = {};
	sf.IE7fix = function(){
		var o = sf.op;
		if (jQuery.browser.msie && jQuery.browser.version > 6 && o.dropShadows && o.animation.opacity!=undefined)
			this.toggleClass(sf.c.shadowClass+'-off');
		};
	sf.c = {
		bcClass     : 'sf-breadcrumb',
		menuClass   : 'sf-js-enabled',
		anchorClass : 'sf-with-ul',
		arrowClass  : 'ui-icon-carat-1-s ui-icon',
		shadowClass : 'sf-shadow'
	};
	sf.defaults = {
		hoverClass	: 'sfHover',
		pathClass	: 'overideThisToUse',
		pathLevels	: 1,
		delay		: 200,
		animation	: {opacity:'show'},
		speed		: 'fast',
		autoArrows	: true,
		dropShadows : true,
		disableHI	: false,		// true disables hoverIntent detection
		onInit		: function(){}, // callback functions
		onBeforeShow: function(){},
		onShow		: function(){},
		onHide		: function(){}
	};
	jQuery.fn.extend({
		hideSuperfishUl : function(){
			var o = sf.op,
				not = (o.retainPath===true) ? o.jQuerypath : '';
			o.retainPath = false;
			var jQueryul = jQuery(['li.',o.hoverClass].join(''),this).add(this).not(not).removeClass(o.hoverClass)
					.find('>ul').hide().css('visibility','hidden');
			o.onHide.call(jQueryul);
			return this;
		},
		showSuperfishUl : function(){
			var o = sf.op,
				sh = sf.c.shadowClass+'-off',
				jQueryul = this.addClass(o.hoverClass)
					.find('>ul:hidden').css('visibility','visible');
			sf.IE7fix.call(jQueryul);
			o.onBeforeShow.call(jQueryul);
			jQueryul.animate(o.animation,o.speed,function(){ sf.IE7fix.call(jQueryul); o.onShow.call(jQueryul); });
			return this;
		}
	});

})(jQuery);