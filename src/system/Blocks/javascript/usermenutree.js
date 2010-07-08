// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

document.observe("dom:loaded", menutree_init, false);

function menutree_init()
{
    // prepare each menutree block
    $$('ul.usermenutree').each(function(menu) {
        new MenuTree(menu);
    });
}
var MenuTree = Class.create({
    initialize: function(menu) {
        this.menu = $(menu);
        this.id = menu.identify();
        this.toggler = 'img.toggle';
        this.plus = document.location.pnbaseURL+'modules/menutree/pnimages/plus.gif';
        this.minus = document.location.pnbaseURL+'modules/menutree/pnimages/minus.gif';
        // bind toggle action
        this.menu.select(this.toggler).invoke('observe','click',this.toggleNode.bindAsEventListener(this));
        // bind also empty spans
        this.menu.select('li.parent > span').invoke('observe','click',this.toggleNode.bindAsEventListener(this));
        // initialy hide childnodes
        this.cookie = new CookieJar({expires:'',path:'/'});
        this.getStatus();
        this.menu.select('ul').each(function(u) {
            if(!this.status.get(u.up('li').identify())) {
                u.hide();
                u.previous(this.toggler).writeAttribute('src',this.plus);
            }
        }.bind(this));
    },
    toggleNode: function(event) {
        var target = event.element().up('li').down(this.toggler),
            ul = event.element().up('li').down('ul')
        if (ul != undefined) {
            ul.toggle();
            target.src = (ul.visible()) ? this.minus : this.plus;
            this.getStatus();
            this.status.set(ul.up('li').identify(),ul.visible());
            this.saveStatus();
        }
    },
    getStatus: function() {
        this.status = this.cookie.get(this.id) ? $H(this.cookie.get(this.id)) : new Hash();
    },
    saveStatus: function() {
        this.cookie.put(this.id,this.status);
    }
});
