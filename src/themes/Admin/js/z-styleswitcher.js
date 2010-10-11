var ZikulaSwitcher = Class.create({
  
	initialize: function(container, styles, options) {
		
		this.container  	= $(container);		
		this.styles			= $A(styles);
		this.body 			= $$('body')[0];
		
		this.options    = Object.extend({
			label			: '',
			callback		: null
        }, options || {});
		
		this.buildSwitcher();

  	},
	
	buildSwitcher: function() {

		
		this.switcher 		= new Element('div', { id:'zikulaswitcher' }).update(this.options.label);
		this.styleOptions	= new Element('ul', { id:'style-options' });
		this.container.insert(this.switcher);
		this.switcher.insert(this.styleOptions);
		
		this.temp= [];
		this.styles.each(function(style, index) {
			this.temp[index] = new Element('li', { id:style }).update(style);
			this.styleOptions.insert(this.temp[index]);
		}.bind(this));
		
		this.loadPreferences();
		this.setPreferences();

	},
	
	loadPreferences: function() {
		var cookie = Cookie.get('zikulaswitcher');
    	if (cookie=='' || cookie==null) {
			cookie = this.styles[0];			
  		}
  		this.body.className = cookie;
		this.currentStyle	= cookie;
	},
	
	setPreferences: function () {

		this.temp.each(function(element) {
								  
			element.observe('click', function(event) {	
			
  				var newStyle = element.readAttribute('id'); 

        		if (newStyle!= this.currentStyle) { 
          			this.replaceClass(this.body, this.currentStyle, newStyle);
					Cookie.set('zikulaswitcher', newStyle, 365);
					this.currentStyle = newStyle;
        		} 
     		
	      		Event.stop(event);
    		}.bind(this));

		}.bind(this)); 

	},
	
	replaceClass: function (elem, old_class, new_class) {
  		$(elem).addClassName(new_class);
  		$(elem).removeClassName(old_class);
	}

});