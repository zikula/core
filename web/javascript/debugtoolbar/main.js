
var ZikulaDebugToolbar = Class.create({
    initialize: function() {
        this.current_panel = null;
    },
    toggleContentForPanel: function(id) {
        // hide current panel
        if(this.current_panel != null) {
            this.current_panel.hide();
        }

        if($(id) != this.current_panel) {
            $(id).show();
            this.current_panel = $(id);
        } else {
            this.current_panel = null;
        }
    },
    toggleBar: function() {
        if($('DebugToolbarLinks').visible()) {
            if(this.current_panel != null) {
                this.current_panel.hide();
                this.current_panel = null;
            }
        }

        $('DebugToolbarLinks').toggle();
    }
});

var defaultZikulaDebugToolbar = new ZikulaDebugToolbar();