// Copyright 2012 Zikula Foundation, licensed LGPLv3 or any later version.
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
