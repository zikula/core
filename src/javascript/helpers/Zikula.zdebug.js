// Copyright Zikula Foundation 2011 - license GNU/LGPLv3 (or at your option, any later version).

if (typeof(Zikula) == 'undefined')
    Zikula = {};

Zikula.zdebug = Class.create({
    initialize: function(title, debugoutput) {
        if (title && (title != '')) {
            this.title = Zikula.__('Zikula Console') + ' - ' + title;
        } else {
            this.title = Zikula.__('Zikula Console');
        }
        
        this.debugoutput = debugoutput;
    },
    
    showConsole: function() {
        var test = this.title;
        _dbg_console = window.open("", this.title, "width=680,height=600,resizable,scrollbars=yes");
        _dbg_console.document.write('<html><head><title>'+this.title+'</title></head><body><div id="debugcontent">'+this.debugoutput+'</div></body class="donotremovemeorthepopupwillbreak"></html>');
        _dbg_console.document.close();
    }
});