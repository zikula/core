/*	
 * jQuery mmenu dropdown addon
 * mmenu.frebsite.nl
 *
 * Copyright (c) Fred Heusschen
 */

(function( $ ) {

	var _PLUGIN_ = 'mmenu',
		_ADDON_  = 'dropdown';


	$[ _PLUGIN_ ].addons[ _ADDON_ ] = {

		//	setup: fired once per menu
		setup: function()
		{
			if ( !this.opts.offCanvas )
			{
				return;
			}

			var that = this,
				opts = this.opts[ _ADDON_ ],
				conf = this.conf[ _ADDON_ ];

			glbl = $[ _PLUGIN_ ].glbl;


			//	Extend shortcut options
			if ( typeof opts == 'boolean' && opts )
			{
				opts = {
					drop: opts
				};
			}
			if ( typeof opts != 'object' )
			{
				opts = {};
			}
			if ( typeof opts.position == 'string' )
			{
				opts.position = {
					of: opts.position
				};
			}
			opts = this.opts[ _ADDON_ ] = $.extend( true, {}, $[ _PLUGIN_ ].defaults[ _ADDON_ ], opts );


			if ( !opts.drop )
			{
				return;
			}


			if ( typeof opts.position.of != 'string' )
			{
				var id = this.$menu.attr( 'id' );
				if ( id && id.length )
				{
					if ( this.conf.clone )
					{
						id = _c.umm( id );
					}
					opts.position.of = '[href="#' + id + '"]';
				}
			}
			if ( typeof opts.position.of != 'string' )
			{
				return;
			}


			//	Get the button to put the menu next to
			var $bttn = $(opts.position.of);
			if ( !$bttn.length )
			{
				return;
			}

			this.$menu.addClass( _c.dropdown );

			if ( opts.tip )
			{
				this.$menu.addClass( _c.tip );
			}


			//	Emulate hover effect
			opts.event = opts.event.split( ' ' );
			if ( opts.event.length == 1 )
			{
				opts.event[ 1 ] = opts.event[ 0 ];
			}
			if ( opts.event[ 0 ] == 'hover' )
			{
				$bttn
					.on( _e.mouseenter + '-dropdown',
						function()
						{
							that.open();
						}
					);
			}
			if ( opts.event[ 1 ] == 'hover' )
			{
				this.$menu
					.on( _e.mouseleave + '-dropdown',
						function()
						{
							that.close();
						}
					);
			}


			//	Add/remove classname and style when opening/closing the menu
			this.bind( 'opening',
				function()
				{
					this.$menu.data( _d.style, this.$menu.attr( 'style' ) || '' );
					glbl.$html.addClass( _c.dropdown );
				}
			);

			this.bind( 'closed',
				function()
				{
					this.$menu.attr( 'style', this.$menu.data( _d.style ) );
					glbl.$html.removeClass( _c.dropdown );
				}
			);


			//	Update the position and sizes
			var getPosition = function( dir, obj )
			{
				var css = obj[ 0 ],
					cls = obj[ 1 ];

				var _scr = dir == 'x' ? 'scrollLeft' 	: 'scrollTop',
					_out = dir == 'x' ? 'outerWidth' 	: 'outerHeight',
					_str = dir == 'x' ? 'left' 			: 'top',
					_stp = dir == 'x' ? 'right' 		: 'bottom',
					_siz = dir == 'x' ? 'width' 		: 'height',
					_max = dir == 'x' ? 'maxWidth' 		: 'maxHeight',
					_pos = null;

				var scrl = glbl.$wndw[ _scr ](),
					strt = $bttn.offset()[ _str ] -= scrl,
					stop = strt + $bttn[ _out ](),
					wndw = glbl.$wndw[ _siz ]();

				var offs = conf.offset.button[ dir ] + conf.offset.viewport[ dir ];

				//	Position set in option
				if ( opts.position[ dir ] )
				{
					switch ( opts.position[ dir ] )
					{
						case 'left':
						case 'bottom':
							_pos = 'after';
							break;

						case 'right':
						case 'top':
							_pos = 'before';
							break;
					}
				}

				//	Position not set in option, find most space
				if ( _pos === null )
				{
					_pos = ( strt + ( ( stop - strt ) / 2 ) < wndw / 2 ) ? 'after' : 'before';
				}

				//	Set position and max
				var val, max;
				if ( _pos == 'after' )
				{
					val = ( dir == 'x' ) ? strt : stop;
					max = wndw - ( val + offs );

					css[ _str ] = val + conf.offset.button[ dir ];
					css[ _stp ] = 'auto';

					cls.push( _c[ ( dir == 'x' ) ? 'tipleft' : 'tiptop' ] );
				}
				else
				{
					val = ( dir == 'x' ) ? stop : strt;
					max = val - offs;

					css[ _stp ] = 'calc( 100% - ' + ( val - conf.offset.button[ dir ] ) + 'px )';
					css[ _str ] = 'auto';

					cls.push( _c[ ( dir == 'x' ) ? 'tipright' : 'tipbottom' ] );
				}

				css[ _max ] = Math.min( $[ _PLUGIN_ ].configuration[ _ADDON_ ][ _siz ].max, max );

				return [ css, cls ];
			};
			var position = function( $panl )
			{
				if ( !this.vars.opened )
				{
					return;
				}

				this.$menu.attr( 'style', this.$menu.data( _d.style ) );

				var obj = [{}, []];
				obj = getPosition.call( this, 'y', obj );
				obj = getPosition.call( this, 'x', obj );

				this.$menu.css( obj[ 0 ] );

				if ( opts.tip )
				{
					this.$menu
						.removeClass( 
							_c.tipleft 	+ ' ' +
							_c.tipright	+ ' ' + 
							_c.tiptop 	+ ' ' +
							_c.tipbottom
						)
						.addClass( obj[ 1 ].join( ' ' ) );
				}
			};

			this.bind( 'opening', position );

			glbl.$wndw
				.on( _e.resize + '-dropdown',
					function( e )
					{
						position.call( that );
					}
				);

			if ( !this.opts.offCanvas.blockUI )
			{
				glbl.$wndw
					.on( _e.scroll + '-dropdown',
						function( e )
						{
							position.call( that );
						}
					);
			}
		},

		//	add: fired once per page load
		add: function()
		{
			_c = $[ _PLUGIN_ ]._c;
			_d = $[ _PLUGIN_ ]._d;
			_e = $[ _PLUGIN_ ]._e;

 			_c.add( 'dropdown tip tipleft tipright tiptop tipbottom' );
 			_e.add( 'mouseenter mouseleave resize scroll' );
		},

		//	clickAnchor: prevents default behavior when clicking an anchor
		clickAnchor: function( $a, inMenu ) {}
	};


	//	Default options and configuration
	$[ _PLUGIN_ ].defaults[ _ADDON_ ] = {
		drop 		: false,
		event		: 'click',
		position	: {},
		tip			: true
	};
	$[ _PLUGIN_ ].configuration[ _ADDON_ ] = {
		offset: {
			button	: {
				x 		: -10,
				y		: 10
			},
			viewport: {
				x 		: 20,
				y 		: 20
			}
		},
		height	: {
			max		: 880
		},
		width	: {
			max		: 440
		}
	};


	var _c, _d, _e, glbl;


})( jQuery );