// Copyright Zikula Foundation, licensed MIT.

Zikula.define('Imagine');

document.observe('dom:loaded', function() {
    Zikula.Imagine.init();
});

Zikula.Imagine.init = function() {
    var form = $('imagine-configuration');
    form.on('click', 'a.add-preset', Zikula.Imagine.addPreset);
    form.on('click', 'a.copy-preset', Zikula.Imagine.copyPreset);
    form.on('click', 'a.delete-preset', Zikula.Imagine.deletePreset);
    $('thumb_auto_cleanup').observe('change', thumb_auto_cleanup_onchange);
    if (!$('thumb_auto_cleanup').checked) {
        $('imagine_thumb_auto_cleanup_period').hide();
    }    
};

function thumb_auto_cleanup_onchange()
{
    Zikula.checkboxswitchdisplaystate('thumb_auto_cleanup', 'imagine_thumb_auto_cleanup_period', true);
}

Zikula.Imagine.addPreset = function(event, element) {
    event.preventDefault();

    var preset = Zikula.Imagine.getPresetCopy($('imagine-configuration').down('fieldset.preset.default')),
        name = preset.down('div.preset-name input');

    name.removeAttribute('readonly');
    name.removeClassName('z-form-readonly');

    preset.select('input, select').invoke('clear');
    preset.hide();

    Zikula.Imagine.insertPresetCopy(preset);
};

Zikula.Imagine.copyPreset = function(event, element) {
    event.preventDefault();

    var preset = Zikula.Imagine.getPresetCopy(element.up('fieldset.preset')),
        name = preset.down('div.preset-name input');

    name.removeAttribute('readonly');
    name.removeClassName('z-form-readonly');
    name.clear();

    Zikula.Imagine.insertPresetCopy(preset);
};

Zikula.Imagine.deletePreset = function(event, element) {
    event.preventDefault();
    element.up('fieldset.preset').remove();
};

Zikula.Imagine.getPresetCopy = function(source) {
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

    preset.select('.z-formbuttons a').invoke('removeClassName', 'hide');

    return preset;
};

Zikula.Imagine.insertPresetCopy = function(preset) {
    $$('fieldset:last')[0].insert({
        after: preset
    });

    preset.show();
};
