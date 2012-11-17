Zikula.define('Imagine');

document.observe("dom:loaded", function(){
    Zikula.Imagine.init();
});

Zikula.Imagine.init = function() {
    var form = $('imagine-configuration');
    form.on('click', 'a.add-preset', Zikula.Imagine.addPreset);
    form.on('click', 'a.copy-preset', Zikula.Imagine.copyPreset);
    form.on('click', 'a.delete-preset', Zikula.Imagine.deletePreset);
};

Zikula.Imagine.addPreset = function(event, element) {
    event.preventDefault();

    var preset = Zikula.Imagine.getPersetCopy($('imagine-configuration').down('fieldset.preset.default')),
        name = preset.down('div.preset-name input');

    name.removeAttribute('readonly');
    name.removeClassName('z-form-readonly');

    preset.select('input, select').invoke('clear');
    preset.hide();

    Zikula.Imagine.insertPersetCopy(preset);
};

Zikula.Imagine.copyPreset = function(event, element) {
    event.preventDefault();

    var preset = Zikula.Imagine.getPersetCopy(element.up('fieldset.preset')),
        name = preset.down('div.preset-name input');

    name.removeAttribute('readonly');
    name.removeClassName('z-form-readonly');
    name.clear();

    Zikula.Imagine.insertPersetCopy(preset);
};

Zikula.Imagine.deletePreset = function(event, element) {
    event.preventDefault();
    element.up('fieldset.preset').remove();
};

Zikula.Imagine.getPersetCopy = function(source) {
    var preset = source.clone(true),
        newId = $$('fieldset.preset').size(),
        prevId = /preset-(\d+)/.exec(preset.className)[1];

    preset.removeClassName('default').removeClassName('preset-' + prevId).addClassName('preset-' + newId);
    preset.down('legend').update('preset-' + newId);
    preset.select('label').each(function(element, index) {
        var forAttr = element.readAttribute('for');
        element.writeAttribute('for', forAttr.replace(prevId, newId));
    });
    preset.select('input, select').each(function(element, index) {
        var id = element.readAttribute('id'),
            name = element.readAttribute('name');
        element.writeAttribute('id', id.replace(prevId, newId));
        element.writeAttribute('name', name.replace(prevId, newId));
    });

    preset.select('.z-formbuttons a').invoke('removeClassName', 'z-hide');

    return preset;
};

Zikula.Imagine.insertPersetCopy = function(preset) {
    $$('.presets fieldset:last')[0].insert({
        after: preset
    });

    preset.show();
};

