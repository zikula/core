/*	
 * jQuery mmenu sectionIndexer addon
 * mmenu.frebsite.nl
 *
 * Copyright (c) Fred Heusschen
 */

(function( $ ) {

	var _PLUGIN_ = 'mmenu',
		_ADDON_  = 'sectionIndexer';


	$[ _PLUGIN_ ].addons[ _ADDON_ ] = {

		//	setup: fired once per menu
		setup: function()
		{
			var that = this,
				opts = this.opts[ _ADDON_ ],
				conf = this.conf[ _ADDON_ ];

			glbl = $[ _PLUGIN_ ].glbl;


			//	Extend shortcut options
			if ( typeof opts == 'boolean' )
			{
				opts = {
					add: opts
				};
			}
			if ( typeof opts != 'object' )
			{
				opts = {};
			}
			opts = this.opts[ _ADDON_ ] = $.extend( true, {}, $[ _PLUGIN_ ].defaults[ _ADDON_ ], opts );


			this.bind( 'init',
				function( $panels )
				{
					//	Set the panel(s)
					if ( opts.add )
					{
						var $wrapper;
						switch( opts.addTo )
						{
							case 'panels':
								 $wrapper = $panels;
								break;
			
							default:
								$wrapper = $(opts.addTo, this.$menu).filter( '.' + _c.panel );
								break;
						}

						$wrapper
							.find( '.' + _c.divider )
							.closest( '.' + _c.panel )
							.addClass( _c.hasindexer );
					}


					//	Add the indexer, only if it does not allready excists
					if ( !this.$indexer && 
						this.$pnls.children( '.' + _c.hasindexer ).length
					) {
						this.$indexer = $( '<div class="' + _c.indexer + '" />' )
							.prependTo( this.$pnls )
							.append( 
								'<a href="#a">a</a>' +
								'<a href="#b">b</a>' +
								'<a href="#c">c</a>' +
								'<a href="#d">d</a>' +
								'<a href="#e">e</a>' +
								'<a href="#f">f</a>' +
								'<a href="#g">g</a>' +
								'<a href="#h">h</a>' +
								'<a href="#i">i</a>' +
								'<a href="#j">j</a>' +
								'<a href="#k">k</a>' +
								'<a href="#l">l</a>' +
								'<a href="#m">m</a>' +
								'<a href="#n">n</a>' +
								'<a href="#o">o</a>' +
								'<a href="#p">p</a>' +
								'<a href="#q">q</a>' +
								'<a href="#r">r</a>' +
								'<a href="#s">s</a>' +
								'<a href="#t">t</a>' +
								'<a href="#u">u</a>' +
								'<a href="#v">v</a>' +
								'<a href="#w">w</a>' +
								'<a href="#x">x</a>' +
								'<a href="#y">y</a>' +
								'<a href="#z">z</a>' );

						//	Scroll onMouseOver
						this.$indexer
							.children()
							.on( _e.mouseover + '-sectionindexer ' + _c.touchstart + '-sectionindexer',
								function( e )
								{
									var lttr = $(this).attr( 'href' ).slice( 1 ),
										$panl = that.$pnls.children( '.' + _c.current ),
										$list = $panl.find( '.' + _c.listview );

									var newTop = false,
										oldTop = $panl.scrollTop();

									$panl.scrollTop( 0 );
									$list
										.children( '.' + _c.divider )
										.not( '.' + _c.hidden )
										.each(
											function()
											{
												if ( newTop === false &&
													lttr == $(this).text().slice( 0, 1 ).toLowerCase()
												) {
													newTop = $(this).position().top;
												}
											}
										);
									$panl.scrollTop( newTop !== false ? newTop : oldTop );
								}
							);


						//	Show or hide the indexer
						var update = function( $panl )
						{
							that.$menu[ ( $panl.hasClass( _c.hasindexer ) ? 'add' : 'remove' ) + 'Class' ]( _c.hasindexer );
						};

						this.bind( 'openPanel', update );
						update.call( this, this.$pnls.children( '.' + _c.current ) );
					}
				}
			);
		},

		//	add: fired once per page load
		add: function()
		{
			_c = $[ _PLUGIN_ ]._c;
			_d = $[ _PLUGIN_ ]._d;
			_e = $[ _PLUGIN_ ]._e;

			_c.add( 'indexer hasindexer' );
			_e.add( 'mouseover touchstart' );
		},
		
		//	clickAnchor: prevents default behavior when clicking an anchor
		clickAnchor: function( $a, inMenu )
		{
			if ( $a.parent().is( '.' + _c.indexer ) )
			{
				return true;
			}
		}
	};


	//	Default options and configuration
	$[ _PLUGIN_ ].defaults[ _ADDON_ ] = {
		add		: false,
		addTo	: 'panels'
	};


	var _c, _d, _e, glbl;

})( jQuery );