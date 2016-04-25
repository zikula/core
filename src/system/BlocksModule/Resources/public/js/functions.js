// Copyright Zikula Foundation, licensed MIT.

Event.observe(window, 'load', function() {
    if ($('menuTreeImportOptions')) {
        Zikula.Menutree.Tree.inst.tree.observeOnce('tree:item:save', function() {
            $('menuTreeImportOptions').blindUp();
        });
    }
});
document.observe('dom:loaded', menutree_init);

function menutree_init()
{
    //init tree controls
    menutree_treecontrols_onload();
    //hide link classes
    menutree_linkclasses_onload();
    //init tpl and style select observer
    menutree_stylehelper_onload();
}

function menutree_treecontrols_onload()
{
    //menutree controls - add, expand and collapse
    $('menutree_newnode').observe('click', function(e) {
        e.stop();
        Zikula.Menutree.Tree.inst.newNode();
    });
    $('menutree_expandall').observe('click', function(e) {
        e.stop();
        Zikula.Menutree.Tree.inst.expandAll();
    });
    $('menutree_collapseall').observe('click', function(e) {
        e.stop();
        Zikula.Menutree.Tree.inst.collapseAll();
    });
    //controls for lang changing
    $$('.menutree_langcontrols').invoke('observe', 'click', menutree_onlangchange)
}

function menutree_linkclasses_onload()
{
    if ($('menutree_linkclass')) {
        if ($('menutree_linkclass').checked == true) {
            $('menutree_linkclasses_group').show();
        } else {
            $('menutree_linkclasses_group').hide();
        }
        //observe link class checbox - if checked show more options
        $('menutree_linkclass').observe('click', menutree_linkclasses_onchange);
        //add new row in link classes list
        $('menutree_linkclass_add').observe('click', menutree_linkclass_add);
        //remove row in link classes list
        $$('.menutree_linkclass_del').invoke('observe', 'click', menutree_linkclass_del);
    }
}

function menutree_linkclasses_onchange()
{
    if ($('menutree_linkclass').checked == true) {
        showeffect('menutree_linkclasses_group');
    } else {
        hideeffect('menutree_linkclasses_group');
    }
}

function menutree_linkclass_add(event)
{
    event.stop();
    var list = $('menutree_linkclasses_list').childElements().last().cloneNode(true),
        id = Number(list.id.match(/\d+/)[0]),
        newId = id + 1,
        inputs = list.select('input');
    list.id = list.id.replace(/\d+/, newId);
    inputs.invoke('clear');
    inputs.each(function(i) {
        i.name = i.name.replace(/\d+/, newId);
    });
    $('menutree_linkclasses_list').insert(list);
    list.down('.menutree_linkclass_del').observe('click', menutree_linkclass_del);
}

function menutree_linkclass_del(event)
{
    event.stop();
    var span = event.element();
    if ($('menutree_linkclasses_list').select('li').size() > 2 ) {
        span.up('li').remove();
    } else {
        span.up('li').select('input').invoke('clear');
    }
}

function menutree_onlangchange(event)
{
    event.stop();
    var referer = event.element();
    Zikula.Menutree.Tree.inst.changeLang(referer.lang);
    $$('.activelang').invoke('removeClassName', 'activelang')
    $(referer).addClassName('activelang');
}

function menutree_stylehelper_onload()
{
    if ($('menutree_tpl') && $('menutree_stylesheet_helper')) {
        $('menutree_tpl').observe('change', menutree_stylehelper);
        $('menutree_stylesheet_helper').hide();
        menutree_stylehelper();
    }
}

function menutree_stylehelper(event)
{
    //stop if it's onload run
    if (event == undefined) {
        return;
    }
    //pattern for tpl name
    var p = /Blocks\/Menutree\/(.+?)\.tpl/,
        tpl = (tpl = $('menutree_tpl').value.match(p)) ? tpl[1] : '',
        hidden = false;
    //hide all options not containing selected tpl name
    if (!tpl.empty() && tpl != 'default') {
        $('menutree_stylesheet').value = 'null';
        hidden = true;
        if (Prototype.Browser.IE) {
            //IE does not respect "display: none" on option element
            if ($('menutree_stylesheet_backup') == undefined) {
                var backup = $('menutree_stylesheet').cloneNode(true);
                backup.id = 'menutree_stylesheet_backup';
                backup.name = 'menutree_stylesheet_backup';
                $(backup).hide();
                document.body.appendChild(backup);
            } else {
                while ($('menutree_stylesheet').firstChild) {
                  $('menutree_stylesheet').removeChild($('menutree_stylesheet').firstChild);
                }
                $('menutree_stylesheet_backup').select('option').each(function(o) {
                    $('menutree_stylesheet').insert($(o.cloneNode(true)));
                })
            }
            $('menutree_stylesheet')
                .select('option')
                    .select(function(o) {
                        return !(o.value.include('/' + tpl) || o.value == 'null');
                    }).invoke('remove');
        } else {
            $('menutree_stylesheet')
                .select('option')
                    .invoke('show')
                    .select(function(o) {
                        return !(o.value.include('/' + tpl) || o.value == 'null');
                    }).invoke('hide');
        }
        var first = $('menutree_stylesheet').select('option').find(function(o) {
            return o.value != 'null' && o.visible();
        });
        if (first != undefined) {
            first.selected = true;
        }
        if (hidden && !$('menutree_stylesheet_helper').visible()) {
            showeffect('menutree_stylesheet_helper');
            $('menutree_stylesheet_helper').down('a').observe('click', menutree_stylehelper_reset)
        }
    }
}

function menutree_stylehelper_reset(event)
{
    event.stop();
    if (Prototype.Browser.IE) {
        //IE does not respect display: none on option element
        while ($('menutree_stylesheet').firstChild) {
          $('menutree_stylesheet').removeChild($('menutree_stylesheet').firstChild);
        }
        $('menutree_stylesheet_backup').select('option').each(function(o) {
            $('menutree_stylesheet').insert($(o.cloneNode(true)));
        })
    } else {
        $('menutree_stylesheet').select('option').invoke('show');
    }
    hideeffect('menutree_stylesheet_helper');
}

function menutree_toggle(event)
{
    event.stop();
    var element = event.element().previous();
    if (element.visible()) {
        hideeffect(element);
    } else {
        showeffect(element);
    }
}


function hideeffect(id)
{
    var obj = $(id);

    if (typeof(Effect) != 'undefined') {
        Effect.BlindUp(obj);
    } else {
        obj.hide();
    }
}

function showeffect(id)
{
    var obj = $(id);

    if (typeof(Effect) != 'undefined') {
        Effect.BlindDown(obj);
    } else {
        obj.show();
    }
}
