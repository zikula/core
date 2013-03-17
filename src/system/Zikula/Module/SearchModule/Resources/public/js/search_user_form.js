// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

function toggleboxes(mybox) {
    form = mybox.form;
    state = form.togglebox.checked;
    for (i=0; i < form.elements.length; i++) {
        e = form.elements[i];
        if (e.type == 'checkbox' && e.id.match(/^active_/)) {
            e.checked = state;
        }
    }
}
