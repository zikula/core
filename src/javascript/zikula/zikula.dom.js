// Copyright 2012 Zikula Foundation, licensed LGPLv3 or any later version.
/**
 * @fileOverview DOM utilities
 * @requires jQuery, underscore, core.js, factory.js
 */

(function($) {
    /**
     * Zikula DOM utilities.
     * Placeholder for Zikula's extensions for jQuery.
     *
     * @name Zikula.Dom
     * @namespace Zikula Dom namespace
     * @see jQuery.fn
     */
    Zikula.define('Dom');

    /**
     * Set odd/even classes on given list.
     *
     * @exports Zikula.Dom.recolor as jQuery.fn.zRecolor
     *
     * @example
     * Zikula.Dom.recolor($this, options); // base syntax
     * $($this).zRecolor(options); // jQuery syntax (recommended)
     * $('selector').zRecolor();
     * $('selector').zRecolor('.headerClass');
     * $('selector').zRecolor({
     *     header: '.headerClass',
     *     classes: 'oddClass evenClass'
     * });
     *
     * @param {HTMLElement}    $this    Single element from jQuery collection
     * @param {String|Object} [options] Selector for header or options object
     *
     * @return {HTMLElement}
     */
    Zikula.Dom.recolor = function($this) {
        var options = {};
        if (_(arguments[1]).isString()) {
            options.header = arguments[1];
        } else if (_(arguments[1]).isObject()) {
            options = arguments[1];
        }
        options = $.extend(true, Zikula.Dom.recolor.options, options);
        var classes = options.classes.split(' '),
            items = $($this).children(options.elements)
                .not(options.header)
                .removeClass(options.classes);
        items.filter(':odd').addClass(classes[0]);
        items.filter(':even').addClass(classes[1]);
        if (options.deep) {
            items.each(function(index, element) {
                Zikula.Dom.recolor(element, options);
            });
        }
        return $this;
    };
    Zikula.Dom.recolor.options = {
        elements: 'li',
        header: '',
        classes: 'z-odd z-even',
        deep: true
    };
    $.zPluginFactory('zRecolor', Zikula.Dom.recolor);

    /**
     * Allows to check, uncheck or toggle given checkbox or radio inputs.
     *
     * @exports Zikula.Dom.toggleInput as jQuery.fn.zToggleInput
     *
     * @example
     * Zikula.Dom.toggleInput($this, value); // base syntax
     * $($this).zToggleInput(value); // jQuery syntax (recommended)
     * $('form.class').zToggleInput();
     * $('form.class').zToggleInput(true);
     *
     * @param {HTMLElement}  $this       Single element from jQuery collection
     * @param {Boolean}     [value] True to check, false to uncheck. Leave undefined to toggle status
     *
     * @return {HTMLElement}
     */
    Zikula.Dom.toggleInput = function($this, value) {
        var setValue = _(value).isUndefined() ? function(v) {return !v;} : function(v) {return value;},
            iterate = function(index, element) {
                element = $(element);
                if (element.prop('nodeName') === 'INPUT') {
                    element.attr('checked', setValue(element.attr('checked')));
                } else {
                    element.find('input[type=radio],input[type=checkbox]').each(iterate);
                }
            };
        $($this).each(iterate);

        return $this;
    };

    $.zPluginFactory('zToggleInput', Zikula.Dom.toggleInput);

    /**
     * Changes the display state of an specific container depending of an input value.
     * By default this method observe given input changes and automatically control container state.
     *
     * @exports Zikula.Dom.displayWhen as jQuery.fn.zDisplayWhen
     *
     * @example
     * Zikula.Dom.displayWhen($this, input, value); // base syntax
     * $($this).zDisplayWhen(input, value); // jQuery syntax (recommended)
     * $('#container').zDisplayWhen('#checkbox', true);
     * $('#container').zDisplayWhen('[name[radio_group]', true);
     * $('#container').zDisplayWhen('#input_parent_element', true);
     *
     * @param {HTMLElement}        $this        Single element from jQuery collection
     * @param {HTMLElement|String} input        Selector for input, inputs group or theirs container
     * @param {*}                  value        Input value to trigger container display
     * @param {Boolean}           [once=false]  Run once or observe the changes
     *
     * @return {HTMLElement}
     */
    Zikula.Dom.displayWhen = function($this, input, value, once) {
        value = _(value).isUndefined() ? true : value;
        once = _(once).isUndefined() ? false : once;
        input = $(input);

        if (input.prop('nodeName') !== 'INPUT') {
            input = input.find('input[type=radio],input[type=checkbox]');
        }

        var handler = function(animation) {
            animation = _(animation).isUndefined() ? true : animation;
            var test = input.filter(':checked').val() == value,
                hide = animation ? 'slideUp' : 'hide',
                show = animation ? 'slideDown' : 'show';
            if (test) {
                $($this)[show]();
            } else {
                $($this)[hide]();
            }
        };

        if (once) {
            handler();
        } else {
            handler(false);
            input.on('change', handler);
        }

        return $this;
    };
    $.zPluginFactory('zDisplayWhen', Zikula.Dom.displayWhen);

    /**
     * Workaround for wrong buttons values in IE and multiple submit buttons in IE6/7.
     * Also useful for capturing buttons and inputs in forms submitted by script
     * (in most browsers such forms does not have buttons/inputs data attached to form data).
     *
     * @exports Zikula.Dom.fixButtons as jQuery.fn.zFixButtons
     *
     * @example
     * Zikula.Dom.fixButtons($this, off); // base syntax
     * $($this).fixButtons(off); // jQuery syntax (recommended)
     * $('#form').fixButtons(); // turn on
     * $('#form').fixButtons(true); // turn off
     *
     * @param {HTMLElement} $this       Single element from jQuery collection
     * @param {Boolean}     [off=false] Set to true to recall the fix.
     *
     * @return {HTMLElement}
     */
    Zikula.Dom.fixButtons = function($this, off) {
        var forms = $($this).prop('nodeName') === 'FORM' ? $($this): $($this).find('form');
        if (off) {
            forms.off('.zikula.fixButtons').removeData('zikula.fixButtonsBound');
        } else {
            forms
                .filter(function(index, element) {
                    return !$(element).data('zikula.fixButtonsBound');
                })
                .on('click.zikula.fixButtons', 'button[name], input[type=submit][name]', function(event) {
                    $(this).parents('form').data('buttonClicked', event.currentTarget);
                })
                .on('submit.zikula.fixButtons', function(event) {
                    var form = $(event.currentTarget),
                        buttonClicked = form.data('buttonClicked');
                    form.find('button, input[type=submit]').each(function(index, element) {
                        $(element).attr('disabled', true);
                        if (element === buttonClicked) {
                            $('<input type="hidden" />')
                                .attr('name', buttonClicked.name)
                                .attr('value', buttonClicked.attributes.getNamedItem('value').nodeValue)
                                .appendTo(form);
                        }
                    });
                })
                .data('zikula.fixButtonsBound', true);
        }

        return $this;
    };
    $.zPluginFactory('zFixButtons', Zikula.Dom.fixButtons);

})(jQuery);