/*	
 * jQuery mmenu toggles addon
 * mmenu.frebsite.nl
 *
 * Copyright (c) Fred Heusschen
 */

(function( $ ) {

	var _PLUGIN_ = 'mmenu',
		_ADDON_  = 'toggles';


	$[ _PLUGIN_ ].addons[ _ADDON_ ] = {

		//	setup: fired once per menu
		setup: function()
		{
			var that = this,
				opts = this.opts[ _ADDON_ ],
				conf = this.conf[ _ADDON_ ];

			glbl = $[ _PLUGIN_ ].glbl;


			this.bind( 'init',
				function( $panels )
				{

					//	Refactor toggle classes
					this.__refactorClass( $('input', $panels), this.conf.classNames[ _ADDON_ ].toggle, 'toggle' );
					this.__refactorClass( $('input', $panels), this.conf.classNames[ _ADDON_ ].check, 'check' );
			

					//	Add markup
					$('input.' + _c.toggle + ', input.' + _c.check, $panels)
						.each(
							function()
							{
								var $inpt = $(this),
									$prnt = $inpt.closest( 'li' ),
									cl = $inpt.hasClass( _c.toggle ) ? 'toggle' : 'check',
									id = $inpt.attr( 'id' ) || that.__getUniqueId();

								if ( !$prnt.children( 'label[for="' + id + '"]' ).length )
								{
									$inpt.attr( 'id', id );
									$prnt.prepend( $inpt );
			
									$('<label for="' + id + '" class="' + _c[ cl ] + '"></label>')
										.insertBefore( $prnt.children( 'a, span' ).last() );
								}
							}
						);
				}
			);
		},

		//	add: fired once per page load
		add: function()
		{
			_c = $[ _PLUGIN_ ]._c;
			_d = $[ _PLUGIN_ ]._d;
			_e = $[ _PLUGIN_ ]._e;
	
			_c.add( 'toggle check' );
		},

		//	clickAnchor: prevents default behavior when clicking an anchor
		clickAnchor: function( $a, inMenu ) {}
	};


	//	Default options and configuration
	$[ _PLUGIN_ ].configuration.classNames[ _ADDON_ ] = {
		toggle	: 'Toggle',
		check	: 'Check'
	};


	var _c, _d, _e, glbl;

})( jQuery );