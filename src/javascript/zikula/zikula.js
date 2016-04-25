// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Zikula namespace definition
 */
if (typeof Zikula === 'undefined') {
    /**
     * Zikula global object
     *
     * @namespace Zikula global object
     *
     * @borrows Zikula.Util.Gettext#getMessage as __
     * @borrows Zikula.Util.Gettext#getMessageFormatted as __f
     * @borrows Zikula.Util.Gettext#getPluralMessage as _n
     * @borrows Zikula.Util.Gettext#getPluralMessageFormatted as _fn
     */
    var Zikula = {};
}
if (typeof jQuery !== 'undefined') {
    jQuery.Zikula = Zikula;
}