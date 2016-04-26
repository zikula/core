// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Ajax utils
 * @requires jQuery, underscore, core.js, class.js
 */
(function($) {
    /**
     * Zikula Ajax namespace
     *
     * @name Zikula.Ajax
     * @namespace Zikula Ajax namespace
     */
    Zikula.define('Ajax');

    Zikula.Ajax.Response = Zikula.Class.create(/** @lends Zikula.Ajax.Response.prototype */{
        /**
         * Custom handler for jQuery ajax request and response objects.
         * It's recommended to obtain data from response using this methods rather than
         * reading responseText directly.
         *
         * @class Zikula.Ajax.Response
         * @constructs
         *
         * @param {Object} request  jQuery request object
         * @param {Object} response jQuery response object (jqXHR)
         *
         * @return {Zikula.Ajax.Response} New Zikula.Ajax.Response instance
         */
        init: function(request, response) {
            this.request = function() {
                return request;
            };
            this.response = function() {
                return response;
            };

            return this;
        },
        /**
         * Get status or error messages from response
         * Note - it is possible to get more then one message from response, so this method
         * may return simple string or object with numeric keys and multiple messages.
         *
         * @return {String|Object} Message or object with multiple messages
         */
        getMessage: function() {
            return _(this.decodeResponse()).objectGetPath('core.statusmsg', null);
        },
        /**
         * Get data returned by module controller
         *
         * @return {*} Data returned by module controller
         */
        getData: function() {
            return this.decodeResponse().data;
        },
        /**
         * Get core data from response
         *
         * @private
         *
         * @return {*}
         */
        getCoreData: function() {
            return this.decodeResponse().core;
        },
        /**
         * Tests whether the request was successful.
         *
         * @return {Boolean} True on success, false otherwise
         */
        isSuccess: function() {
            var status = this.response().status;
            return status >= 200 && status < 300 || status === 304;
        },
        /**
         * Decodes responseText
         *
         * @private
         *
         * @param {Boolean} [force=false] True to skip cache
         *
         * @return {Object} Decoded response text
         */
        decodeResponse: function(force) {
            if (!this.result || force) {
                var responseText = this.response().responseText,
                    result = {
                        data: responseText || null,
                        core: null
                    },
                    response;
                if (_(responseText).isJSON()) {
                    try {
                        if (_(responseText).isJSON()) {
                            response = jQuery.parseJSON(responseText);
                            result = {
                                data: response.data || responseText,
                                core: response.core || null
                            };
                        }
                    } catch (e) {}
                }
                this.result = result;
            }

            return this.result;
        }
    });
    Zikula.Class.extend(Zikula.Ajax.Response, /** @lends Zikula.Ajax.Response.prototype */{
        /**
         * Converter function for standard Zikula ajax responses.
         * It's registered for 'text json' data type and checks if responseText contains expected properties (core and data).
         *
         * @static
         *
         * @return {Object} Decoded response text
         */
        convertResponseText: function(responseText) {
            var response = jQuery.parseJSON(responseText);
            if (_(response).objectIssetPath('core') && _(response).objectIssetPath('data')) {
                response = response.data;
            }
            return response;
        }
    });

})(jQuery);
