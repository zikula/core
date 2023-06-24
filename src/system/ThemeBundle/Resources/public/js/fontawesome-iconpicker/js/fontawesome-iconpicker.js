/*!
 * Font Awesome Icon Picker
 * https://farbelous.github.io/fontawesome-iconpicker/
 *
 * @author Javi Aguilar, itsjavi.com
 * @license MIT License
 * @see https://github.com/farbelous/fontawesome-iconpicker/blob/master/LICENSE
 */


(function(e) {
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], e);
    } else {
        e(jQuery);
    }
})(function(S) {
    S.ui = S.ui || {};
    var e = S.ui.version = "1.12.1";
    (function() {
        var s, v = Math.max, x = Math.abs, r = /left|center|right/, i = /top|center|bottom/, o = /[\+\-]\d+(\.[\d]+)?%?/, c = /^\w+/, l = /%$/, a = S.fn.pos;
        function q(e, a, t) {
            return [ parseFloat(e[0]) * (l.test(e[0]) ? a / 100 : 1), parseFloat(e[1]) * (l.test(e[1]) ? t / 100 : 1) ];
        }
        function L(e, a) {
            return parseInt(S.css(e, a), 10) || 0;
        }
        function t(e) {
            var a = e[0];
            if (a.nodeType === 9) {
                return {
                    width: e.width(),
                    height: e.height(),
                    offset: {
                        top: 0,
                        left: 0
                    }
                };
            }
            if (S.isWindow(a)) {
                return {
                    width: e.width(),
                    height: e.height(),
                    offset: {
                        top: e.scrollTop(),
                        left: e.scrollLeft()
                    }
                };
            }
            if (a.preventDefault) {
                return {
                    width: 0,
                    height: 0,
                    offset: {
                        top: a.pageY,
                        left: a.pageX
                    }
                };
            }
            return {
                width: e.outerWidth(),
                height: e.outerHeight(),
                offset: e.offset()
            };
        }
        S.pos = {
            scrollbarWidth: function() {
                if (s !== undefined) {
                    return s;
                }
                var e, a, t = S("<div " + "style='display:block;position:absolute;width:50px;height:50px;overflow:hidden;'>" + "<div style='height:100px;width:auto;'></div></div>"), r = t.children()[0];
                S("body").append(t);
                e = r.offsetWidth;
                t.css("overflow", "scroll");
                a = r.offsetWidth;
                if (e === a) {
                    a = t[0].clientWidth;
                }
                t.remove();
                return s = e - a;
            },
            getScrollInfo: function(e) {
                var a = e.isWindow || e.isDocument ? "" : e.element.css("overflow-x"), t = e.isWindow || e.isDocument ? "" : e.element.css("overflow-y"), r = a === "scroll" || a === "auto" && e.width < e.element[0].scrollWidth, s = t === "scroll" || t === "auto" && e.height < e.element[0].scrollHeight;
                return {
                    width: s ? S.pos.scrollbarWidth() : 0,
                    height: r ? S.pos.scrollbarWidth() : 0
                };
            },
            getWithinInfo: function(e) {
                var a = S(e || window), t = S.isWindow(a[0]), r = !!a[0] && a[0].nodeType === 9, s = !t && !r;
                return {
                    element: a,
                    isWindow: t,
                    isDocument: r,
                    offset: s ? S(e).offset() : {
                        left: 0,
                        top: 0
                    },
                    scrollLeft: a.scrollLeft(),
                    scrollTop: a.scrollTop(),
                    width: a.outerWidth(),
                    height: a.outerHeight()
                };
            }
        };
        S.fn.pos = function(h) {
            if (!h || !h.of) {
                return a.apply(this, arguments);
            }
            h = S.extend({}, h);
            var m, p, d, u, g, e, b = S(h.of), T = S.pos.getWithinInfo(h.within), w = S.pos.getScrollInfo(T), k = (h.collision || "flip").split(" "), y = {};
            e = t(b);
            if (b[0].preventDefault) {
                h.at = "left top";
            }
            p = e.width;
            d = e.height;
            u = e.offset;
            g = S.extend({}, u);
            S.each([ "my", "at" ], function() {
                var e = (h[this] || "").split(" "), a, t;
                if (e.length === 1) {
                    e = r.test(e[0]) ? e.concat([ "center" ]) : i.test(e[0]) ? [ "center" ].concat(e) : [ "center", "center" ];
                }
                e[0] = r.test(e[0]) ? e[0] : "center";
                e[1] = i.test(e[1]) ? e[1] : "center";
                a = o.exec(e[0]);
                t = o.exec(e[1]);
                y[this] = [ a ? a[0] : 0, t ? t[0] : 0 ];
                h[this] = [ c.exec(e[0])[0], c.exec(e[1])[0] ];
            });
            if (k.length === 1) {
                k[1] = k[0];
            }
            if (h.at[0] === "right") {
                g.left += p;
            } else if (h.at[0] === "center") {
                g.left += p / 2;
            }
            if (h.at[1] === "bottom") {
                g.top += d;
            } else if (h.at[1] === "center") {
                g.top += d / 2;
            }
            m = q(y.at, p, d);
            g.left += m[0];
            g.top += m[1];
            return this.each(function() {
                var t, e, o = S(this), c = o.outerWidth(), l = o.outerHeight(), a = L(this, "marginLeft"), r = L(this, "marginTop"), s = c + a + L(this, "marginRight") + w.width, i = l + r + L(this, "marginBottom") + w.height, n = S.extend({}, g), f = q(y.my, o.outerWidth(), o.outerHeight());
                if (h.my[0] === "right") {
                    n.left -= c;
                } else if (h.my[0] === "center") {
                    n.left -= c / 2;
                }
                if (h.my[1] === "bottom") {
                    n.top -= l;
                } else if (h.my[1] === "center") {
                    n.top -= l / 2;
                }
                n.left += f[0];
                n.top += f[1];
                t = {
                    marginLeft: a,
                    marginTop: r
                };
                S.each([ "left", "top" ], function(e, a) {
                    if (S.ui.pos[k[e]]) {
                        S.ui.pos[k[e]][a](n, {
                            targetWidth: p,
                            targetHeight: d,
                            elemWidth: c,
                            elemHeight: l,
                            collisionPosition: t,
                            collisionWidth: s,
                            collisionHeight: i,
                            offset: [ m[0] + f[0], m[1] + f[1] ],
                            my: h.my,
                            at: h.at,
                            within: T,
                            elem: o
                        });
                    }
                });
                if (h.using) {
                    e = function(e) {
                        var a = u.left - n.left, t = a + p - c, r = u.top - n.top, s = r + d - l, i = {
                            target: {
                                element: b,
                                left: u.left,
                                top: u.top,
                                width: p,
                                height: d
                            },
                            element: {
                                element: o,
                                left: n.left,
                                top: n.top,
                                width: c,
                                height: l
                            },
                            horizontal: t < 0 ? "left" : a > 0 ? "right" : "center",
                            vertical: s < 0 ? "top" : r > 0 ? "bottom" : "middle"
                        };
                        if (p < c && x(a + t) < p) {
                            i.horizontal = "center";
                        }
                        if (d < l && x(r + s) < d) {
                            i.vertical = "middle";
                        }
                        if (v(x(a), x(t)) > v(x(r), x(s))) {
                            i.important = "horizontal";
                        } else {
                            i.important = "vertical";
                        }
                        h.using.call(this, e, i);
                    };
                }
                o.offset(S.extend(n, {
                    using: e
                }));
            });
        };
        S.ui.pos = {
            _trigger: function(e, a, t, r) {
                if (a.elem) {
                    a.elem.trigger({
                        type: t,
                        position: e,
                        positionData: a,
                        triggered: r
                    });
                }
            },
            fit: {
                left: function(e, a) {
                    S.ui.pos._trigger(e, a, "posCollide", "fitLeft");
                    var t = a.within, r = t.isWindow ? t.scrollLeft : t.offset.left, s = t.width, i = e.left - a.collisionPosition.marginLeft, o = r - i, c = i + a.collisionWidth - s - r, l;
                    if (a.collisionWidth > s) {
                        if (o > 0 && c <= 0) {
                            l = e.left + o + a.collisionWidth - s - r;
                            e.left += o - l;
                        } else if (c > 0 && o <= 0) {
                            e.left = r;
                        } else {
                            if (o > c) {
                                e.left = r + s - a.collisionWidth;
                            } else {
                                e.left = r;
                            }
                        }
                    } else if (o > 0) {
                        e.left += o;
                    } else if (c > 0) {
                        e.left -= c;
                    } else {
                        e.left = v(e.left - i, e.left);
                    }
                    S.ui.pos._trigger(e, a, "posCollided", "fitLeft");
                },
                top: function(e, a) {
                    S.ui.pos._trigger(e, a, "posCollide", "fitTop");
                    var t = a.within, r = t.isWindow ? t.scrollTop : t.offset.top, s = a.within.height, i = e.top - a.collisionPosition.marginTop, o = r - i, c = i + a.collisionHeight - s - r, l;
                    if (a.collisionHeight > s) {
                        if (o > 0 && c <= 0) {
                            l = e.top + o + a.collisionHeight - s - r;
                            e.top += o - l;
                        } else if (c > 0 && o <= 0) {
                            e.top = r;
                        } else {
                            if (o > c) {
                                e.top = r + s - a.collisionHeight;
                            } else {
                                e.top = r;
                            }
                        }
                    } else if (o > 0) {
                        e.top += o;
                    } else if (c > 0) {
                        e.top -= c;
                    } else {
                        e.top = v(e.top - i, e.top);
                    }
                    S.ui.pos._trigger(e, a, "posCollided", "fitTop");
                }
            },
            flip: {
                left: function(e, a) {
                    S.ui.pos._trigger(e, a, "posCollide", "flipLeft");
                    var t = a.within, r = t.offset.left + t.scrollLeft, s = t.width, i = t.isWindow ? t.scrollLeft : t.offset.left, o = e.left - a.collisionPosition.marginLeft, c = o - i, l = o + a.collisionWidth - s - i, n = a.my[0] === "left" ? -a.elemWidth : a.my[0] === "right" ? a.elemWidth : 0, f = a.at[0] === "left" ? a.targetWidth : a.at[0] === "right" ? -a.targetWidth : 0, h = -2 * a.offset[0], m, p;
                    if (c < 0) {
                        m = e.left + n + f + h + a.collisionWidth - s - r;
                        if (m < 0 || m < x(c)) {
                            e.left += n + f + h;
                        }
                    } else if (l > 0) {
                        p = e.left - a.collisionPosition.marginLeft + n + f + h - i;
                        if (p > 0 || x(p) < l) {
                            e.left += n + f + h;
                        }
                    }
                    S.ui.pos._trigger(e, a, "posCollided", "flipLeft");
                },
                top: function(e, a) {
                    S.ui.pos._trigger(e, a, "posCollide", "flipTop");
                    var t = a.within, r = t.offset.top + t.scrollTop, s = t.height, i = t.isWindow ? t.scrollTop : t.offset.top, o = e.top - a.collisionPosition.marginTop, c = o - i, l = o + a.collisionHeight - s - i, n = a.my[1] === "top", f = n ? -a.elemHeight : a.my[1] === "bottom" ? a.elemHeight : 0, h = a.at[1] === "top" ? a.targetHeight : a.at[1] === "bottom" ? -a.targetHeight : 0, m = -2 * a.offset[1], p, d;
                    if (c < 0) {
                        d = e.top + f + h + m + a.collisionHeight - s - r;
                        if (d < 0 || d < x(c)) {
                            e.top += f + h + m;
                        }
                    } else if (l > 0) {
                        p = e.top - a.collisionPosition.marginTop + f + h + m - i;
                        if (p > 0 || x(p) < l) {
                            e.top += f + h + m;
                        }
                    }
                    S.ui.pos._trigger(e, a, "posCollided", "flipTop");
                }
            },
            flipfit: {
                left: function() {
                    S.ui.pos.flip.left.apply(this, arguments);
                    S.ui.pos.fit.left.apply(this, arguments);
                },
                top: function() {
                    S.ui.pos.flip.top.apply(this, arguments);
                    S.ui.pos.fit.top.apply(this, arguments);
                }
            }
        };
        (function() {
            var e, a, t, r, s, i = document.getElementsByTagName("body")[0], o = document.createElement("div");
            e = document.createElement(i ? "div" : "body");
            t = {
                visibility: "hidden",
                width: 0,
                height: 0,
                border: 0,
                margin: 0,
                background: "none"
            };
            if (i) {
                S.extend(t, {
                    position: "absolute",
                    left: "-1000px",
                    top: "-1000px"
                });
            }
            for (s in t) {
                e.style[s] = t[s];
            }
            e.appendChild(o);
            a = i || document.documentElement;
            a.insertBefore(e, a.firstChild);
            o.style.cssText = "position: absolute; left: 10.7432222px;";
            r = S(o).offset().left;
            S.support.offsetFractions = r > 10 && r < 11;
            e.innerHTML = "";
            a.removeChild(e);
        })();
    })();
    var a = S.ui.position;
});

(function(e) {
    "use strict";
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], e);
    } else if (window.jQuery && !window.jQuery.fn.iconpicker) {
        e(window.jQuery);
    }
})(function(l) {
    "use strict";
    var o = {
        isEmpty: function(e) {
            return e === false || e === "" || e === null || e === undefined;
        },
        isEmptyObject: function(e) {
            return this.isEmpty(e) === true || e.length === 0;
        },
        isElement: function(e) {
            return l(e).length > 0;
        },
        isString: function(e) {
            return typeof e === "string" || e instanceof String;
        },
        isArray: function(e) {
            return l.isArray(e);
        },
        inArray: function(e, a) {
            return l.inArray(e, a) !== -1;
        },
        throwError: function(e) {
            throw "Font Awesome Icon Picker Exception: " + e;
        }
    };
    var t = function(e, a) {
        this._id = t._idCounter++;
        this.element = l(e).addClass("iconpicker-element");
        this._trigger("iconpickerCreate", {
            iconpickerValue: this.iconpickerValue
        });
        this.options = l.extend({}, t.defaultOptions, this.element.data(), a);
        this.options.templates = l.extend({}, t.defaultOptions.templates, this.options.templates);
        this.options.originalPlacement = this.options.placement;
        this.container = o.isElement(this.options.container) ? l(this.options.container) : false;
        if (this.container === false) {
            if (this.element.is(".dropdown-toggle")) {
                this.container = l("~ .dropdown-menu:first", this.element);
            } else {
                this.container = this.element.is("input,textarea,button,.btn") ? this.element.parent() : this.element;
            }
        }
        this.container.addClass("iconpicker-container");
        if (this.isDropdownMenu()) {
            this.options.placement = "inline";
        }
        this.input = this.element.is("input,textarea") ? this.element.addClass("iconpicker-input") : false;
        if (this.input === false) {
            this.input = this.container.find(this.options.input);
            if (!this.input.is("input,textarea")) {
                this.input = false;
            }
        }
        this.component = this.isDropdownMenu() ? this.container.parent().find(this.options.component) : this.container.find(this.options.component);
        if (this.component.length === 0) {
            this.component = false;
        } else {
            this.component.find("i").addClass("iconpicker-component");
        }
        this._createPopover();
        this._createIconpicker();
        if (this.getAcceptButton().length === 0) {
            this.options.mustAccept = false;
        }
        if (this.isInputGroup()) {
            this.container.parent().append(this.popover);
        } else {
            this.container.append(this.popover);
        }
        this._bindElementEvents();
        this._bindWindowEvents();
        this.update(this.options.selected);
        if (this.isInline()) {
            this.show();
        }
        this._trigger("iconpickerCreated", {
            iconpickerValue: this.iconpickerValue
        });
    };
    t._idCounter = 0;
    t.defaultOptions = {
        title: false,
        selected: false,
        defaultValue: false,
        placement: "bottom",
        collision: "none",
        animation: true,
        hideOnSelect: false,
        showFooter: false,
        searchInFooter: false,
        mustAccept: false,
        selectedCustomClass: "bg-primary",
        icons: [],
        fullClassFormatter: function(e) {
            return e;
        },
        input: "input,.iconpicker-input",
        inputSearch: false,
        container: false,
        component: ".input-group-addon,.iconpicker-component",
        templates: {
            popover: '<div class="iconpicker-popover popover"><div class="arrow"></div>' + '<div class="popover-title"></div><div class="popover-content"></div></div>',
            footer: '<div class="popover-footer"></div>',
            buttons: '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">Cancel</button>' + ' <button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">Accept</button>',
            search: '<input type="search" class="form-control iconpicker-search" placeholder="Type to filter" />',
            iconpicker: '<div class="iconpicker"><div class="iconpicker-items"></div></div>',
            iconpickerItem: '<a role="button" href="javascript:;" class="iconpicker-item"><i></i></a>'
        }
    };
    t.batch = function(e, a) {
        var t = Array.prototype.slice.call(arguments, 2);
        return l(e).each(function() {
            var e = l(this).data("iconpicker");
            if (!!e) {
                e[a].apply(e, t);
            }
        });
    };
    t.prototype = {
        constructor: t,
        options: {},
        _id: 0,
        _trigger: function(e, a) {
            a = a || {};
            this.element.trigger(l.extend({
                type: e,
                iconpickerInstance: this
            }, a));
        },
        _createPopover: function() {
            this.popover = l(this.options.templates.popover);
            var e = this.popover.find(".popover-title");
            if (!!this.options.title) {
                e.append(l('<div class="popover-title-text">' + this.options.title + "</div>"));
            }
            if (this.hasSeparatedSearchInput() && !this.options.searchInFooter) {
                e.append(this.options.templates.search);
            } else if (!this.options.title) {
                e.remove();
            }
            if (this.options.showFooter && !o.isEmpty(this.options.templates.footer)) {
                var a = l(this.options.templates.footer);
                if (this.hasSeparatedSearchInput() && this.options.searchInFooter) {
                    a.append(l(this.options.templates.search));
                }
                if (!o.isEmpty(this.options.templates.buttons)) {
                    a.append(l(this.options.templates.buttons));
                }
                this.popover.append(a);
            }
            if (this.options.animation === true) {
                this.popover.addClass("fade");
            }
            return this.popover;
        },
        _createIconpicker: function() {
            var t = this;
            this.iconpicker = l(this.options.templates.iconpicker);
            var e = function(e) {
                var a = l(this);
                if (a.is("i")) {
                    a = a.parent();
                }
                t._trigger("iconpickerSelect", {
                    iconpickerItem: a,
                    iconpickerValue: t.iconpickerValue
                });
                if (t.options.mustAccept === false) {
                    t.update(a.data("iconpickerValue"));
                    t._trigger("iconpickerSelected", {
                        iconpickerItem: this,
                        iconpickerValue: t.iconpickerValue
                    });
                } else {
                    t.update(a.data("iconpickerValue"), true);
                }
                if (t.options.hideOnSelect && t.options.mustAccept === false) {
                    t.hide();
                }
            };
            var a = l(this.options.templates.iconpickerItem);
            var r = [];
            for (var s in this.options.icons) {
                if (typeof this.options.icons[s].title === "string") {
                    var i = a.clone();
                    i.find("i").addClass(this.options.fullClassFormatter(this.options.icons[s].title));
                    i.data("iconpickerValue", this.options.icons[s].title).on("click.iconpicker", e);
                    i.attr("title", "." + this.options.icons[s].title);
                    if (this.options.icons[s].searchTerms.length > 0) {
                        var o = "";
                        for (var c = 0; c < this.options.icons[s].searchTerms.length; c++) {
                            o = o + this.options.icons[s].searchTerms[c] + " ";
                        }
                        i.attr("data-search-terms", o);
                    }
                    r.push(i);
                }
            }
            this.iconpicker.find(".iconpicker-items").append(r);
            this.popover.find(".popover-content").append(this.iconpicker);
            return this.iconpicker;
        },
        _isEventInsideIconpicker: function(e) {
            var a = l(e.target);
            if ((!a.hasClass("iconpicker-element") || a.hasClass("iconpicker-element") && !a.is(this.element)) && a.parents(".iconpicker-popover").length === 0) {
                return false;
            }
            return true;
        },
        _bindElementEvents: function() {
            var a = this;
            this.getSearchInput().on("keyup.iconpicker", function() {
                a.filter(l(this).val().toLowerCase());
            });
            this.getAcceptButton().on("click.iconpicker", function() {
                var e = a.iconpicker.find(".iconpicker-selected").get(0);
                a.update(a.iconpickerValue);
                a._trigger("iconpickerSelected", {
                    iconpickerItem: e,
                    iconpickerValue: a.iconpickerValue
                });
                if (!a.isInline()) {
                    a.hide();
                }
            });
            this.getCancelButton().on("click.iconpicker", function() {
                if (!a.isInline()) {
                    a.hide();
                }
            });
            this.element.on("focus.iconpicker", function(e) {
                a.show();
                e.stopPropagation();
            });
            if (this.hasComponent()) {
                this.component.on("click.iconpicker", function() {
                    a.toggle();
                });
            }
            if (this.hasInput()) {
                this.input.on("keyup.iconpicker", function(e) {
                    if (!o.inArray(e.keyCode, [ 38, 40, 37, 39, 16, 17, 18, 9, 8, 91, 93, 20, 46, 186, 190, 46, 78, 188, 44, 86 ])) {
                        a.update();
                    } else {
                        a._updateFormGroupStatus(a.getValid(this.value) !== false);
                    }
                    if (a.options.inputSearch === true) {
                        a.filter(l(this).val().toLowerCase());
                    }
                });
            }
        },
        _bindWindowEvents: function() {
            var e = l(window.document);
            var a = this;
            var t = ".iconpicker.inst" + this._id;
            l(window).on("resize.iconpicker" + t + " orientationchange.iconpicker" + t, function(e) {
                if (a.popover.hasClass("in")) {
                    a.updatePlacement();
                }
            });
            if (!a.isInline()) {
                e.on("mouseup" + t, function(e) {
                    if (!a._isEventInsideIconpicker(e) && !a.isInline()) {
                        a.hide();
                    }
                });
            }
        },
        _unbindElementEvents: function() {
            this.popover.off(".iconpicker");
            this.element.off(".iconpicker");
            if (this.hasInput()) {
                this.input.off(".iconpicker");
            }
            if (this.hasComponent()) {
                this.component.off(".iconpicker");
            }
            if (this.hasContainer()) {
                this.container.off(".iconpicker");
            }
        },
        _unbindWindowEvents: function() {
            l(window).off(".iconpicker.inst" + this._id);
            l(window.document).off(".iconpicker.inst" + this._id);
        },
        updatePlacement: function(e, a) {
            e = e || this.options.placement;
            this.options.placement = e;
            a = a || this.options.collision;
            a = a === true ? "flip" : a;
            var t = {
                at: "right bottom",
                my: "right top",
                of: this.hasInput() && !this.isInputGroup() ? this.input : this.container,
                collision: a === true ? "flip" : a,
                within: window
            };
            this.popover.removeClass("inline topLeftCorner topLeft top topRight topRightCorner " + "rightTop right rightBottom bottomRight bottomRightCorner " + "bottom bottomLeft bottomLeftCorner leftBottom left leftTop");
            if (typeof e === "object") {
                return this.popover.pos(l.extend({}, t, e));
            }
            switch (e) {
              case "inline":
                {
                    t = false;
                }
                break;

              case "topLeftCorner":
                {
                    t.my = "right bottom";
                    t.at = "left top";
                }
                break;

              case "topLeft":
                {
                    t.my = "left bottom";
                    t.at = "left top";
                }
                break;

              case "top":
                {
                    t.my = "center bottom";
                    t.at = "center top";
                }
                break;

              case "topRight":
                {
                    t.my = "right bottom";
                    t.at = "right top";
                }
                break;

              case "topRightCorner":
                {
                    t.my = "left bottom";
                    t.at = "right top";
                }
                break;

              case "rightTop":
                {
                    t.my = "left bottom";
                    t.at = "right center";
                }
                break;

              case "right":
                {
                    t.my = "left center";
                    t.at = "right center";
                }
                break;

              case "rightBottom":
                {
                    t.my = "left top";
                    t.at = "right center";
                }
                break;

              case "bottomRightCorner":
                {
                    t.my = "left top";
                    t.at = "right bottom";
                }
                break;

              case "bottomRight":
                {
                    t.my = "right top";
                    t.at = "right bottom";
                }
                break;

              case "bottom":
                {
                    t.my = "center top";
                    t.at = "center bottom";
                }
                break;

              case "bottomLeft":
                {
                    t.my = "left top";
                    t.at = "left bottom";
                }
                break;

              case "bottomLeftCorner":
                {
                    t.my = "right top";
                    t.at = "left bottom";
                }
                break;

              case "leftBottom":
                {
                    t.my = "right top";
                    t.at = "left center";
                }
                break;

              case "left":
                {
                    t.my = "right center";
                    t.at = "left center";
                }
                break;

              case "leftTop":
                {
                    t.my = "right bottom";
                    t.at = "left center";
                }
                break;

              default:
                {
                    return false;
                }
                break;
            }
            this.popover.css({
                display: this.options.placement === "inline" ? "" : "block"
            });
            if (t !== false) {
                this.popover.pos(t).css("maxWidth", l(window).width() - this.container.offset().left - 5);
            } else {
                this.popover.css({
                    top: "auto",
                    right: "auto",
                    bottom: "auto",
                    left: "auto",
                    maxWidth: "none"
                });
            }
            this.popover.addClass(this.options.placement);
            return true;
        },
        _updateComponents: function() {
            this.iconpicker.find(".iconpicker-item.iconpicker-selected").removeClass("iconpicker-selected " + this.options.selectedCustomClass);
            if (this.iconpickerValue) {
                this.iconpicker.find("." + this.options.fullClassFormatter(this.iconpickerValue).replace(/ /g, ".")).parent().addClass("iconpicker-selected " + this.options.selectedCustomClass);
            }
            if (this.hasComponent()) {
                var e = this.component.find("i");
                if (e.length > 0) {
                    e.attr("class", this.options.fullClassFormatter(this.iconpickerValue));
                } else {
                    this.component.html(this.getHtml());
                }
            }
        },
        _updateFormGroupStatus: function(e) {
            if (this.hasInput()) {
                if (e !== false) {
                    this.input.parents(".form-group:first").removeClass("has-error");
                } else {
                    this.input.parents(".form-group:first").addClass("has-error");
                }
                return true;
            }
            return false;
        },
        getValid: function(e) {
            if (!o.isString(e)) {
                e = "";
            }
            var a = e === "";
            e = l.trim(e);
            var t = false;
            for (var r = 0; r < this.options.icons.length; r++) {
                if (this.options.icons[r].title === e) {
                    t = true;
                    break;
                }
            }
            if (t || a) {
                return e;
            }
            return false;
        },
        setValue: function(e) {
            var a = this.getValid(e);
            if (a !== false) {
                this.iconpickerValue = a;
                this._trigger("iconpickerSetValue", {
                    iconpickerValue: a
                });
                return this.iconpickerValue;
            } else {
                this._trigger("iconpickerInvalid", {
                    iconpickerValue: e
                });
                return false;
            }
        },
        getHtml: function() {
            return '<i class="' + this.options.fullClassFormatter(this.iconpickerValue) + '"></i>';
        },
        setSourceValue: function(e) {
            e = this.setValue(e);
            if (e !== false && e !== "") {
                if (this.hasInput()) {
                    this.input.val(this.iconpickerValue);
                } else {
                    this.element.data("iconpickerValue", this.iconpickerValue);
                }
                this._trigger("iconpickerSetSourceValue", {
                    iconpickerValue: e
                });
            }
            return e;
        },
        getSourceValue: function(e) {
            e = e || this.options.defaultValue;
            var a = e;
            if (this.hasInput()) {
                a = this.input.val();
            } else {
                a = this.element.data("iconpickerValue");
            }
            if (a === undefined || a === "" || a === null || a === false) {
                a = e;
            }
            return a;
        },
        hasInput: function() {
            return this.input !== false;
        },
        isInputSearch: function() {
            return this.hasInput() && this.options.inputSearch === true;
        },
        isInputGroup: function() {
            return this.container.is(".input-group");
        },
        isDropdownMenu: function() {
            return this.container.is(".dropdown-menu");
        },
        hasSeparatedSearchInput: function() {
            return this.options.templates.search !== false && !this.isInputSearch();
        },
        hasComponent: function() {
            return this.component !== false;
        },
        hasContainer: function() {
            return this.container !== false;
        },
        getAcceptButton: function() {
            return this.popover.find(".iconpicker-btn-accept");
        },
        getCancelButton: function() {
            return this.popover.find(".iconpicker-btn-cancel");
        },
        getSearchInput: function() {
            return this.popover.find(".iconpicker-search");
        },
        filter: function(s) {
            if (o.isEmpty(s)) {
                this.iconpicker.find(".iconpicker-item").show();
                return l(false);
            } else {
                var i = [];
                this.iconpicker.find(".iconpicker-item").each(function() {
                    var e = l(this);
                    var a = e.attr("title").toLowerCase();
                    var t = e.attr("data-search-terms") ? e.attr("data-search-terms").toLowerCase() : "";
                    a = a + " " + t;
                    var r = false;
                    try {
                        r = new RegExp("(^|\\W)" + s, "g");
                    } catch (e) {
                        r = false;
                    }
                    if (r !== false && a.match(r)) {
                        i.push(e);
                        e.show();
                    } else {
                        e.hide();
                    }
                });
                return i;
            }
        },
        show: function() {
            if (this.popover.hasClass("in")) {
                return false;
            }
            l.iconpicker.batch(l(".iconpicker-popover.in:not(.inline)").not(this.popover), "hide");
            this._trigger("iconpickerShow", {
                iconpickerValue: this.iconpickerValue
            });
            this.updatePlacement();
            this.popover.addClass("in");
            setTimeout(l.proxy(function() {
                this.popover.css("display", this.isInline() ? "" : "block");
                this._trigger("iconpickerShown", {
                    iconpickerValue: this.iconpickerValue
                });
            }, this), this.options.animation ? 300 : 1);
        },
        hide: function() {
            if (!this.popover.hasClass("in")) {
                return false;
            }
            this._trigger("iconpickerHide", {
                iconpickerValue: this.iconpickerValue
            });
            this.popover.removeClass("in");
            setTimeout(l.proxy(function() {
                this.popover.css("display", "none");
                this.getSearchInput().val("");
                this.filter("");
                this._trigger("iconpickerHidden", {
                    iconpickerValue: this.iconpickerValue
                });
            }, this), this.options.animation ? 300 : 1);
        },
        toggle: function() {
            if (this.popover.is(":visible")) {
                this.hide();
            } else {
                this.show(true);
            }
        },
        update: function(e, a) {
            e = e ? e : this.getSourceValue(this.iconpickerValue);
            this._trigger("iconpickerUpdate", {
                iconpickerValue: this.iconpickerValue
            });
            if (a === true) {
                e = this.setValue(e);
            } else {
                e = this.setSourceValue(e);
                this._updateFormGroupStatus(e !== false);
            }
            if (e !== false) {
                this._updateComponents();
            }
            this._trigger("iconpickerUpdated", {
                iconpickerValue: this.iconpickerValue
            });
            return e;
        },
        destroy: function() {
            this._trigger("iconpickerDestroy", {
                iconpickerValue: this.iconpickerValue
            });
            this.element.removeData("iconpicker").removeData("iconpickerValue").removeClass("iconpicker-element");
            this._unbindElementEvents();
            this._unbindWindowEvents();
            l(this.popover).remove();
            this._trigger("iconpickerDestroyed", {
                iconpickerValue: this.iconpickerValue
            });
        },
        disable: function() {
            if (this.hasInput()) {
                this.input.prop("disabled", true);
                return true;
            }
            return false;
        },
        enable: function() {
            if (this.hasInput()) {
                this.input.prop("disabled", false);
                return true;
            }
            return false;
        },
        isDisabled: function() {
            if (this.hasInput()) {
                return this.input.prop("disabled") === true;
            }
            return false;
        },
        isInline: function() {
            return this.options.placement === "inline" || this.popover.hasClass("inline");
        }
    };
    l.iconpicker = t;
    l.fn.iconpicker = function(a) {
        return this.each(function() {
            var e = l(this);
            if (!e.data("iconpicker")) {
                e.data("iconpicker", new t(this, typeof a === "object" ? a : {}));
            }
        });
    };
    t.defaultOptions = l.extend(t.defaultOptions, {
        icons: [ {
            title: "fas fa-0",
            searchTerms: [ "Digit Zero", "nada", "none", "zero", "zilch" ]
        }, {
            title: "fas fa-1",
            searchTerms: [ "Digit One", "one" ]
        }, {
            title: "fas fa-2",
            searchTerms: [ "Digit Two", "two" ]
        }, {
            title: "fas fa-3",
            searchTerms: [ "Digit Three", "three" ]
        }, {
            title: "fas fa-4",
            searchTerms: [ "Digit Four", "four" ]
        }, {
            title: "fas fa-5",
            searchTerms: [ "Digit Five", "five" ]
        }, {
            title: "fas fa-6",
            searchTerms: [ "Digit Six", "six" ]
        }, {
            title: "fas fa-7",
            searchTerms: [ "Digit Seven", "seven" ]
        }, {
            title: "fas fa-8",
            searchTerms: [ "Digit Eight", "eight" ]
        }, {
            title: "fas fa-9",
            searchTerms: [ "Digit Nine", "nine" ]
        }, {
            title: "fab fa-42-group",
            searchTerms: []
        }, {
            title: "fab fa-500px",
            searchTerms: []
        }, {
            title: "fas fa-a",
            searchTerms: [ "Latin Capital Letter A", "Latin Small Letter A", "letter" ]
        }, {
            title: "fab fa-accessible-icon",
            searchTerms: [ "accessibility", "handicap", "person", "wheelchair", "wheelchair-alt" ]
        }, {
            title: "fab fa-accusoft",
            searchTerms: []
        }, {
            title: "fas fa-address-book",
            searchTerms: [ "contact", "directory", "index", "little black book", "rolodex" ]
        }, {
            title: "far fa-address-book",
            searchTerms: [ "contact", "directory", "index", "little black book", "rolodex" ]
        }, {
            title: "fas fa-address-card",
            searchTerms: [ "about", "contact", "id", "identification", "postcard", "profile", "registration" ]
        }, {
            title: "far fa-address-card",
            searchTerms: [ "about", "contact", "id", "identification", "postcard", "profile", "registration" ]
        }, {
            title: "fab fa-adn",
            searchTerms: []
        }, {
            title: "fab fa-adversal",
            searchTerms: []
        }, {
            title: "fab fa-affiliatetheme",
            searchTerms: []
        }, {
            title: "fab fa-airbnb",
            searchTerms: []
        }, {
            title: "fab fa-algolia",
            searchTerms: []
        }, {
            title: "fas fa-align-center",
            searchTerms: [ "format", "middle", "paragraph", "text" ]
        }, {
            title: "fas fa-align-justify",
            searchTerms: [ "format", "paragraph", "text" ]
        }, {
            title: "fas fa-align-left",
            searchTerms: [ "format", "paragraph", "text" ]
        }, {
            title: "fas fa-align-right",
            searchTerms: [ "format", "paragraph", "text" ]
        }, {
            title: "fab fa-alipay",
            searchTerms: []
        }, {
            title: "fab fa-amazon",
            searchTerms: []
        }, {
            title: "fab fa-amazon-pay",
            searchTerms: []
        }, {
            title: "fab fa-amilia",
            searchTerms: []
        }, {
            title: "fas fa-anchor",
            searchTerms: [ "anchor", "berth", "boat", "dock", "embed", "link", "maritime", "moor", "port", "secure", "ship", "tool" ]
        }, {
            title: "fas fa-anchor-circle-check",
            searchTerms: [ "marina", "not affected", "ok", "okay", "port" ]
        }, {
            title: "fas fa-anchor-circle-exclamation",
            searchTerms: [ "affected", "marina", "port" ]
        }, {
            title: "fas fa-anchor-circle-xmark",
            searchTerms: [ "destroy", "marina", "port" ]
        }, {
            title: "fas fa-anchor-lock",
            searchTerms: [ "closed", "lockdown", "marina", "port", "quarantine" ]
        }, {
            title: "fab fa-android",
            searchTerms: [ "robot" ]
        }, {
            title: "fab fa-angellist",
            searchTerms: []
        }, {
            title: "fas fa-angle-down",
            searchTerms: [ "Down Arrowhead", "arrow", "caret", "download", "expand" ]
        }, {
            title: "fas fa-angle-left",
            searchTerms: [ "Single Left-Pointing Angle Quotation Mark", "arrow", "back", "caret", "less", "previous" ]
        }, {
            title: "fas fa-angle-right",
            searchTerms: [ "Single Right-Pointing Angle Quotation Mark", "arrow", "care", "forward", "more", "next" ]
        }, {
            title: "fas fa-angle-up",
            searchTerms: [ "Up Arrowhead", "arrow", "caret", "collapse", "upload" ]
        }, {
            title: "fas fa-angles-down",
            searchTerms: [ "arrows", "caret", "download", "expand" ]
        }, {
            title: "fas fa-angles-left",
            searchTerms: [ "Left-Pointing Double Angle Quotation Mark", "arrows", "back", "caret", "laquo", "previous", "quote" ]
        }, {
            title: "fas fa-angles-right",
            searchTerms: [ "Right-Pointing Double Angle Quotation Mark", "arrows", "caret", "forward", "more", "next", "quote", "raquo" ]
        }, {
            title: "fas fa-angles-up",
            searchTerms: [ "arrows", "caret", "collapse", "upload" ]
        }, {
            title: "fab fa-angrycreative",
            searchTerms: []
        }, {
            title: "fab fa-angular",
            searchTerms: []
        }, {
            title: "fas fa-ankh",
            searchTerms: [ "Ankh", "amulet", "copper", "coptic christianity", "copts", "crux ansata", "egypt", "venus" ]
        }, {
            title: "fab fa-app-store",
            searchTerms: []
        }, {
            title: "fab fa-app-store-ios",
            searchTerms: []
        }, {
            title: "fab fa-apper",
            searchTerms: []
        }, {
            title: "fab fa-apple",
            searchTerms: [ "fruit", "ios", "mac", "operating system", "os", "osx" ]
        }, {
            title: "fab fa-apple-pay",
            searchTerms: []
        }, {
            title: "fas fa-apple-whole",
            searchTerms: [ "apple", "fall", "fruit", "fuji", "green", "green apple", "macintosh", "orchard", "red", "red apple", "seasonal", "vegan" ]
        }, {
            title: "fas fa-archway",
            searchTerms: [ "arc", "monument", "road", "street", "tunnel" ]
        }, {
            title: "fas fa-arrow-down",
            searchTerms: [ "Downwards Arrow", "download" ]
        }, {
            title: "fas fa-arrow-down-1-9",
            searchTerms: [ "arrange", "filter", "numbers", "order", "sort-numeric-asc" ]
        }, {
            title: "fas fa-arrow-down-9-1",
            searchTerms: [ "arrange", "filter", "numbers", "order", "sort-numeric-asc" ]
        }, {
            title: "fas fa-arrow-down-a-z",
            searchTerms: [ "alphabetical", "arrange", "filter", "order", "sort-alpha-asc" ]
        }, {
            title: "fas fa-arrow-down-long",
            searchTerms: [ "download", "long-arrow-down" ]
        }, {
            title: "fas fa-arrow-down-short-wide",
            searchTerms: [ "arrange", "filter", "order", "sort-amount-asc" ]
        }, {
            title: "fas fa-arrow-down-up-across-line",
            searchTerms: [ "border", "crossing", "transfer" ]
        }, {
            title: "fas fa-arrow-down-up-lock",
            searchTerms: [ "border", "closed", "crossing", "lockdown", "quarantine", "transfer" ]
        }, {
            title: "fas fa-arrow-down-wide-short",
            searchTerms: [ "arrange", "filter", "number", "order", "sort-amount-asc" ]
        }, {
            title: "fas fa-arrow-down-z-a",
            searchTerms: [ "alphabetical", "arrange", "filter", "order", "sort-alpha-asc" ]
        }, {
            title: "fas fa-arrow-left",
            searchTerms: [ "Leftwards Arrow", "back", "previous" ]
        }, {
            title: "fas fa-arrow-left-long",
            searchTerms: [ "back", "long-arrow-left", "previous" ]
        }, {
            title: "fas fa-arrow-pointer",
            searchTerms: [ "arrow", "cursor", "select" ]
        }, {
            title: "fas fa-arrow-right",
            searchTerms: [ "Rightwards Arrow", "forward", "next" ]
        }, {
            title: "fas fa-arrow-right-arrow-left",
            searchTerms: [ "Rightwards Arrow Over Leftwards Arrow", "arrow", "arrows", "reciprocate", "return", "swap", "transfer" ]
        }, {
            title: "fas fa-arrow-right-from-bracket",
            searchTerms: [ "arrow", "exit", "leave", "log out", "logout" ]
        }, {
            title: "fas fa-arrow-right-long",
            searchTerms: [ "forward", "long-arrow-right", "next" ]
        }, {
            title: "fas fa-arrow-right-to-bracket",
            searchTerms: [ "arrow", "enter", "join", "log in", "login", "sign in", "sign up", "sign-in", "signin", "signup" ]
        }, {
            title: "fas fa-arrow-right-to-city",
            searchTerms: [ "building", "city", "exodus", "rural", "urban" ]
        }, {
            title: "fas fa-arrow-rotate-left",
            searchTerms: [ "Anticlockwise Open Circle Arrow", "back", "control z", "exchange", "oops", "return", "rotate", "swap" ]
        }, {
            title: "fas fa-arrow-rotate-right",
            searchTerms: [ "Clockwise Open Circle Arrow", "forward", "refresh", "reload", "repeat" ]
        }, {
            title: "fas fa-arrow-trend-down",
            searchTerms: [ "line", "stocks", "trend" ]
        }, {
            title: "fas fa-arrow-trend-up",
            searchTerms: [ "line", "stocks", "trend" ]
        }, {
            title: "fas fa-arrow-turn-down",
            searchTerms: [ "arrow" ]
        }, {
            title: "fas fa-arrow-turn-up",
            searchTerms: [ "arrow" ]
        }, {
            title: "fas fa-arrow-up",
            searchTerms: [ "Upwards Arrow", "forward", "upload" ]
        }, {
            title: "fas fa-arrow-up-1-9",
            searchTerms: [ "arrange", "filter", "numbers", "order", "sort-numeric-desc" ]
        }, {
            title: "fas fa-arrow-up-9-1",
            searchTerms: [ "arrange", "filter", "numbers", "order", "sort-numeric-desc" ]
        }, {
            title: "fas fa-arrow-up-a-z",
            searchTerms: [ "alphabetical", "arrange", "filter", "order", "sort-alpha-desc" ]
        }, {
            title: "fas fa-arrow-up-from-bracket",
            searchTerms: [ "share", "transfer", "upload" ]
        }, {
            title: "fas fa-arrow-up-from-ground-water",
            searchTerms: [ "groundwater", "spring", "water supply", "water table" ]
        }, {
            title: "fas fa-arrow-up-from-water-pump",
            searchTerms: [ "flood", "groundwater", "pump", "submersible", "sump pump" ]
        }, {
            title: "fas fa-arrow-up-long",
            searchTerms: [ "long-arrow-up", "upload" ]
        }, {
            title: "fas fa-arrow-up-right-dots",
            searchTerms: [ "growth", "increase", "population" ]
        }, {
            title: "fas fa-arrow-up-right-from-square",
            searchTerms: [ "new", "open", "send", "share" ]
        }, {
            title: "fas fa-arrow-up-short-wide",
            searchTerms: [ "arrange", "filter", "order", "sort-amount-desc" ]
        }, {
            title: "fas fa-arrow-up-wide-short",
            searchTerms: [ "arrange", "filter", "order", "sort-amount-desc" ]
        }, {
            title: "fas fa-arrow-up-z-a",
            searchTerms: [ "alphabetical", "arrange", "filter", "order", "sort-alpha-desc" ]
        }, {
            title: "fas fa-arrows-down-to-line",
            searchTerms: [ "scale down", "sink" ]
        }, {
            title: "fas fa-arrows-down-to-people",
            searchTerms: [ "affected", "focus", "targeted" ]
        }, {
            title: "fas fa-arrows-left-right",
            searchTerms: [ "expand", "horizontal", "landscape", "resize", "wide" ]
        }, {
            title: "fas fa-arrows-left-right-to-line",
            searchTerms: [ "analysis", "expand", "gap" ]
        }, {
            title: "fas fa-arrows-rotate",
            searchTerms: [ "Clockwise Right and Left Semicircle Arrows", "exchange", "refresh", "reload", "rotate", "swap" ]
        }, {
            title: "fas fa-arrows-spin",
            searchTerms: [ "cycle", "rotate", "spin", "whirl" ]
        }, {
            title: "fas fa-arrows-split-up-and-left",
            searchTerms: [ "agile", "split" ]
        }, {
            title: "fas fa-arrows-to-circle",
            searchTerms: [ "center", "concentrate", "coordinate", "coordination", "focal point", "focus" ]
        }, {
            title: "fas fa-arrows-to-dot",
            searchTerms: [ "assembly point", "center", "condense", "focus", "minimize" ]
        }, {
            title: "fas fa-arrows-to-eye",
            searchTerms: [ "center", "coordinated assessment", "focus" ]
        }, {
            title: "fas fa-arrows-turn-right",
            searchTerms: [ "arrows" ]
        }, {
            title: "fas fa-arrows-turn-to-dots",
            searchTerms: [ "destination", "nexus" ]
        }, {
            title: "fas fa-arrows-up-down",
            searchTerms: [ "expand", "portrait", "resize", "tall", "vertical" ]
        }, {
            title: "fas fa-arrows-up-down-left-right",
            searchTerms: [ "arrow", "arrows", "bigger", "enlarge", "expand", "fullscreen", "move", "position", "reorder", "resize" ]
        }, {
            title: "fas fa-arrows-up-to-line",
            searchTerms: [ "rise", "scale up" ]
        }, {
            title: "fab fa-artstation",
            searchTerms: []
        }, {
            title: "fas fa-asterisk",
            searchTerms: [ "Asterisk", "Heavy Asterisk", "annotation", "details", "reference", "star" ]
        }, {
            title: "fab fa-asymmetrik",
            searchTerms: []
        }, {
            title: "fas fa-at",
            searchTerms: [ "Commercial At", "address", "author", "e-mail", "email", "fluctuate", "handle" ]
        }, {
            title: "fab fa-atlassian",
            searchTerms: []
        }, {
            title: "fas fa-atom",
            searchTerms: [ "atheism", "atheist", "atom", "atom symbol", "chemistry", "electron", "ion", "isotope", "neutron", "nuclear", "proton", "science" ]
        }, {
            title: "fab fa-audible",
            searchTerms: []
        }, {
            title: "fas fa-audio-description",
            searchTerms: [ "blind", "narration", "video", "visual" ]
        }, {
            title: "fas fa-austral-sign",
            searchTerms: [ "Austral Sign", "currency" ]
        }, {
            title: "fab fa-autoprefixer",
            searchTerms: []
        }, {
            title: "fab fa-avianex",
            searchTerms: []
        }, {
            title: "fab fa-aviato",
            searchTerms: []
        }, {
            title: "fas fa-award",
            searchTerms: [ "honor", "praise", "prize", "recognition", "ribbon", "trophy" ]
        }, {
            title: "fab fa-aws",
            searchTerms: []
        }, {
            title: "fas fa-b",
            searchTerms: [ "Latin Capital Letter B", "Latin Small Letter B", "letter" ]
        }, {
            title: "fas fa-baby",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-baby-carriage",
            searchTerms: [ "buggy", "carrier", "infant", "push", "stroller", "transportation", "walk", "wheels" ]
        }, {
            title: "fas fa-backward",
            searchTerms: [ "arrow", "double", "fast reverse button", "previous", "rewind" ]
        }, {
            title: "fas fa-backward-fast",
            searchTerms: [ "arrow", "beginning", "first", "last track button", "previous", "previous scene", "previous track", "rewind", "start", "triangle" ]
        }, {
            title: "fas fa-backward-step",
            searchTerms: [ "beginning", "first", "previous", "rewind", "start" ]
        }, {
            title: "fas fa-bacon",
            searchTerms: [ "bacon", "blt", "breakfast", "food", "ham", "lard", "meat", "pancetta", "pork", "rasher" ]
        }, {
            title: "fas fa-bacteria",
            searchTerms: [ "antibiotic", "antibody", "covid-19", "health", "organism", "sick" ]
        }, {
            title: "fas fa-bacterium",
            searchTerms: [ "antibiotic", "antibody", "covid-19", "health", "organism", "sick" ]
        }, {
            title: "fas fa-bag-shopping",
            searchTerms: [ "buy", "checkout", "grocery", "payment", "purchase" ]
        }, {
            title: "fas fa-bahai",
            searchTerms: [ "bahai", "bahá'í", "star" ]
        }, {
            title: "fas fa-baht-sign",
            searchTerms: [ "currency" ]
        }, {
            title: "fas fa-ban",
            searchTerms: [ "abort", "ban", "block", "cancel", "delete", "entry", "forbidden", "hide", "no", "not", "prohibit", "prohibited", "remove", "stop", "trash" ]
        }, {
            title: "fas fa-ban-smoking",
            searchTerms: [ "ban", "cancel", "forbidden", "no", "no smoking", "non-smoking", "not", "prohibited", "smoking" ]
        }, {
            title: "fas fa-bandage",
            searchTerms: [ "adhesive bandage", "bandage", "boo boo", "first aid", "ouch" ]
        }, {
            title: "fab fa-bandcamp",
            searchTerms: []
        }, {
            title: "fas fa-bangladeshi-taka-sign",
            searchTerms: [ "bdt", "currency", "tk" ]
        }, {
            title: "fas fa-barcode",
            searchTerms: [ "info", "laser", "price", "scan", "upc" ]
        }, {
            title: "fas fa-bars",
            searchTerms: [ "checklist", "drag", "hamburger", "list", "menu", "nav", "navigation", "ol", "reorder", "settings", "todo", "ul" ]
        }, {
            title: "fas fa-bars-progress",
            searchTerms: [ "checklist", "downloading", "downloads", "loading", "poll", "progress", "project management", "settings", "to do" ]
        }, {
            title: "fas fa-bars-staggered",
            searchTerms: [ "flow", "list", "timeline" ]
        }, {
            title: "fas fa-baseball",
            searchTerms: [ "ball", "baseball", "foul", "glove", "hardball", "league", "leather", "mlb", "softball", "sport", "underarm" ]
        }, {
            title: "fas fa-baseball-bat-ball",
            searchTerms: [ "bat", "league", "mlb", "slugger", "softball", "sport" ]
        }, {
            title: "fas fa-basket-shopping",
            searchTerms: [ "buy", "checkout", "grocery", "payment", "purchase" ]
        }, {
            title: "fas fa-basketball",
            searchTerms: [ "ball", "basketball", "dribble", "dunk", "hoop", "nba" ]
        }, {
            title: "fas fa-bath",
            searchTerms: [ "bath", "bathtub", "clean", "shower", "tub", "wash" ]
        }, {
            title: "fas fa-battery-empty",
            searchTerms: [ "charge", "dead", "power", "status" ]
        }, {
            title: "fas fa-battery-full",
            searchTerms: [ "batter", "battery", "charge", "power", "status" ]
        }, {
            title: "fas fa-battery-half",
            searchTerms: [ "charge", "power", "status" ]
        }, {
            title: "fas fa-battery-quarter",
            searchTerms: [ "charge", "low", "power", "status" ]
        }, {
            title: "fas fa-battery-three-quarters",
            searchTerms: [ "charge", "power", "status" ]
        }, {
            title: "fab fa-battle-net",
            searchTerms: []
        }, {
            title: "fas fa-bed",
            searchTerms: [ "hospital", "hotel", "lodging", "mattress", "patient", "person in bed", "rest", "sleep", "travel" ]
        }, {
            title: "fas fa-bed-pulse",
            searchTerms: [ "EKG", "bed", "electrocardiogram", "health", "hospital", "life", "patient", "vital" ]
        }, {
            title: "fas fa-beer-mug-empty",
            searchTerms: [ "alcohol", "ale", "bar", "beverage", "brew", "brewery", "drink", "foam", "lager", "liquor", "mug", "stein" ]
        }, {
            title: "fab fa-behance",
            searchTerms: []
        }, {
            title: "fas fa-bell",
            searchTerms: [ "alarm", "alert", "bel", "bell", "chime", "notification", "reminder" ]
        }, {
            title: "far fa-bell",
            searchTerms: [ "alarm", "alert", "bel", "bell", "chime", "notification", "reminder" ]
        }, {
            title: "fas fa-bell-concierge",
            searchTerms: [ "attention", "bell", "bellhop", "bellhop bell", "hotel", "receptionist", "service", "support" ]
        }, {
            title: "fas fa-bell-slash",
            searchTerms: [ "alert", "bell", "bell with slash", "cancel", "disabled", "forbidden", "mute", "notification", "off", "quiet", "reminder", "silent" ]
        }, {
            title: "far fa-bell-slash",
            searchTerms: [ "alert", "bell", "bell with slash", "cancel", "disabled", "forbidden", "mute", "notification", "off", "quiet", "reminder", "silent" ]
        }, {
            title: "fas fa-bezier-curve",
            searchTerms: [ "curves", "illustrator", "lines", "path", "vector" ]
        }, {
            title: "fas fa-bicycle",
            searchTerms: [ "bicycle", "bike", "gears", "pedal", "transportation", "vehicle" ]
        }, {
            title: "fab fa-bilibili",
            searchTerms: []
        }, {
            title: "fab fa-bimobject",
            searchTerms: []
        }, {
            title: "fas fa-binoculars",
            searchTerms: [ "glasses", "magnify", "scenic", "spyglass", "view" ]
        }, {
            title: "fas fa-biohazard",
            searchTerms: [ "biohazard", "covid-19", "danger", "dangerous", "epidemic", "hazmat", "medical", "pandemic", "radioactive", "sign", "toxic", "waste", "zombie" ]
        }, {
            title: "fab fa-bitbucket",
            searchTerms: [ "atlassian", "bitbucket-square", "git" ]
        }, {
            title: "fab fa-bitcoin",
            searchTerms: []
        }, {
            title: "fas fa-bitcoin-sign",
            searchTerms: [ "Bitcoin Sign", "currency" ]
        }, {
            title: "fab fa-bity",
            searchTerms: []
        }, {
            title: "fab fa-black-tie",
            searchTerms: []
        }, {
            title: "fab fa-blackberry",
            searchTerms: []
        }, {
            title: "fas fa-blender",
            searchTerms: [ "cocktail", "milkshake", "mixer", "puree", "smoothie" ]
        }, {
            title: "fas fa-blender-phone",
            searchTerms: [ "appliance", "cocktail", "fantasy", "milkshake", "mixer", "puree", "silly", "smoothie" ]
        }, {
            title: "fas fa-blog",
            searchTerms: [ "journal", "log", "online", "personal", "post", "web 2.0", "wordpress", "writing" ]
        }, {
            title: "fab fa-blogger",
            searchTerms: []
        }, {
            title: "fab fa-blogger-b",
            searchTerms: []
        }, {
            title: "fab fa-bluetooth",
            searchTerms: [ "signal" ]
        }, {
            title: "fab fa-bluetooth-b",
            searchTerms: []
        }, {
            title: "fas fa-bold",
            searchTerms: [ "emphasis", "format", "text" ]
        }, {
            title: "fas fa-bolt",
            searchTerms: [ "charge", "danger", "electric", "electricity", "flash", "high voltage", "lightning", "voltage", "weather", "zap" ]
        }, {
            title: "fas fa-bolt-lightning",
            searchTerms: [ "electricity", "flash", "lightning", "weather", "zap" ]
        }, {
            title: "fas fa-bomb",
            searchTerms: [ "bomb", "comic", "error", "explode", "fuse", "grenade", "warning" ]
        }, {
            title: "fas fa-bone",
            searchTerms: [ "bone", "calcium", "dog", "skeletal", "skeleton", "tibia" ]
        }, {
            title: "fas fa-bong",
            searchTerms: [ "aparatus", "cannabis", "marijuana", "pipe", "smoke", "smoking" ]
        }, {
            title: "fas fa-book",
            searchTerms: [ "book", "cover", "decorated", "diary", "documentation", "journal", "library", "notebook", "notebook with decorative cover", "read", "research" ]
        }, {
            title: "fas fa-book-atlas",
            searchTerms: [ "book", "directions", "geography", "globe", "library", "map", "research", "travel", "wayfinding" ]
        }, {
            title: "fas fa-book-bible",
            searchTerms: [ "book", "catholicism", "christianity", "god", "holy" ]
        }, {
            title: "fas fa-book-bookmark",
            searchTerms: [ "library", "research" ]
        }, {
            title: "fas fa-book-journal-whills",
            searchTerms: [ "book", "force", "jedi", "sith", "star wars", "yoda" ]
        }, {
            title: "fas fa-book-medical",
            searchTerms: [ "diary", "documentation", "health", "history", "journal", "library", "read", "record", "research" ]
        }, {
            title: "fas fa-book-open",
            searchTerms: [ "Book", "book", "flyer", "library", "notebook", "open", "open book", "pamphlet", "reading", "research" ]
        }, {
            title: "fas fa-book-open-reader",
            searchTerms: [ "flyer", "library", "notebook", "open book", "pamphlet", "reading", "research" ]
        }, {
            title: "fas fa-book-quran",
            searchTerms: [ "book", "islam", "muslim", "religion" ]
        }, {
            title: "fas fa-book-skull",
            searchTerms: [ "Dungeons & Dragons", "crossbones", "d&d", "dark arts", "death", "dnd", "documentation", "evil", "fantasy", "halloween", "holiday", "library", "necronomicon", "read", "research", "skull", "spell" ]
        }, {
            title: "fas fa-book-tanakh",
            searchTerms: [ "book", "jewish", "judaism", "religion" ]
        }, {
            title: "fas fa-bookmark",
            searchTerms: [ "bookmark", "favorite", "library", "mark", "marker", "read", "remember", "research", "save" ]
        }, {
            title: "far fa-bookmark",
            searchTerms: [ "bookmark", "favorite", "library", "mark", "marker", "read", "remember", "research", "save" ]
        }, {
            title: "fab fa-bootstrap",
            searchTerms: []
        }, {
            title: "fas fa-border-all",
            searchTerms: [ "cell", "grid", "outline", "stroke", "table" ]
        }, {
            title: "fas fa-border-none",
            searchTerms: [ "cell", "grid", "outline", "stroke", "table" ]
        }, {
            title: "fas fa-border-top-left",
            searchTerms: [ "cell", "outline", "stroke", "table" ]
        }, {
            title: "fas fa-bore-hole",
            searchTerms: [ "bore", "bury", "drill", "hole" ]
        }, {
            title: "fab fa-bots",
            searchTerms: []
        }, {
            title: "fas fa-bottle-droplet",
            searchTerms: [ "alcohol", "drink", "oil", "olive oil", "wine" ]
        }, {
            title: "fas fa-bottle-water",
            searchTerms: [ "h2o", "plastic", "water" ]
        }, {
            title: "fas fa-bowl-food",
            searchTerms: [ "catfood", "dogfood", "food", "rice" ]
        }, {
            title: "fas fa-bowl-rice",
            searchTerms: [ "boiled", "cooked", "cooked rice", "rice", "steamed" ]
        }, {
            title: "fas fa-bowling-ball",
            searchTerms: [ "alley", "candlepin", "gutter", "lane", "strike", "tenpin" ]
        }, {
            title: "fas fa-box",
            searchTerms: [ "archive", "box", "container", "package", "parcel", "storage" ]
        }, {
            title: "fas fa-box-archive",
            searchTerms: [ "box", "package", "save", "storage" ]
        }, {
            title: "fas fa-box-open",
            searchTerms: [ "archive", "container", "package", "storage", "unpack" ]
        }, {
            title: "fas fa-box-tissue",
            searchTerms: [ "cough", "covid-19", "kleenex", "mucus", "nose", "sneeze", "snot" ]
        }, {
            title: "fas fa-boxes-packing",
            searchTerms: [ "archive", "box", "package", "storage", "supplies" ]
        }, {
            title: "fas fa-boxes-stacked",
            searchTerms: [ "archives", "inventory", "storage", "warehouse" ]
        }, {
            title: "fas fa-braille",
            searchTerms: [ "alphabet", "blind", "dots", "raised", "vision" ]
        }, {
            title: "fas fa-brain",
            searchTerms: [ "brain", "cerebellum", "gray matter", "intellect", "intelligent", "medulla oblongata", "mind", "noodle", "wit" ]
        }, {
            title: "fas fa-brazilian-real-sign",
            searchTerms: [ "brazilian real sign", "currency" ]
        }, {
            title: "fas fa-bread-slice",
            searchTerms: [ "bake", "bakery", "baking", "dough", "flour", "gluten", "grain", "sandwich", "sourdough", "toast", "wheat", "yeast" ]
        }, {
            title: "fas fa-bridge",
            searchTerms: [ "bridge", "road" ]
        }, {
            title: "fas fa-bridge-circle-check",
            searchTerms: [ "bridge", "not affected", "ok", "okay", "road" ]
        }, {
            title: "fas fa-bridge-circle-exclamation",
            searchTerms: [ "affected", "bridge", "road" ]
        }, {
            title: "fas fa-bridge-circle-xmark",
            searchTerms: [ "bridge", "destroy", "road" ]
        }, {
            title: "fas fa-bridge-lock",
            searchTerms: [ "bridge", "closed", "lockdown", "quarantine", "road" ]
        }, {
            title: "fas fa-bridge-water",
            searchTerms: [ "bridge", "road" ]
        }, {
            title: "fas fa-briefcase",
            searchTerms: [ "bag", "briefcas", "briefcase", "business", "luggage", "office", "work" ]
        }, {
            title: "fas fa-briefcase-medical",
            searchTerms: [ "doctor", "emt", "first aid", "health" ]
        }, {
            title: "fas fa-broom",
            searchTerms: [ "broom", "clean", "cleaning", "firebolt", "fly", "halloween", "nimbus 2000", "quidditch", "sweep", "sweeping", "witch" ]
        }, {
            title: "fas fa-broom-ball",
            searchTerms: [ "ball", "bludger", "broom", "golden snitch", "harry potter", "hogwarts", "quaffle", "sport", "wizard" ]
        }, {
            title: "fas fa-brush",
            searchTerms: [ "art", "bristles", "color", "handle", "paint" ]
        }, {
            title: "fab fa-btc",
            searchTerms: []
        }, {
            title: "fas fa-bucket",
            searchTerms: [ "bucket", "pail", "sandcastle" ]
        }, {
            title: "fab fa-buffer",
            searchTerms: []
        }, {
            title: "fas fa-bug",
            searchTerms: [ "beetle", "error", "glitch", "insect", "repair", "report" ]
        }, {
            title: "fas fa-bug-slash",
            searchTerms: [ "beetle", "fix", "glitch", "insect", "optimize", "repair", "report", "warning" ]
        }, {
            title: "fas fa-bugs",
            searchTerms: [ "bedbug", "infestation", "lice", "plague", "ticks" ]
        }, {
            title: "fas fa-building",
            searchTerms: [ "apartment", "building", "business", "city", "company", "office", "office building", "urban", "work" ]
        }, {
            title: "far fa-building",
            searchTerms: [ "apartment", "building", "business", "city", "company", "office", "office building", "urban", "work" ]
        }, {
            title: "fas fa-building-circle-arrow-right",
            searchTerms: [ "building", "city", "distribution center", "office" ]
        }, {
            title: "fas fa-building-circle-check",
            searchTerms: [ "building", "city", "not affected", "office", "ok", "okay" ]
        }, {
            title: "fas fa-building-circle-exclamation",
            searchTerms: [ "affected", "building", "city", "office" ]
        }, {
            title: "fas fa-building-circle-xmark",
            searchTerms: [ "building", "city", "destroy", "office" ]
        }, {
            title: "fas fa-building-columns",
            searchTerms: [ "bank", "building", "college", "education", "institution", "museum", "students" ]
        }, {
            title: "fas fa-building-flag",
            searchTerms: [ " city", "building", "diplomat", "embassy", "flag", "headquarters", "united nations" ]
        }, {
            title: "fas fa-building-lock",
            searchTerms: [ "building", "city", "closed", "lock", "lockdown", "quarantine", "secure" ]
        }, {
            title: "fas fa-building-ngo",
            searchTerms: [ " city", "building", "non governmental organization", "office" ]
        }, {
            title: "fas fa-building-shield",
            searchTerms: [ "building", "city", "police", "protect", "safety" ]
        }, {
            title: "fas fa-building-un",
            searchTerms: [ "building", "city", "office", "united nations" ]
        }, {
            title: "fas fa-building-user",
            searchTerms: [ "apartment", "building", "city" ]
        }, {
            title: "fas fa-building-wheat",
            searchTerms: [ "agriculture", "building", "city", "usda" ]
        }, {
            title: "fas fa-bullhorn",
            searchTerms: [ "Bullhorn", "announcement", "broadcast", "loud", "louder", "loudspeaker", "megaphone", "public address", "share" ]
        }, {
            title: "fas fa-bullseye",
            searchTerms: [ "archery", "goal", "objective", "strategy", "target" ]
        }, {
            title: "fas fa-burger",
            searchTerms: [ "bacon", "beef", "burger", "burger king", "cheeseburger", "fast food", "grill", "ground beef", "mcdonalds", "sandwich" ]
        }, {
            title: "fab fa-buromobelexperte",
            searchTerms: []
        }, {
            title: "fas fa-burst",
            searchTerms: [ "boom", "crash", "explosion" ]
        }, {
            title: "fas fa-bus",
            searchTerms: [ "bus", "oncoming", "oncoming bus", "public transportation", "transportation", "travel", "vehicle" ]
        }, {
            title: "fas fa-bus-simple",
            searchTerms: [ "mta", "public transportation", "transportation", "travel", "vehicle" ]
        }, {
            title: "fas fa-business-time",
            searchTerms: [ "alarm", "briefcase", "business socks", "clock", "flight of the conchords", "reminder", "wednesday" ]
        }, {
            title: "fab fa-buy-n-large",
            searchTerms: []
        }, {
            title: "fab fa-buysellads",
            searchTerms: []
        }, {
            title: "fas fa-c",
            searchTerms: [ "Latin Capital Letter C", "Latin Small Letter C", "letter" ]
        }, {
            title: "fas fa-cable-car",
            searchTerms: [ "aerial tramway", "cable", "gondola", "lift", "mountain", "mountain cableway", "tram", "tramway", "trolley" ]
        }, {
            title: "fas fa-cake-candles",
            searchTerms: [ "anniversary", "bakery", "birthday", "birthday cake", "cake", "candles", "celebration", "dessert", "frosting", "holiday", "party", "pastry", "sweet" ]
        }, {
            title: "fas fa-calculator",
            searchTerms: [ "Pocket Calculator", "abacus", "addition", "arithmetic", "counting", "math", "multiplication", "subtraction" ]
        }, {
            title: "fas fa-calendar",
            searchTerms: [ "calendar", "calendar-o", "date", "day", "event", "month", "schedule", "tear-off calendar", "time", "when", "year" ]
        }, {
            title: "far fa-calendar",
            searchTerms: [ "calendar", "calendar-o", "date", "day", "event", "month", "schedule", "tear-off calendar", "time", "when", "year" ]
        }, {
            title: "fas fa-calendar-check",
            searchTerms: [ "accept", "agree", "appointment", "confirm", "correct", "date", "day", "done", "event", "month", "ok", "schedule", "select", "success", "tick", "time", "todo", "when", "year" ]
        }, {
            title: "far fa-calendar-check",
            searchTerms: [ "accept", "agree", "appointment", "confirm", "correct", "date", "day", "done", "event", "month", "ok", "schedule", "select", "success", "tick", "time", "todo", "when", "year" ]
        }, {
            title: "fas fa-calendar-day",
            searchTerms: [ "date", "day", "detail", "event", "focus", "month", "schedule", "single day", "time", "today", "when", "year" ]
        }, {
            title: "fas fa-calendar-days",
            searchTerms: [ "calendar", "date", "day", "event", "month", "schedule", "time", "when", "year" ]
        }, {
            title: "far fa-calendar-days",
            searchTerms: [ "calendar", "date", "day", "event", "month", "schedule", "time", "when", "year" ]
        }, {
            title: "fas fa-calendar-minus",
            searchTerms: [ "calendar", "date", "day", "delete", "event", "month", "negative", "remove", "schedule", "time", "when", "year" ]
        }, {
            title: "far fa-calendar-minus",
            searchTerms: [ "calendar", "date", "day", "delete", "event", "month", "negative", "remove", "schedule", "time", "when", "year" ]
        }, {
            title: "fas fa-calendar-plus",
            searchTerms: [ "add", "calendar", "create", "date", "day", "event", "month", "new", "positive", "schedule", "time", "when", "year" ]
        }, {
            title: "far fa-calendar-plus",
            searchTerms: [ "add", "calendar", "create", "date", "day", "event", "month", "new", "positive", "schedule", "time", "when", "year" ]
        }, {
            title: "fas fa-calendar-week",
            searchTerms: [ "date", "day", "detail", "event", "focus", "month", "schedule", "single week", "time", "today", "when", "year" ]
        }, {
            title: "fas fa-calendar-xmark",
            searchTerms: [ "archive", "calendar", "date", "day", "delete", "event", "month", "remove", "schedule", "time", "when", "x", "year" ]
        }, {
            title: "far fa-calendar-xmark",
            searchTerms: [ "archive", "calendar", "date", "day", "delete", "event", "month", "remove", "schedule", "time", "when", "x", "year" ]
        }, {
            title: "fas fa-camera",
            searchTerms: [ "image", "lens", "photo", "picture", "record", "shutter", "video" ]
        }, {
            title: "fas fa-camera-retro",
            searchTerms: [ "camera", "image", "lens", "photo", "picture", "record", "shutter", "video" ]
        }, {
            title: "fas fa-camera-rotate",
            searchTerms: [ "flip", "front-facing", "photo", "selfie" ]
        }, {
            title: "fas fa-campground",
            searchTerms: [ "camping", "fall", "outdoors", "teepee", "tent", "tipi" ]
        }, {
            title: "fab fa-canadian-maple-leaf",
            searchTerms: [ "canada", "flag", "flora", "nature", "plant" ]
        }, {
            title: "fas fa-candy-cane",
            searchTerms: [ "candy", "christmas", "holiday", "mint", "peppermint", "striped", "xmas" ]
        }, {
            title: "fas fa-cannabis",
            searchTerms: [ "bud", "chronic", "drugs", "endica", "endo", "ganja", "marijuana", "mary jane", "pot", "reefer", "sativa", "spliff", "weed", "whacky-tabacky" ]
        }, {
            title: "fas fa-capsules",
            searchTerms: [ "drugs", "medicine", "pills", "prescription" ]
        }, {
            title: "fas fa-car",
            searchTerms: [ "auto", "automobile", "car", "oncoming", "oncoming automobile", "sedan", "transportation", "travel", "vehicle" ]
        }, {
            title: "fas fa-car-battery",
            searchTerms: [ "auto", "electric", "mechanic", "power" ]
        }, {
            title: "fas fa-car-burst",
            searchTerms: [ "accident", "auto", "automobile", "insurance", "sedan", "transportation", "vehicle", "wreck" ]
        }, {
            title: "fas fa-car-on",
            searchTerms: [ "alarm", "car", "carjack", "warning" ]
        }, {
            title: "fas fa-car-rear",
            searchTerms: [ "auto", "automobile", "sedan", "transportation", "travel", "vehicle" ]
        }, {
            title: "fas fa-car-side",
            searchTerms: [ "auto", "automobile", "car", "sedan", "transportation", "travel", "vehicle" ]
        }, {
            title: "fas fa-car-tunnel",
            searchTerms: [ "road", "tunnel" ]
        }, {
            title: "fas fa-caravan",
            searchTerms: [ "camper", "motor home", "rv", "trailer", "travel" ]
        }, {
            title: "fas fa-caret-down",
            searchTerms: [ "arrow", "dropdown", "expand", "menu", "more", "triangle" ]
        }, {
            title: "fas fa-caret-left",
            searchTerms: [ "arrow", "back", "previous", "triangle" ]
        }, {
            title: "fas fa-caret-right",
            searchTerms: [ "arrow", "forward", "next", "triangle" ]
        }, {
            title: "fas fa-caret-up",
            searchTerms: [ "arrow", "collapse", "triangle" ]
        }, {
            title: "fas fa-carrot",
            searchTerms: [ "bugs bunny", "carrot", "food", "orange", "vegan", "vegetable" ]
        }, {
            title: "fas fa-cart-arrow-down",
            searchTerms: [ "download", "save", "shopping" ]
        }, {
            title: "fas fa-cart-flatbed",
            searchTerms: [ "carry", "inventory", "shipping", "transport" ]
        }, {
            title: "fas fa-cart-flatbed-suitcase",
            searchTerms: [ "airport", "bag", "baggage", "suitcase", "travel" ]
        }, {
            title: "fas fa-cart-plus",
            searchTerms: [ "add", "create", "new", "positive", "shopping" ]
        }, {
            title: "fas fa-cart-shopping",
            searchTerms: [ "buy", "cart", "checkout", "grocery", "payment", "purchase", "shopping", "shopping cart", "trolley" ]
        }, {
            title: "fas fa-cash-register",
            searchTerms: [ "buy", "cha-ching", "change", "checkout", "commerce", "leaerboard", "machine", "pay", "payment", "purchase", "store" ]
        }, {
            title: "fas fa-cat",
            searchTerms: [ "cat", "feline", "halloween", "holiday", "kitten", "kitty", "meow", "pet" ]
        }, {
            title: "fab fa-cc-amazon-pay",
            searchTerms: []
        }, {
            title: "fab fa-cc-amex",
            searchTerms: [ "amex" ]
        }, {
            title: "fab fa-cc-apple-pay",
            searchTerms: []
        }, {
            title: "fab fa-cc-diners-club",
            searchTerms: []
        }, {
            title: "fab fa-cc-discover",
            searchTerms: []
        }, {
            title: "fab fa-cc-jcb",
            searchTerms: []
        }, {
            title: "fab fa-cc-mastercard",
            searchTerms: []
        }, {
            title: "fab fa-cc-paypal",
            searchTerms: []
        }, {
            title: "fab fa-cc-stripe",
            searchTerms: []
        }, {
            title: "fab fa-cc-visa",
            searchTerms: []
        }, {
            title: "fas fa-cedi-sign",
            searchTerms: [ "Cedi Sign", "currency" ]
        }, {
            title: "fas fa-cent-sign",
            searchTerms: [ "Cent Sign", "currency" ]
        }, {
            title: "fab fa-centercode",
            searchTerms: []
        }, {
            title: "fab fa-centos",
            searchTerms: [ "linux", "operating system", "os" ]
        }, {
            title: "fas fa-certificate",
            searchTerms: [ "badge", "star", "verified" ]
        }, {
            title: "fas fa-chair",
            searchTerms: [ "chair", "furniture", "seat", "sit" ]
        }, {
            title: "fas fa-chalkboard",
            searchTerms: [ "blackboard", "learning", "school", "teaching", "whiteboard", "writing" ]
        }, {
            title: "fas fa-chalkboard-user",
            searchTerms: [ "blackboard", "instructor", "learning", "professor", "school", "whiteboard", "writing" ]
        }, {
            title: "fas fa-champagne-glasses",
            searchTerms: [ "alcohol", "bar", "beverage", "celebrate", "celebration", "champagne", "clink", "clinking glasses", "drink", "glass", "holiday", "new year's eve", "party", "toast" ]
        }, {
            title: "fas fa-charging-station",
            searchTerms: [ "electric", "ev", "tesla", "vehicle" ]
        }, {
            title: "fas fa-chart-area",
            searchTerms: [ "analytics", "area", "chart", "graph" ]
        }, {
            title: "fas fa-chart-bar",
            searchTerms: [ "analytics", "bar", "chart", "graph" ]
        }, {
            title: "far fa-chart-bar",
            searchTerms: [ "analytics", "bar", "chart", "graph" ]
        }, {
            title: "fas fa-chart-column",
            searchTerms: [ "bar", "bar chart", "chart", "graph", "track", "trend" ]
        }, {
            title: "fas fa-chart-gantt",
            searchTerms: [ "chart", "graph", "track", "trend" ]
        }, {
            title: "fas fa-chart-line",
            searchTerms: [ "activity", "analytics", "chart", "dashboard", "gain", "graph", "increase", "line" ]
        }, {
            title: "fas fa-chart-pie",
            searchTerms: [ "analytics", "chart", "diagram", "graph", "pie" ]
        }, {
            title: "fas fa-chart-simple",
            searchTerms: [ "analytics", "bar", "chart", "column", "graph", "row", "trend" ]
        }, {
            title: "fas fa-check",
            searchTerms: [ "Check Mark", "accept", "agree", "check", "check mark", "checkmark", "confirm", "correct", "done", "mark", "notice", "notification", "notify", "ok", "select", "success", "tick", "todo", "yes", "✓" ]
        }, {
            title: "fas fa-check-double",
            searchTerms: [ "accept", "agree", "checkmark", "confirm", "correct", "done", "notice", "notification", "notify", "ok", "select", "success", "tick", "todo" ]
        }, {
            title: "fas fa-check-to-slot",
            searchTerms: [ "accept", "cast", "election", "politics", "positive", "voting", "yes" ]
        }, {
            title: "fas fa-cheese",
            searchTerms: [ "cheddar", "curd", "gouda", "melt", "parmesan", "sandwich", "swiss", "wedge" ]
        }, {
            title: "fas fa-chess",
            searchTerms: [ "board", "castle", "checkmate", "game", "king", "rook", "strategy", "tournament" ]
        }, {
            title: "fas fa-chess-bishop",
            searchTerms: [ "Black Chess Bishop", "board", "checkmate", "game", "strategy" ]
        }, {
            title: "far fa-chess-bishop",
            searchTerms: [ "Black Chess Bishop", "board", "checkmate", "game", "strategy" ]
        }, {
            title: "fas fa-chess-board",
            searchTerms: [ "board", "checkmate", "game", "strategy" ]
        }, {
            title: "fas fa-chess-king",
            searchTerms: [ "Black Chess King", "board", "checkmate", "game", "strategy" ]
        }, {
            title: "far fa-chess-king",
            searchTerms: [ "Black Chess King", "board", "checkmate", "game", "strategy" ]
        }, {
            title: "fas fa-chess-knight",
            searchTerms: [ "Black Chess Knight", "board", "checkmate", "game", "horse", "strategy" ]
        }, {
            title: "far fa-chess-knight",
            searchTerms: [ "Black Chess Knight", "board", "checkmate", "game", "horse", "strategy" ]
        }, {
            title: "fas fa-chess-pawn",
            searchTerms: [ "board", "checkmate", "chess", "chess pawn", "dupe", "expendable", "game", "strategy" ]
        }, {
            title: "far fa-chess-pawn",
            searchTerms: [ "board", "checkmate", "chess", "chess pawn", "dupe", "expendable", "game", "strategy" ]
        }, {
            title: "fas fa-chess-queen",
            searchTerms: [ "Black Chess Queen", "board", "checkmate", "game", "strategy" ]
        }, {
            title: "far fa-chess-queen",
            searchTerms: [ "Black Chess Queen", "board", "checkmate", "game", "strategy" ]
        }, {
            title: "fas fa-chess-rook",
            searchTerms: [ "Black Chess Rook", "board", "castle", "checkmate", "game", "strategy" ]
        }, {
            title: "far fa-chess-rook",
            searchTerms: [ "Black Chess Rook", "board", "castle", "checkmate", "game", "strategy" ]
        }, {
            title: "fas fa-chevron-down",
            searchTerms: [ "arrow", "download", "expand" ]
        }, {
            title: "fas fa-chevron-left",
            searchTerms: [ "Left-Pointing Angle Bracket", "arrow", "back", "bracket", "previous" ]
        }, {
            title: "fas fa-chevron-right",
            searchTerms: [ "Right-Pointing Angle Bracket", "arrow", "bracket", "forward", "next" ]
        }, {
            title: "fas fa-chevron-up",
            searchTerms: [ "arrow", "collapse", "upload" ]
        }, {
            title: "fas fa-child",
            searchTerms: [ "boy", "girl", "kid", "toddler", "young", "youth" ]
        }, {
            title: "fas fa-child-combatant",
            searchTerms: [ "combatant" ]
        }, {
            title: "fas fa-child-dress",
            searchTerms: [ "boy", "girl", "kid", "toddler", "young", "youth" ]
        }, {
            title: "fas fa-child-reaching",
            searchTerms: [ "boy", "girl", "kid", "toddler", "young", "youth" ]
        }, {
            title: "fas fa-children",
            searchTerms: [ "boy", "child", "girl", "kid", "kids", "young", "youth" ]
        }, {
            title: "fab fa-chrome",
            searchTerms: [ "browser" ]
        }, {
            title: "fab fa-chromecast",
            searchTerms: []
        }, {
            title: "fas fa-church",
            searchTerms: [ "Christian", "building", "cathedral", "chapel", "church", "community", "cross", "religion" ]
        }, {
            title: "fas fa-circle",
            searchTerms: [ "Black Circle", "Black Large Circle", "black circle", "blue", "blue circle", "brown", "brown circle", "chart", "circle", "circle-thin", "diameter", "dot", "ellipse", "fill", "geometric", "green", "green circle", "notification", "orange", "orange circle", "progress", "purple", "purple circle", "red", "red circle", "round", "white circle", "yellow", "yellow circle" ]
        }, {
            title: "far fa-circle",
            searchTerms: [ "Black Circle", "Black Large Circle", "black circle", "blue", "blue circle", "brown", "brown circle", "chart", "circle", "circle-thin", "diameter", "dot", "ellipse", "fill", "geometric", "green", "green circle", "notification", "orange", "orange circle", "progress", "purple", "purple circle", "red", "red circle", "round", "white circle", "yellow", "yellow circle" ]
        }, {
            title: "fas fa-circle-arrow-down",
            searchTerms: [ "download" ]
        }, {
            title: "fas fa-circle-arrow-left",
            searchTerms: [ "back", "previous" ]
        }, {
            title: "fas fa-circle-arrow-right",
            searchTerms: [ "forward", "next" ]
        }, {
            title: "fas fa-circle-arrow-up",
            searchTerms: [ "upload" ]
        }, {
            title: "fas fa-circle-check",
            searchTerms: [ "accept", "affected", "agree", "clear", "confirm", "correct", "done", "ok", "select", "success", "tick", "todo", "yes" ]
        }, {
            title: "far fa-circle-check",
            searchTerms: [ "accept", "affected", "agree", "clear", "confirm", "correct", "done", "ok", "select", "success", "tick", "todo", "yes" ]
        }, {
            title: "fas fa-circle-chevron-down",
            searchTerms: [ "arrow", "download", "dropdown", "menu", "more" ]
        }, {
            title: "fas fa-circle-chevron-left",
            searchTerms: [ "arrow", "back", "previous" ]
        }, {
            title: "fas fa-circle-chevron-right",
            searchTerms: [ "arrow", "forward", "next" ]
        }, {
            title: "fas fa-circle-chevron-up",
            searchTerms: [ "arrow", "collapse", "upload" ]
        }, {
            title: "fas fa-circle-dollar-to-slot",
            searchTerms: [ "contribute", "generosity", "gift", "give" ]
        }, {
            title: "fas fa-circle-dot",
            searchTerms: [ "bullseye", "button", "geometric", "notification", "radio", "radio button", "target" ]
        }, {
            title: "far fa-circle-dot",
            searchTerms: [ "bullseye", "button", "geometric", "notification", "radio", "radio button", "target" ]
        }, {
            title: "fas fa-circle-down",
            searchTerms: [ "arrow-circle-o-down", "download" ]
        }, {
            title: "far fa-circle-down",
            searchTerms: [ "arrow-circle-o-down", "download" ]
        }, {
            title: "fas fa-circle-exclamation",
            searchTerms: [ "affect", "alert", "damage", "danger", "error", "important", "notice", "notification", "notify", "problem", "warning" ]
        }, {
            title: "fas fa-circle-h",
            searchTerms: [ "Circled Latin Capital Letter H", "clinic", "covid-19", "emergency", "letter", "map" ]
        }, {
            title: "fas fa-circle-half-stroke",
            searchTerms: [ "Circle with Left Half Black", "adjust", "chart", "contrast", "dark", "fill", "light", "pie", "progress", "saturation" ]
        }, {
            title: "fas fa-circle-info",
            searchTerms: [ "details", "help", "information", "more", "support" ]
        }, {
            title: "fas fa-circle-left",
            searchTerms: [ "arrow-circle-o-left", "back", "previous" ]
        }, {
            title: "far fa-circle-left",
            searchTerms: [ "arrow-circle-o-left", "back", "previous" ]
        }, {
            title: "fas fa-circle-minus",
            searchTerms: [ "delete", "hide", "negative", "remove", "shape", "trash" ]
        }, {
            title: "fas fa-circle-nodes",
            searchTerms: [ "cluster", "connect", "network" ]
        }, {
            title: "fas fa-circle-notch",
            searchTerms: [ "circle-o-notch", "diameter", "dot", "ellipse", "round", "spinner" ]
        }, {
            title: "fas fa-circle-pause",
            searchTerms: [ "hold", "wait" ]
        }, {
            title: "far fa-circle-pause",
            searchTerms: [ "hold", "wait" ]
        }, {
            title: "fas fa-circle-play",
            searchTerms: [ "audio", "music", "playing", "sound", "start", "video" ]
        }, {
            title: "far fa-circle-play",
            searchTerms: [ "audio", "music", "playing", "sound", "start", "video" ]
        }, {
            title: "fas fa-circle-plus",
            searchTerms: [ "add", "create", "expand", "new", "positive", "shape" ]
        }, {
            title: "fas fa-circle-question",
            searchTerms: [ "help", "information", "support", "unknown" ]
        }, {
            title: "far fa-circle-question",
            searchTerms: [ "help", "information", "support", "unknown" ]
        }, {
            title: "fas fa-circle-radiation",
            searchTerms: [ "danger", "dangerous", "deadly", "hazard", "nuclear", "radioactive", "sign", "warning" ]
        }, {
            title: "fas fa-circle-right",
            searchTerms: [ "arrow-circle-o-right", "forward", "next" ]
        }, {
            title: "far fa-circle-right",
            searchTerms: [ "arrow-circle-o-right", "forward", "next" ]
        }, {
            title: "fas fa-circle-stop",
            searchTerms: [ "block", "box", "circle", "square" ]
        }, {
            title: "far fa-circle-stop",
            searchTerms: [ "block", "box", "circle", "square" ]
        }, {
            title: "fas fa-circle-up",
            searchTerms: [ "arrow-circle-o-up" ]
        }, {
            title: "far fa-circle-up",
            searchTerms: [ "arrow-circle-o-up" ]
        }, {
            title: "fas fa-circle-user",
            searchTerms: [ "users-people" ]
        }, {
            title: "far fa-circle-user",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-circle-xmark",
            searchTerms: [ "close", "cross", "destroy", "exit", "incorrect", "notice", "notification", "notify", "problem", "wrong", "x" ]
        }, {
            title: "far fa-circle-xmark",
            searchTerms: [ "close", "cross", "destroy", "exit", "incorrect", "notice", "notification", "notify", "problem", "wrong", "x" ]
        }, {
            title: "fas fa-city",
            searchTerms: [ "buildings", "busy", "city", "cityscape", "skyscrapers", "urban", "windows" ]
        }, {
            title: "fas fa-clapperboard",
            searchTerms: [ "camera", "clapper", "clapper board", "director", "film", "movie", "record" ]
        }, {
            title: "fas fa-clipboard",
            searchTerms: [ "clipboar", "clipboard", "copy", "notes", "paste", "record" ]
        }, {
            title: "far fa-clipboard",
            searchTerms: [ "clipboar", "clipboard", "copy", "notes", "paste", "record" ]
        }, {
            title: "fas fa-clipboard-check",
            searchTerms: [ "accept", "agree", "confirm", "done", "ok", "select", "success", "tick", "todo", "yes" ]
        }, {
            title: "fas fa-clipboard-list",
            searchTerms: [ "checklist", "completed", "done", "finished", "intinerary", "ol", "schedule", "tick", "todo", "ul" ]
        }, {
            title: "fas fa-clipboard-question",
            searchTerms: [ "assistance", "interview", "query", "question" ]
        }, {
            title: "fas fa-clipboard-user",
            searchTerms: [ "attendance", "record", "roster", "staff" ]
        }, {
            title: "fas fa-clock",
            searchTerms: [ "00", "4", "4:00", "clock", "date", "four", "four o’clock", "hour", "late", "minute", "o'clock", "o’clock", "schedule", "ticking", "time", "timer", "timestamp", "watch" ]
        }, {
            title: "far fa-clock",
            searchTerms: [ "00", "4", "4:00", "clock", "date", "four", "four o’clock", "hour", "late", "minute", "o'clock", "o’clock", "schedule", "ticking", "time", "timer", "timestamp", "watch" ]
        }, {
            title: "fas fa-clock-rotate-left",
            searchTerms: [ "Rewind", "clock", "reverse", "time", "time machine", "time travel" ]
        }, {
            title: "fas fa-clone",
            searchTerms: [ "arrange", "copy", "duplicate", "paste" ]
        }, {
            title: "far fa-clone",
            searchTerms: [ "arrange", "copy", "duplicate", "paste" ]
        }, {
            title: "fas fa-closed-captioning",
            searchTerms: [ "cc", "deaf", "hearing", "subtitle", "subtitling", "text", "video" ]
        }, {
            title: "far fa-closed-captioning",
            searchTerms: [ "cc", "deaf", "hearing", "subtitle", "subtitling", "text", "video" ]
        }, {
            title: "fas fa-cloud",
            searchTerms: [ "atmosphere", "cloud", "fog", "overcast", "save", "upload", "weather" ]
        }, {
            title: "fas fa-cloud-arrow-down",
            searchTerms: [ "download", "export", "save" ]
        }, {
            title: "fas fa-cloud-arrow-up",
            searchTerms: [ "import", "save", "upload" ]
        }, {
            title: "fas fa-cloud-bolt",
            searchTerms: [ "bolt", "cloud", "cloud with lightning", "lightning", "precipitation", "rain", "storm", "weather" ]
        }, {
            title: "fas fa-cloud-meatball",
            searchTerms: [ "FLDSMDFR", "food", "spaghetti", "storm" ]
        }, {
            title: "fas fa-cloud-moon",
            searchTerms: [ "crescent", "evening", "lunar", "night", "partly cloudy", "sky" ]
        }, {
            title: "fas fa-cloud-moon-rain",
            searchTerms: [ "crescent", "evening", "lunar", "night", "partly cloudy", "precipitation", "rain", "sky", "storm" ]
        }, {
            title: "fas fa-cloud-rain",
            searchTerms: [ "Rain", "cloud", "cloud with rain", "precipitation", "rain", "sky", "storm" ]
        }, {
            title: "fas fa-cloud-showers-heavy",
            searchTerms: [ "precipitation", "rain", "sky", "storm" ]
        }, {
            title: "fas fa-cloud-showers-water",
            searchTerms: [ "cloud", "deluge", "flood", "rain", "storm", "surge" ]
        }, {
            title: "fas fa-cloud-sun",
            searchTerms: [ "clear", "cloud", "day", "daytime", "fall", "outdoors", "overcast", "partly cloudy", "sun", "sun behind cloud" ]
        }, {
            title: "fas fa-cloud-sun-rain",
            searchTerms: [ "cloud", "day", "overcast", "precipitation", "rain", "storm", "summer", "sun", "sun behind rain cloud", "sunshower" ]
        }, {
            title: "fab fa-cloudflare",
            searchTerms: []
        }, {
            title: "fab fa-cloudscale",
            searchTerms: []
        }, {
            title: "fab fa-cloudsmith",
            searchTerms: []
        }, {
            title: "fab fa-cloudversify",
            searchTerms: []
        }, {
            title: "fas fa-clover",
            searchTerms: [ "4", "charm", "clover", "four", "four leaf clover", "four-leaf clover", "leaf", "leprechaun", "luck", "lucky" ]
        }, {
            title: "fab fa-cmplid",
            searchTerms: []
        }, {
            title: "fas fa-code",
            searchTerms: [ "brackets", "code", "development", "html" ]
        }, {
            title: "fas fa-code-branch",
            searchTerms: [ "branch", "git", "github", "rebase", "svn", "vcs", "version" ]
        }, {
            title: "fas fa-code-commit",
            searchTerms: [ "commit", "git", "github", "hash", "rebase", "svn", "vcs", "version" ]
        }, {
            title: "fas fa-code-compare",
            searchTerms: [ "compare", "git", "github", "svn", "version" ]
        }, {
            title: "fas fa-code-fork",
            searchTerms: [ "fork", "git", "github", "svn", "version" ]
        }, {
            title: "fas fa-code-merge",
            searchTerms: [ "git", "github", "merge", "pr", "rebase", "svn", "vcs", "version" ]
        }, {
            title: "fas fa-code-pull-request",
            searchTerms: [ "git", "github", "pr", "svn", "version" ]
        }, {
            title: "fab fa-codepen",
            searchTerms: []
        }, {
            title: "fab fa-codiepie",
            searchTerms: []
        }, {
            title: "fas fa-coins",
            searchTerms: [ "currency", "dime", "financial", "gold", "money", "penny" ]
        }, {
            title: "fas fa-colon-sign",
            searchTerms: [ "Colon Sign", "currency" ]
        }, {
            title: "fas fa-comment",
            searchTerms: [ "Right Speech Bubble", "bubble", "chat", "commenting", "conversation", "feedback", "message", "note", "notification", "sms", "speech", "texting" ]
        }, {
            title: "far fa-comment",
            searchTerms: [ "Right Speech Bubble", "bubble", "chat", "commenting", "conversation", "feedback", "message", "note", "notification", "sms", "speech", "texting" ]
        }, {
            title: "fas fa-comment-dollar",
            searchTerms: [ "bubble", "chat", "commenting", "conversation", "feedback", "message", "money", "note", "notification", "pay", "sms", "speech", "spend", "texting", "transfer" ]
        }, {
            title: "fas fa-comment-dots",
            searchTerms: [ "balloon", "bubble", "chat", "comic", "commenting", "conversation", "dialog", "feedback", "message", "more", "note", "notification", "reply", "sms", "speech", "speech balloon", "texting" ]
        }, {
            title: "far fa-comment-dots",
            searchTerms: [ "balloon", "bubble", "chat", "comic", "commenting", "conversation", "dialog", "feedback", "message", "more", "note", "notification", "reply", "sms", "speech", "speech balloon", "texting" ]
        }, {
            title: "fas fa-comment-medical",
            searchTerms: [ "advice", "bubble", "chat", "commenting", "conversation", "diagnose", "feedback", "message", "note", "notification", "prescription", "sms", "speech", "texting" ]
        }, {
            title: "fas fa-comment-slash",
            searchTerms: [ "bubble", "cancel", "chat", "commenting", "conversation", "feedback", "message", "mute", "note", "notification", "quiet", "sms", "speech", "texting" ]
        }, {
            title: "fas fa-comment-sms",
            searchTerms: [ "chat", "conversation", "message", "mobile", "notification", "phone", "sms", "texting" ]
        }, {
            title: "fas fa-comments",
            searchTerms: [ "Two Speech Bubbles", "bubble", "chat", "commenting", "conversation", "feedback", "message", "note", "notification", "sms", "speech", "texting" ]
        }, {
            title: "far fa-comments",
            searchTerms: [ "Two Speech Bubbles", "bubble", "chat", "commenting", "conversation", "feedback", "message", "note", "notification", "sms", "speech", "texting" ]
        }, {
            title: "fas fa-comments-dollar",
            searchTerms: [ "bubble", "chat", "commenting", "conversation", "feedback", "message", "money", "note", "notification", "pay", "sms", "speech", "spend", "texting", "transfer" ]
        }, {
            title: "fas fa-compact-disc",
            searchTerms: [ "Optical Disc Icon", "album", "blu-ray", "bluray", "cd", "computer", "disc", "disk", "dvd", "media", "movie", "music", "optical", "optical disk", "record", "video", "vinyl" ]
        }, {
            title: "fas fa-compass",
            searchTerms: [ "compass", "directions", "directory", "location", "magnetic", "menu", "navigation", "orienteering", "safari", "travel" ]
        }, {
            title: "far fa-compass",
            searchTerms: [ "compass", "directions", "directory", "location", "magnetic", "menu", "navigation", "orienteering", "safari", "travel" ]
        }, {
            title: "fas fa-compass-drafting",
            searchTerms: [ "design", "map", "mechanical drawing", "plot", "plotting" ]
        }, {
            title: "fas fa-compress",
            searchTerms: [ "collapse", "fullscreen", "minimize", "move", "resize", "shrink", "smaller" ]
        }, {
            title: "fas fa-computer",
            searchTerms: [ "computer", "desktop", "display", "monitor", "tower" ]
        }, {
            title: "fas fa-computer-mouse",
            searchTerms: [ "click", "computer", "computer mouse", "cursor", "input", "peripheral" ]
        }, {
            title: "fab fa-confluence",
            searchTerms: [ "atlassian" ]
        }, {
            title: "fab fa-connectdevelop",
            searchTerms: []
        }, {
            title: "fab fa-contao",
            searchTerms: []
        }, {
            title: "fas fa-cookie",
            searchTerms: [ "baked good", "chips", "chocolate", "cookie", "dessert", "eat", "snack", "sweet", "treat" ]
        }, {
            title: "fas fa-cookie-bite",
            searchTerms: [ "baked good", "bitten", "chips", "chocolate", "eat", "snack", "sweet", "treat" ]
        }, {
            title: "fas fa-copy",
            searchTerms: [ "clone", "duplicate", "file", "files-o", "paper", "paste" ]
        }, {
            title: "far fa-copy",
            searchTerms: [ "clone", "duplicate", "file", "files-o", "paper", "paste" ]
        }, {
            title: "fas fa-copyright",
            searchTerms: [ "brand", "c", "copyright", "mark", "register", "trademark" ]
        }, {
            title: "far fa-copyright",
            searchTerms: [ "brand", "c", "copyright", "mark", "register", "trademark" ]
        }, {
            title: "fab fa-cotton-bureau",
            searchTerms: [ "clothing", "t-shirts", "tshirts" ]
        }, {
            title: "fas fa-couch",
            searchTerms: [ "chair", "cushion", "furniture", "relax", "sofa" ]
        }, {
            title: "fas fa-cow",
            searchTerms: [ "agriculture", "animal", "beef", "bovine", "co", "cow", "farm", "fauna", "livestock", "mammal", "milk", "moo" ]
        }, {
            title: "fab fa-cpanel",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-by",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-nc",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-nc-eu",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-nc-jp",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-nd",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-pd",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-pd-alt",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-remix",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-sa",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-sampling",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-sampling-plus",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-share",
            searchTerms: []
        }, {
            title: "fab fa-creative-commons-zero",
            searchTerms: []
        }, {
            title: "fas fa-credit-card",
            searchTerms: [ "buy", "card", "checkout", "credit", "credit card", "credit-card-alt", "debit", "money", "payment", "purchase" ]
        }, {
            title: "far fa-credit-card",
            searchTerms: [ "buy", "card", "checkout", "credit", "credit card", "credit-card-alt", "debit", "money", "payment", "purchase" ]
        }, {
            title: "fab fa-critical-role",
            searchTerms: [ "Dungeons & Dragons", "d&d", "dnd", "fantasy", "game", "gaming", "tabletop" ]
        }, {
            title: "fas fa-crop",
            searchTerms: [ "design", "frame", "mask", "resize", "shrink" ]
        }, {
            title: "fas fa-crop-simple",
            searchTerms: [ "design", "frame", "mask", "resize", "shrink" ]
        }, {
            title: "fas fa-cross",
            searchTerms: [ "Christian", "Heavy Latin Cross", "catholicism", "christianity", "church", "cross", "jesus", "latin cross", "religion" ]
        }, {
            title: "fas fa-crosshairs",
            searchTerms: [ "aim", "bullseye", "gpd", "picker", "position" ]
        }, {
            title: "fas fa-crow",
            searchTerms: [ "bird", "bullfrog", "fauna", "halloween", "holiday", "toad" ]
        }, {
            title: "fas fa-crown",
            searchTerms: [ "award", "clothing", "crown", "favorite", "king", "queen", "royal", "tiara" ]
        }, {
            title: "fas fa-crutch",
            searchTerms: [ "cane", "injury", "mobility", "wheelchair" ]
        }, {
            title: "fas fa-cruzeiro-sign",
            searchTerms: [ "Cruzeiro Sign", "currency" ]
        }, {
            title: "fab fa-css3",
            searchTerms: [ "code" ]
        }, {
            title: "fab fa-css3-alt",
            searchTerms: []
        }, {
            title: "fas fa-cube",
            searchTerms: [ "3d", "block", "dice", "package", "square", "tesseract" ]
        }, {
            title: "fas fa-cubes",
            searchTerms: [ "3d", "block", "dice", "package", "pyramid", "square", "stack", "tesseract" ]
        }, {
            title: "fas fa-cubes-stacked",
            searchTerms: [ "blocks", "cubes", "sugar" ]
        }, {
            title: "fab fa-cuttlefish",
            searchTerms: []
        }, {
            title: "fas fa-d",
            searchTerms: [ "Latin Capital Letter D", "Latin Small Letter D", "letter" ]
        }, {
            title: "fab fa-d-and-d",
            searchTerms: []
        }, {
            title: "fab fa-d-and-d-beyond",
            searchTerms: [ "Dungeons & Dragons", "d&d", "dnd", "fantasy", "gaming", "tabletop" ]
        }, {
            title: "fab fa-dailymotion",
            searchTerms: []
        }, {
            title: "fab fa-dashcube",
            searchTerms: []
        }, {
            title: "fas fa-database",
            searchTerms: [ "computer", "development", "directory", "memory", "storage" ]
        }, {
            title: "fab fa-deezer",
            searchTerms: []
        }, {
            title: "fas fa-delete-left",
            searchTerms: [ "Erase to the Left", "command", "delete", "erase", "keyboard", "undo" ]
        }, {
            title: "fab fa-delicious",
            searchTerms: []
        }, {
            title: "fas fa-democrat",
            searchTerms: [ "american", "democratic party", "donkey", "election", "left", "left-wing", "liberal", "politics", "usa" ]
        }, {
            title: "fab fa-deploydog",
            searchTerms: []
        }, {
            title: "fab fa-deskpro",
            searchTerms: []
        }, {
            title: "fas fa-desktop",
            searchTerms: [ "computer", "cpu", "demo", "desktop", "desktop computer", "device", "imac", "machine", "monitor", "pc", "screen" ]
        }, {
            title: "fab fa-dev",
            searchTerms: []
        }, {
            title: "fab fa-deviantart",
            searchTerms: []
        }, {
            title: "fas fa-dharmachakra",
            searchTerms: [ "Buddhist", "buddhism", "buddhist", "dharma", "religion", "wheel", "wheel of dharma" ]
        }, {
            title: "fab fa-dhl",
            searchTerms: [ "Dalsey", "Hillblom and Lynn", "german", "package", "shipping" ]
        }, {
            title: "fas fa-diagram-next",
            searchTerms: [ "cells", "chart", "gantt", "row", "subtask", "successor", "table" ]
        }, {
            title: "fas fa-diagram-predecessor",
            searchTerms: [ "cells", "chart", "gantt", "predecessor", "previous", "row", "subtask", "table" ]
        }, {
            title: "fas fa-diagram-project",
            searchTerms: [ "chart", "graph", "network", "pert" ]
        }, {
            title: "fas fa-diagram-successor",
            searchTerms: [ "cells", "chart", "gantt", "next", "row", "subtask", "successor", "table" ]
        }, {
            title: "fas fa-diamond",
            searchTerms: [ "card", "cards", "diamond suit", "game", "gem", "gemstone", "poker", "suit" ]
        }, {
            title: "fas fa-diamond-turn-right",
            searchTerms: [ "map", "navigation", "sign", "turn" ]
        }, {
            title: "fab fa-diaspora",
            searchTerms: []
        }, {
            title: "fas fa-dice",
            searchTerms: [ "chance", "dice", "die", "gambling", "game", "game die", "roll" ]
        }, {
            title: "fas fa-dice-d20",
            searchTerms: [ "Dungeons & Dragons", "chance", "d&d", "dnd", "fantasy", "gambling", "game", "roll" ]
        }, {
            title: "fas fa-dice-d6",
            searchTerms: [ "Dungeons & Dragons", "chance", "d&d", "dnd", "fantasy", "gambling", "game", "roll" ]
        }, {
            title: "fas fa-dice-five",
            searchTerms: [ "Die Face-5", "chance", "gambling", "game", "roll" ]
        }, {
            title: "fas fa-dice-four",
            searchTerms: [ "Die Face-4", "chance", "gambling", "game", "roll" ]
        }, {
            title: "fas fa-dice-one",
            searchTerms: [ "Die Face-1", "chance", "gambling", "game", "roll" ]
        }, {
            title: "fas fa-dice-six",
            searchTerms: [ "Die Face-6", "chance", "gambling", "game", "roll" ]
        }, {
            title: "fas fa-dice-three",
            searchTerms: [ "Die Face-3", "chance", "gambling", "game", "roll" ]
        }, {
            title: "fas fa-dice-two",
            searchTerms: [ "Die Face-2", "chance", "gambling", "game", "roll" ]
        }, {
            title: "fab fa-digg",
            searchTerms: []
        }, {
            title: "fab fa-digital-ocean",
            searchTerms: []
        }, {
            title: "fab fa-discord",
            searchTerms: []
        }, {
            title: "fab fa-discourse",
            searchTerms: []
        }, {
            title: "fas fa-disease",
            searchTerms: [ "bacteria", "cancer", "coronavirus", "covid-19", "flu", "illness", "infection", "pandemic", "sickness", "virus" ]
        }, {
            title: "fas fa-display",
            searchTerms: [ "Screen", "computer", "desktop", "imac" ]
        }, {
            title: "fas fa-divide",
            searchTerms: [ "Division Sign", "arithmetic", "calculus", "divide", "division", "math", "sign", "÷" ]
        }, {
            title: "fas fa-dna",
            searchTerms: [ "biologist", "dna", "double helix", "evolution", "gene", "genetic", "genetics", "helix", "life", "molecule", "protein" ]
        }, {
            title: "fab fa-dochub",
            searchTerms: []
        }, {
            title: "fab fa-docker",
            searchTerms: []
        }, {
            title: "fas fa-dog",
            searchTerms: [ "animal", "canine", "dog", "fauna", "mammal", "pet", "pooch", "puppy", "woof" ]
        }, {
            title: "fas fa-dollar-sign",
            searchTerms: [ "Dollar Sign", "currency", "dollar", "heavy dollar sign", "money" ]
        }, {
            title: "fas fa-dolly",
            searchTerms: [ "carry", "shipping", "transport" ]
        }, {
            title: "fas fa-dong-sign",
            searchTerms: [ "Dong Sign", "currency" ]
        }, {
            title: "fas fa-door-closed",
            searchTerms: [ "doo", "door", "enter", "exit", "locked" ]
        }, {
            title: "fas fa-door-open",
            searchTerms: [ "enter", "exit", "welcome" ]
        }, {
            title: "fas fa-dove",
            searchTerms: [ "bird", "dove", "fauna", "fly", "flying", "peace", "war" ]
        }, {
            title: "fas fa-down-left-and-up-right-to-center",
            searchTerms: [ "collapse", "fullscreen", "minimize", "move", "resize", "shrink", "smaller" ]
        }, {
            title: "fas fa-down-long",
            searchTerms: [ "download", "long-arrow-down" ]
        }, {
            title: "fas fa-download",
            searchTerms: [ "export", "hard drive", "save", "transfer" ]
        }, {
            title: "fab fa-draft2digital",
            searchTerms: []
        }, {
            title: "fas fa-dragon",
            searchTerms: [ "Dungeons & Dragons", "d&d", "dnd", "dragon", "fairy tale", "fantasy", "fire", "lizard", "serpent" ]
        }, {
            title: "fas fa-draw-polygon",
            searchTerms: [ "anchors", "lines", "object", "render", "shape" ]
        }, {
            title: "fab fa-dribbble",
            searchTerms: []
        }, {
            title: "fab fa-dropbox",
            searchTerms: []
        }, {
            title: "fas fa-droplet",
            searchTerms: [ "cold", "color", "comic", "drop", "droplet", "raindrop", "sweat", "waterdrop" ]
        }, {
            title: "fas fa-droplet-slash",
            searchTerms: [ "color", "drop", "droplet", "raindrop", "waterdrop" ]
        }, {
            title: "fas fa-drum",
            searchTerms: [ "drum", "drumsticks", "instrument", "music", "percussion", "snare", "sound" ]
        }, {
            title: "fas fa-drum-steelpan",
            searchTerms: [ "calypso", "instrument", "music", "percussion", "reggae", "snare", "sound", "steel", "tropical" ]
        }, {
            title: "fas fa-drumstick-bite",
            searchTerms: [ "bone", "chicken", "leg", "meat", "poultry", "turkey" ]
        }, {
            title: "fab fa-drupal",
            searchTerms: []
        }, {
            title: "fas fa-dumbbell",
            searchTerms: [ "exercise", "gym", "strength", "weight", "weight-lifting" ]
        }, {
            title: "fas fa-dumpster",
            searchTerms: [ "alley", "bin", "commercial", "trash", "waste" ]
        }, {
            title: "fas fa-dumpster-fire",
            searchTerms: [ "alley", "bin", "commercial", "danger", "dangerous", "euphemism", "flame", "heat", "hot", "trash", "waste" ]
        }, {
            title: "fas fa-dungeon",
            searchTerms: [ "Dungeons & Dragons", "building", "d&d", "dnd", "door", "entrance", "fantasy", "gate" ]
        }, {
            title: "fab fa-dyalog",
            searchTerms: []
        }, {
            title: "fas fa-e",
            searchTerms: [ "Latin Capital Letter E", "Latin Small Letter E", "letter" ]
        }, {
            title: "fas fa-ear-deaf",
            searchTerms: [ "ear", "hearing", "sign language" ]
        }, {
            title: "fas fa-ear-listen",
            searchTerms: [ "amplify", "audio", "deaf", "ear", "headset", "hearing", "sound" ]
        }, {
            title: "fab fa-earlybirds",
            searchTerms: []
        }, {
            title: "fas fa-earth-africa",
            searchTerms: [ "africa", "all", "country", "earth", "europe", "global", "globe", "gps", "language", "localize", "location", "map", "online", "place", "planet", "translate", "travel", "world" ]
        }, {
            title: "fas fa-earth-americas",
            searchTerms: [ "all", "america", "country", "earth", "global", "globe", "gps", "language", "localize", "location", "map", "online", "place", "planet", "translate", "travel", "world" ]
        }, {
            title: "fas fa-earth-asia",
            searchTerms: [ "all", "asia", "australia", "country", "earth", "global", "globe", "gps", "language", "localize", "location", "map", "online", "place", "planet", "translate", "travel", "world" ]
        }, {
            title: "fas fa-earth-europe",
            searchTerms: [ "all", "country", "earth", "europe", "global", "globe", "gps", "language", "localize", "location", "map", "online", "place", "planet", "translate", "travel", "world" ]
        }, {
            title: "fas fa-earth-oceania",
            searchTerms: [ "all", "australia", "country", "earth", "global", "globe", "gps", "language", "localize", "location", "map", "melanesia", "micronesia", "new zealand", "online", "place", "planet", "polynesia", "translate", "travel", "world" ]
        }, {
            title: "fab fa-ebay",
            searchTerms: []
        }, {
            title: "fab fa-edge",
            searchTerms: [ "browser", "ie" ]
        }, {
            title: "fab fa-edge-legacy",
            searchTerms: []
        }, {
            title: "fas fa-egg",
            searchTerms: [ "breakfast", "chicken", "easter", "egg", "food", "shell", "yolk" ]
        }, {
            title: "fas fa-eject",
            searchTerms: [ "abort", "cancel", "cd", "discharge", "eject", "eject button" ]
        }, {
            title: "fab fa-elementor",
            searchTerms: []
        }, {
            title: "fas fa-elevator",
            searchTerms: [ "accessibility", "elevator", "hoist", "lift", "users-people" ]
        }, {
            title: "fas fa-ellipsis",
            searchTerms: [ "dots", "drag", "kebab", "list", "menu", "nav", "navigation", "ol", "pacman", "reorder", "settings", "ul" ]
        }, {
            title: "fas fa-ellipsis-vertical",
            searchTerms: [ "dots", "drag", "kebab", "list", "menu", "nav", "navigation", "ol", "reorder", "settings", "ul" ]
        }, {
            title: "fab fa-ello",
            searchTerms: []
        }, {
            title: "fab fa-ember",
            searchTerms: []
        }, {
            title: "fab fa-empire",
            searchTerms: []
        }, {
            title: "fas fa-envelope",
            searchTerms: [ "Back of Envelope", "e-mail", "email", "envelope", "letter", "mail", "message", "notification", "support" ]
        }, {
            title: "far fa-envelope",
            searchTerms: [ "Back of Envelope", "e-mail", "email", "envelope", "letter", "mail", "message", "notification", "support" ]
        }, {
            title: "fas fa-envelope-circle-check",
            searchTerms: [ "check", "email", "envelope", "mail", "not affected", "ok", "okay", "read", "sent" ]
        }, {
            title: "fas fa-envelope-open",
            searchTerms: [ "e-mail", "email", "letter", "mail", "message", "notification", "support" ]
        }, {
            title: "far fa-envelope-open",
            searchTerms: [ "e-mail", "email", "letter", "mail", "message", "notification", "support" ]
        }, {
            title: "fas fa-envelope-open-text",
            searchTerms: [ "e-mail", "email", "letter", "mail", "message", "notification", "support" ]
        }, {
            title: "fas fa-envelopes-bulk",
            searchTerms: [ "archive", "envelope", "letter", "post office", "postal", "postcard", "send", "stamp", "usps" ]
        }, {
            title: "fab fa-envira",
            searchTerms: [ "leaf" ]
        }, {
            title: "fas fa-equals",
            searchTerms: [ "Equals Sign", "arithmetic", "even", "match", "math" ]
        }, {
            title: "fas fa-eraser",
            searchTerms: [ "art", "delete", "remove", "rubber" ]
        }, {
            title: "fab fa-erlang",
            searchTerms: []
        }, {
            title: "fab fa-ethereum",
            searchTerms: []
        }, {
            title: "fas fa-ethernet",
            searchTerms: [ "cable", "cat 5", "cat 6", "connection", "hardware", "internet", "network", "wired" ]
        }, {
            title: "fab fa-etsy",
            searchTerms: []
        }, {
            title: "fas fa-euro-sign",
            searchTerms: [ "Euro Sign", "currency" ]
        }, {
            title: "fab fa-evernote",
            searchTerms: []
        }, {
            title: "fas fa-exclamation",
            searchTerms: [ "!", "Exclamation Mark", "alert", "danger", "error", "exclamation", "important", "mark", "notice", "notification", "notify", "outlined", "problem", "punctuation", "red exclamation mark", "warning", "white exclamation mark" ]
        }, {
            title: "fas fa-expand",
            searchTerms: [ "bigger", "crop", "enlarge", "focus", "fullscreen", "resize", "viewfinder" ]
        }, {
            title: "fab fa-expeditedssl",
            searchTerms: []
        }, {
            title: "fas fa-explosion",
            searchTerms: [ "blast", "blowup", "boom", "crash", "detonation", "explosion" ]
        }, {
            title: "fas fa-eye",
            searchTerms: [ "body", "eye", "look", "optic", "see", "seen", "show", "sight", "views", "visible" ]
        }, {
            title: "far fa-eye",
            searchTerms: [ "body", "eye", "look", "optic", "see", "seen", "show", "sight", "views", "visible" ]
        }, {
            title: "fas fa-eye-dropper",
            searchTerms: [ "beaker", "clone", "color", "copy", "eyedropper", "pipette" ]
        }, {
            title: "fas fa-eye-low-vision",
            searchTerms: [ "blind", "eye", "sight" ]
        }, {
            title: "fas fa-eye-slash",
            searchTerms: [ "blind", "hide", "show", "toggle", "unseen", "views", "visible", "visiblity" ]
        }, {
            title: "far fa-eye-slash",
            searchTerms: [ "blind", "hide", "show", "toggle", "unseen", "views", "visible", "visiblity" ]
        }, {
            title: "fas fa-f",
            searchTerms: [ "Latin Capital Letter F", "Latin Small Letter F", "letter" ]
        }, {
            title: "fas fa-face-angry",
            searchTerms: [ "angry", "angry face", "disapprove", "emoticon", "face", "mad", "upset" ]
        }, {
            title: "far fa-face-angry",
            searchTerms: [ "angry", "angry face", "disapprove", "emoticon", "face", "mad", "upset" ]
        }, {
            title: "fas fa-face-dizzy",
            searchTerms: [ "dazed", "dead", "disapprove", "emoticon", "face" ]
        }, {
            title: "far fa-face-dizzy",
            searchTerms: [ "dazed", "dead", "disapprove", "emoticon", "face" ]
        }, {
            title: "fas fa-face-flushed",
            searchTerms: [ "dazed", "embarrassed", "emoticon", "face", "flushed", "flushed face" ]
        }, {
            title: "far fa-face-flushed",
            searchTerms: [ "dazed", "embarrassed", "emoticon", "face", "flushed", "flushed face" ]
        }, {
            title: "fas fa-face-frown",
            searchTerms: [ "disapprove", "emoticon", "face", "frown", "frowning face", "rating", "sad" ]
        }, {
            title: "far fa-face-frown",
            searchTerms: [ "disapprove", "emoticon", "face", "frown", "frowning face", "rating", "sad" ]
        }, {
            title: "fas fa-face-frown-open",
            searchTerms: [ "disapprove", "emoticon", "face", "frown", "frowning face with open mouth", "mouth", "open", "rating", "sad" ]
        }, {
            title: "far fa-face-frown-open",
            searchTerms: [ "disapprove", "emoticon", "face", "frown", "frowning face with open mouth", "mouth", "open", "rating", "sad" ]
        }, {
            title: "fas fa-face-grimace",
            searchTerms: [ "cringe", "emoticon", "face", "grimace", "grimacing face", "teeth" ]
        }, {
            title: "far fa-face-grimace",
            searchTerms: [ "cringe", "emoticon", "face", "grimace", "grimacing face", "teeth" ]
        }, {
            title: "fas fa-face-grin",
            searchTerms: [ "emoticon", "face", "grin", "grinning face", "laugh", "smile" ]
        }, {
            title: "far fa-face-grin",
            searchTerms: [ "emoticon", "face", "grin", "grinning face", "laugh", "smile" ]
        }, {
            title: "fas fa-face-grin-beam",
            searchTerms: [ "emoticon", "eye", "face", "grinning face with smiling eyes", "laugh", "mouth", "open", "smile" ]
        }, {
            title: "far fa-face-grin-beam",
            searchTerms: [ "emoticon", "eye", "face", "grinning face with smiling eyes", "laugh", "mouth", "open", "smile" ]
        }, {
            title: "fas fa-face-grin-beam-sweat",
            searchTerms: [ "cold", "embarass", "emoticon", "face", "grinning face with sweat", "open", "smile", "sweat" ]
        }, {
            title: "far fa-face-grin-beam-sweat",
            searchTerms: [ "cold", "embarass", "emoticon", "face", "grinning face with sweat", "open", "smile", "sweat" ]
        }, {
            title: "fas fa-face-grin-hearts",
            searchTerms: [ "emoticon", "eye", "face", "love", "smile", "smiling face with heart-eyes" ]
        }, {
            title: "far fa-face-grin-hearts",
            searchTerms: [ "emoticon", "eye", "face", "love", "smile", "smiling face with heart-eyes" ]
        }, {
            title: "fas fa-face-grin-squint",
            searchTerms: [ "emoticon", "face", "grinning squinting face", "laugh", "mouth", "satisfied", "smile" ]
        }, {
            title: "far fa-face-grin-squint",
            searchTerms: [ "emoticon", "face", "grinning squinting face", "laugh", "mouth", "satisfied", "smile" ]
        }, {
            title: "fas fa-face-grin-squint-tears",
            searchTerms: [ "emoticon", "face", "floor", "happy", "laugh", "rolling", "rolling on the floor laughing", "smile" ]
        }, {
            title: "far fa-face-grin-squint-tears",
            searchTerms: [ "emoticon", "face", "floor", "happy", "laugh", "rolling", "rolling on the floor laughing", "smile" ]
        }, {
            title: "fas fa-face-grin-stars",
            searchTerms: [ "emoticon", "eyes", "face", "grinning", "star", "star-struck", "starry-eyed" ]
        }, {
            title: "far fa-face-grin-stars",
            searchTerms: [ "emoticon", "eyes", "face", "grinning", "star", "star-struck", "starry-eyed" ]
        }, {
            title: "fas fa-face-grin-tears",
            searchTerms: [ "LOL", "emoticon", "face", "face with tears of joy", "joy", "laugh", "tear" ]
        }, {
            title: "far fa-face-grin-tears",
            searchTerms: [ "LOL", "emoticon", "face", "face with tears of joy", "joy", "laugh", "tear" ]
        }, {
            title: "fas fa-face-grin-tongue",
            searchTerms: [ "LOL", "emoticon", "face", "face with tongue", "tongue" ]
        }, {
            title: "far fa-face-grin-tongue",
            searchTerms: [ "LOL", "emoticon", "face", "face with tongue", "tongue" ]
        }, {
            title: "fas fa-face-grin-tongue-squint",
            searchTerms: [ "LOL", "emoticon", "eye", "face", "horrible", "squinting face with tongue", "taste", "tongue" ]
        }, {
            title: "far fa-face-grin-tongue-squint",
            searchTerms: [ "LOL", "emoticon", "eye", "face", "horrible", "squinting face with tongue", "taste", "tongue" ]
        }, {
            title: "fas fa-face-grin-tongue-wink",
            searchTerms: [ "LOL", "emoticon", "eye", "face", "joke", "tongue", "wink", "winking face with tongue" ]
        }, {
            title: "far fa-face-grin-tongue-wink",
            searchTerms: [ "LOL", "emoticon", "eye", "face", "joke", "tongue", "wink", "winking face with tongue" ]
        }, {
            title: "fas fa-face-grin-wide",
            searchTerms: [ "emoticon", "face", "grinning face with big eyes", "laugh", "mouth", "open", "smile" ]
        }, {
            title: "far fa-face-grin-wide",
            searchTerms: [ "emoticon", "face", "grinning face with big eyes", "laugh", "mouth", "open", "smile" ]
        }, {
            title: "fas fa-face-grin-wink",
            searchTerms: [ "emoticon", "face", "flirt", "laugh", "smile" ]
        }, {
            title: "far fa-face-grin-wink",
            searchTerms: [ "emoticon", "face", "flirt", "laugh", "smile" ]
        }, {
            title: "fas fa-face-kiss",
            searchTerms: [ "beso", "emoticon", "face", "kiss", "kissing face", "love", "smooch" ]
        }, {
            title: "far fa-face-kiss",
            searchTerms: [ "beso", "emoticon", "face", "kiss", "kissing face", "love", "smooch" ]
        }, {
            title: "fas fa-face-kiss-beam",
            searchTerms: [ "beso", "emoticon", "eye", "face", "kiss", "kissing face with smiling eyes", "love", "smile", "smooch" ]
        }, {
            title: "far fa-face-kiss-beam",
            searchTerms: [ "beso", "emoticon", "eye", "face", "kiss", "kissing face with smiling eyes", "love", "smile", "smooch" ]
        }, {
            title: "fas fa-face-kiss-wink-heart",
            searchTerms: [ "beso", "emoticon", "face", "face blowing a kiss", "kiss", "love", "smooch" ]
        }, {
            title: "far fa-face-kiss-wink-heart",
            searchTerms: [ "beso", "emoticon", "face", "face blowing a kiss", "kiss", "love", "smooch" ]
        }, {
            title: "fas fa-face-laugh",
            searchTerms: [ "LOL", "emoticon", "face", "laugh", "smile" ]
        }, {
            title: "far fa-face-laugh",
            searchTerms: [ "LOL", "emoticon", "face", "laugh", "smile" ]
        }, {
            title: "fas fa-face-laugh-beam",
            searchTerms: [ "LOL", "beaming face with smiling eyes", "emoticon", "eye", "face", "grin", "happy", "smile" ]
        }, {
            title: "far fa-face-laugh-beam",
            searchTerms: [ "LOL", "beaming face with smiling eyes", "emoticon", "eye", "face", "grin", "happy", "smile" ]
        }, {
            title: "fas fa-face-laugh-squint",
            searchTerms: [ "LOL", "emoticon", "face", "happy", "smile" ]
        }, {
            title: "far fa-face-laugh-squint",
            searchTerms: [ "LOL", "emoticon", "face", "happy", "smile" ]
        }, {
            title: "fas fa-face-laugh-wink",
            searchTerms: [ "LOL", "emoticon", "face", "happy", "smile" ]
        }, {
            title: "far fa-face-laugh-wink",
            searchTerms: [ "LOL", "emoticon", "face", "happy", "smile" ]
        }, {
            title: "fas fa-face-meh",
            searchTerms: [ "deadpan", "emoticon", "face", "meh", "neutral", "neutral face", "rating" ]
        }, {
            title: "far fa-face-meh",
            searchTerms: [ "deadpan", "emoticon", "face", "meh", "neutral", "neutral face", "rating" ]
        }, {
            title: "fas fa-face-meh-blank",
            searchTerms: [ "emoticon", "face", "face without mouth", "mouth", "neutral", "quiet", "rating", "silent" ]
        }, {
            title: "far fa-face-meh-blank",
            searchTerms: [ "emoticon", "face", "face without mouth", "mouth", "neutral", "quiet", "rating", "silent" ]
        }, {
            title: "fas fa-face-rolling-eyes",
            searchTerms: [ "emoticon", "eyeroll", "eyes", "face", "face with rolling eyes", "neutral", "rating", "rolling" ]
        }, {
            title: "far fa-face-rolling-eyes",
            searchTerms: [ "emoticon", "eyeroll", "eyes", "face", "face with rolling eyes", "neutral", "rating", "rolling" ]
        }, {
            title: "fas fa-face-sad-cry",
            searchTerms: [ "cry", "emoticon", "face", "loudly crying face", "sad", "sob", "tear", "tears" ]
        }, {
            title: "far fa-face-sad-cry",
            searchTerms: [ "cry", "emoticon", "face", "loudly crying face", "sad", "sob", "tear", "tears" ]
        }, {
            title: "fas fa-face-sad-tear",
            searchTerms: [ "cry", "crying face", "emoticon", "face", "sad", "tear", "tears" ]
        }, {
            title: "far fa-face-sad-tear",
            searchTerms: [ "cry", "crying face", "emoticon", "face", "sad", "tear", "tears" ]
        }, {
            title: "fas fa-face-smile",
            searchTerms: [ "approve", "emoticon", "face", "happy", "rating", "satisfied", "slightly smiling face", "smile" ]
        }, {
            title: "far fa-face-smile",
            searchTerms: [ "approve", "emoticon", "face", "happy", "rating", "satisfied", "slightly smiling face", "smile" ]
        }, {
            title: "fas fa-face-smile-beam",
            searchTerms: [ "blush", "emoticon", "eye", "face", "happy", "positive", "smile", "smiling face with smiling eyes" ]
        }, {
            title: "far fa-face-smile-beam",
            searchTerms: [ "blush", "emoticon", "eye", "face", "happy", "positive", "smile", "smiling face with smiling eyes" ]
        }, {
            title: "fas fa-face-smile-wink",
            searchTerms: [ "emoticon", "face", "happy", "hint", "joke", "wink", "winking face" ]
        }, {
            title: "far fa-face-smile-wink",
            searchTerms: [ "emoticon", "face", "happy", "hint", "joke", "wink", "winking face" ]
        }, {
            title: "fas fa-face-surprise",
            searchTerms: [ "emoticon", "face", "face with open mouth", "mouth", "open", "shocked", "sympathy" ]
        }, {
            title: "far fa-face-surprise",
            searchTerms: [ "emoticon", "face", "face with open mouth", "mouth", "open", "shocked", "sympathy" ]
        }, {
            title: "fas fa-face-tired",
            searchTerms: [ "angry", "emoticon", "face", "grumpy", "tired", "tired face", "upset" ]
        }, {
            title: "far fa-face-tired",
            searchTerms: [ "angry", "emoticon", "face", "grumpy", "tired", "tired face", "upset" ]
        }, {
            title: "fab fa-facebook",
            searchTerms: [ "facebook-official", "social network" ]
        }, {
            title: "fab fa-facebook-f",
            searchTerms: [ "facebook" ]
        }, {
            title: "fab fa-facebook-messenger",
            searchTerms: []
        }, {
            title: "fas fa-fan",
            searchTerms: [ "ac", "air conditioning", "blade", "blower", "cool", "hot" ]
        }, {
            title: "fab fa-fantasy-flight-games",
            searchTerms: [ "Dungeons & Dragons", "d&d", "dnd", "fantasy", "game", "gaming", "tabletop" ]
        }, {
            title: "fas fa-faucet",
            searchTerms: [ "covid-19", "drinking", "drip", "house", "hygiene", "kitchen", "potable", "potable water", "sanitation", "sink", "water" ]
        }, {
            title: "fas fa-faucet-drip",
            searchTerms: [ "drinking", "drip", "house", "hygiene", "kitchen", "potable", "potable water", "sanitation", "sink", "water" ]
        }, {
            title: "fas fa-fax",
            searchTerms: [ "Fax Icon", "business", "communicate", "copy", "facsimile", "fax", "fax machine", "send" ]
        }, {
            title: "fas fa-feather",
            searchTerms: [ "bird", "feather", "flight", "light", "plucked", "plumage", "quill", "write" ]
        }, {
            title: "fas fa-feather-pointed",
            searchTerms: [ "bird", "light", "plucked", "quill", "write" ]
        }, {
            title: "fab fa-fedex",
            searchTerms: [ "Federal Express", "package", "shipping" ]
        }, {
            title: "fab fa-fedora",
            searchTerms: [ "linux", "operating system", "os" ]
        }, {
            title: "fas fa-ferry",
            searchTerms: [ "barge", "boat", "carry", "ferryboat", "ship" ]
        }, {
            title: "fab fa-figma",
            searchTerms: [ "app", "design", "interface" ]
        }, {
            title: "fas fa-file",
            searchTerms: [ "Empty Document", "document", "new", "page", "page facing up", "pdf", "resume" ]
        }, {
            title: "far fa-file",
            searchTerms: [ "Empty Document", "document", "new", "page", "page facing up", "pdf", "resume" ]
        }, {
            title: "fas fa-file-arrow-down",
            searchTerms: [ "document", "export", "save" ]
        }, {
            title: "fas fa-file-arrow-up",
            searchTerms: [ "document", "import", "page", "save" ]
        }, {
            title: "fas fa-file-audio",
            searchTerms: [ "document", "mp3", "music", "page", "play", "sound" ]
        }, {
            title: "far fa-file-audio",
            searchTerms: [ "document", "mp3", "music", "page", "play", "sound" ]
        }, {
            title: "fas fa-file-circle-check",
            searchTerms: [ "document", "file", "not affected", "ok", "okay", "paper" ]
        }, {
            title: "fas fa-file-circle-exclamation",
            searchTerms: [ "document", "file", "paper" ]
        }, {
            title: "fas fa-file-circle-minus",
            searchTerms: [ "document", "file", "paper" ]
        }, {
            title: "fas fa-file-circle-plus",
            searchTerms: [ "add", "document", "file", "new", "page", "paper", "pdf" ]
        }, {
            title: "fas fa-file-circle-question",
            searchTerms: [ "document", "file", "paper" ]
        }, {
            title: "fas fa-file-circle-xmark",
            searchTerms: [ "document", "file", "paper" ]
        }, {
            title: "fas fa-file-code",
            searchTerms: [ "css", "development", "document", "html" ]
        }, {
            title: "far fa-file-code",
            searchTerms: [ "css", "development", "document", "html" ]
        }, {
            title: "fas fa-file-contract",
            searchTerms: [ "agreement", "binding", "document", "legal", "signature" ]
        }, {
            title: "fas fa-file-csv",
            searchTerms: [ "document", "excel", "numbers", "spreadsheets", "table" ]
        }, {
            title: "fas fa-file-excel",
            searchTerms: [ "csv", "document", "numbers", "spreadsheets", "table" ]
        }, {
            title: "far fa-file-excel",
            searchTerms: [ "csv", "document", "numbers", "spreadsheets", "table" ]
        }, {
            title: "fas fa-file-export",
            searchTerms: [ "download", "save" ]
        }, {
            title: "fas fa-file-image",
            searchTerms: [ "Document with Picture", "document", "image", "jpg", "photo", "png" ]
        }, {
            title: "far fa-file-image",
            searchTerms: [ "Document with Picture", "document", "image", "jpg", "photo", "png" ]
        }, {
            title: "fas fa-file-import",
            searchTerms: [ "copy", "document", "send", "upload" ]
        }, {
            title: "fas fa-file-invoice",
            searchTerms: [ "account", "bill", "charge", "document", "payment", "receipt" ]
        }, {
            title: "fas fa-file-invoice-dollar",
            searchTerms: [ "$", "account", "bill", "charge", "document", "dollar-sign", "money", "payment", "receipt", "usd" ]
        }, {
            title: "fas fa-file-lines",
            searchTerms: [ "Document", "Document with Text", "document", "file-text", "invoice", "new", "page", "pdf" ]
        }, {
            title: "far fa-file-lines",
            searchTerms: [ "Document", "Document with Text", "document", "file-text", "invoice", "new", "page", "pdf" ]
        }, {
            title: "fas fa-file-medical",
            searchTerms: [ "document", "health", "history", "prescription", "record" ]
        }, {
            title: "fas fa-file-pdf",
            searchTerms: [ "acrobat", "document", "preview", "save" ]
        }, {
            title: "far fa-file-pdf",
            searchTerms: [ "acrobat", "document", "preview", "save" ]
        }, {
            title: "fas fa-file-pen",
            searchTerms: [ "edit", "memo", "pen", "pencil", "update", "write" ]
        }, {
            title: "fas fa-file-powerpoint",
            searchTerms: [ "display", "document", "keynote", "presentation" ]
        }, {
            title: "far fa-file-powerpoint",
            searchTerms: [ "display", "document", "keynote", "presentation" ]
        }, {
            title: "fas fa-file-prescription",
            searchTerms: [ "document", "drugs", "medical", "medicine", "rx" ]
        }, {
            title: "fas fa-file-shield",
            searchTerms: [ "antivirus", "data", "document", "protect", "safe", "safety", "secure" ]
        }, {
            title: "fas fa-file-signature",
            searchTerms: [ "John Hancock", "contract", "document", "name" ]
        }, {
            title: "fas fa-file-video",
            searchTerms: [ "document", "m4v", "movie", "mp4", "play" ]
        }, {
            title: "far fa-file-video",
            searchTerms: [ "document", "m4v", "movie", "mp4", "play" ]
        }, {
            title: "fas fa-file-waveform",
            searchTerms: [ "document", "health", "history", "prescription", "record" ]
        }, {
            title: "fas fa-file-word",
            searchTerms: [ "document", "edit", "page", "text", "writing" ]
        }, {
            title: "far fa-file-word",
            searchTerms: [ "document", "edit", "page", "text", "writing" ]
        }, {
            title: "fas fa-file-zipper",
            searchTerms: [ ".zip", "bundle", "compress", "compression", "download", "zip" ]
        }, {
            title: "far fa-file-zipper",
            searchTerms: [ ".zip", "bundle", "compress", "compression", "download", "zip" ]
        }, {
            title: "fas fa-fill",
            searchTerms: [ "bucket", "color", "paint", "paint bucket" ]
        }, {
            title: "fas fa-fill-drip",
            searchTerms: [ "bucket", "color", "drop", "paint", "paint bucket", "spill" ]
        }, {
            title: "fas fa-film",
            searchTerms: [ "cinema", "film", "film frames", "frames", "movie", "strip", "video" ]
        }, {
            title: "fas fa-filter",
            searchTerms: [ "funnel", "options", "separate", "sort" ]
        }, {
            title: "fas fa-filter-circle-dollar",
            searchTerms: [ "filter", "money", "options", "separate", "sort" ]
        }, {
            title: "fas fa-filter-circle-xmark",
            searchTerms: [ "cancel", "funnel", "options", "remove", "separate", "sort" ]
        }, {
            title: "fas fa-fingerprint",
            searchTerms: [ "human", "id", "identification", "lock", "smudge", "touch", "unique", "unlock" ]
        }, {
            title: "fas fa-fire",
            searchTerms: [ "burn", "caliente", "fire", "flame", "heat", "hot", "popular", "tool" ]
        }, {
            title: "fas fa-fire-burner",
            searchTerms: [ "cook", "fire", "flame", "kitchen", "stove" ]
        }, {
            title: "fas fa-fire-extinguisher",
            searchTerms: [ "burn", "caliente", "extinguish", "fire", "fire extinguisher", "fire fighter", "flame", "heat", "hot", "quench", "rescue" ]
        }, {
            title: "fas fa-fire-flame-curved",
            searchTerms: [ "burn", "caliente", "flame", "heat", "hot", "popular" ]
        }, {
            title: "fas fa-fire-flame-simple",
            searchTerms: [ "caliente", "energy", "fire", "flame", "gas", "heat", "hot" ]
        }, {
            title: "fab fa-firefox",
            searchTerms: [ "browser" ]
        }, {
            title: "fab fa-firefox-browser",
            searchTerms: [ "browser" ]
        }, {
            title: "fab fa-first-order",
            searchTerms: []
        }, {
            title: "fab fa-first-order-alt",
            searchTerms: []
        }, {
            title: "fab fa-firstdraft",
            searchTerms: []
        }, {
            title: "fas fa-fish",
            searchTerms: [ "Pisces", "fauna", "fish", "gold", "seafood", "swimming", "zodiac" ]
        }, {
            title: "fas fa-fish-fins",
            searchTerms: [ "fish", "fishery", "pisces", "seafood" ]
        }, {
            title: "fas fa-flag",
            searchTerms: [ "black flag", "country", "notice", "notification", "notify", "pole", "report", "symbol", "waving" ]
        }, {
            title: "far fa-flag",
            searchTerms: [ "black flag", "country", "notice", "notification", "notify", "pole", "report", "symbol", "waving" ]
        }, {
            title: "fas fa-flag-checkered",
            searchTerms: [ "checkered", "chequered", "chequered flag", "finish", "notice", "notification", "notify", "pole", "racing", "report", "start", "symbol", "win" ]
        }, {
            title: "fas fa-flag-usa",
            searchTerms: [ "betsy ross", "country", "fla", "flag: United States", "old glory", "stars", "stripes", "symbol" ]
        }, {
            title: "fas fa-flask",
            searchTerms: [ "beaker", "chemicals", "experiment", "experimental", "labs", "liquid", "potion", "science", "vial" ]
        }, {
            title: "fas fa-flask-vial",
            searchTerms: [ " beaker", " chemicals", " experiment", " experimental", " labs", " liquid", " science", " vial", "ampule", "chemistry", "lab", "laboratory", "potion", "test", "test tube" ]
        }, {
            title: "fab fa-flickr",
            searchTerms: []
        }, {
            title: "fab fa-flipboard",
            searchTerms: []
        }, {
            title: "fas fa-floppy-disk",
            searchTerms: [ "Black Hard Shell Floppy Disk", "computer", "disk", "download", "floppy", "floppy disk", "floppy-o" ]
        }, {
            title: "far fa-floppy-disk",
            searchTerms: [ "Black Hard Shell Floppy Disk", "computer", "disk", "download", "floppy", "floppy disk", "floppy-o" ]
        }, {
            title: "fas fa-florin-sign",
            searchTerms: [ "currency" ]
        }, {
            title: "fab fa-fly",
            searchTerms: []
        }, {
            title: "fas fa-folder",
            searchTerms: [ "Black Folder", "archive", "directory", "document", "file", "file folder", "folder" ]
        }, {
            title: "far fa-folder",
            searchTerms: [ "Black Folder", "archive", "directory", "document", "file", "file folder", "folder" ]
        }, {
            title: "fas fa-folder-closed",
            searchTerms: [ "file" ]
        }, {
            title: "far fa-folder-closed",
            searchTerms: [ "file" ]
        }, {
            title: "fas fa-folder-minus",
            searchTerms: [ "archive", "delete", "directory", "document", "file", "negative", "remove" ]
        }, {
            title: "fas fa-folder-open",
            searchTerms: [ "Open Folder", "archive", "directory", "document", "empty", "file", "folder", "new", "open", "open file folder" ]
        }, {
            title: "far fa-folder-open",
            searchTerms: [ "Open Folder", "archive", "directory", "document", "empty", "file", "folder", "new", "open", "open file folder" ]
        }, {
            title: "fas fa-folder-plus",
            searchTerms: [ "add", "archive", "create", "directory", "document", "file", "new", "positive" ]
        }, {
            title: "fas fa-folder-tree",
            searchTerms: [ "archive", "directory", "document", "file", "search", "structure" ]
        }, {
            title: "fas fa-font",
            searchTerms: [ "alphabet", "glyph", "text", "type", "typeface" ]
        }, {
            title: "fas fa-font-awesome",
            searchTerms: [ "awesome", "flag", "font", "icons", "typeface" ]
        }, {
            title: "far fa-font-awesome",
            searchTerms: [ "awesome", "flag", "font", "icons", "typeface" ]
        }, {
            title: "fab fa-font-awesome",
            searchTerms: [ "awesome", "flag", "font", "icons", "typeface" ]
        }, {
            title: "fab fa-fonticons",
            searchTerms: []
        }, {
            title: "fab fa-fonticons-fi",
            searchTerms: []
        }, {
            title: "fas fa-football",
            searchTerms: [ "american", "american football", "ball", "fall", "football", "nfl", "pigskin", "seasonal" ]
        }, {
            title: "fab fa-fort-awesome",
            searchTerms: [ "castle" ]
        }, {
            title: "fab fa-fort-awesome-alt",
            searchTerms: [ "castle" ]
        }, {
            title: "fab fa-forumbee",
            searchTerms: []
        }, {
            title: "fas fa-forward",
            searchTerms: [ "arrow", "double", "fast", "fast-forward button", "forward", "next", "skip" ]
        }, {
            title: "fas fa-forward-fast",
            searchTerms: [ "arrow", "end", "last", "next", "next scene", "next track", "next track button", "triangle" ]
        }, {
            title: "fas fa-forward-step",
            searchTerms: [ "end", "last", "next" ]
        }, {
            title: "fab fa-foursquare",
            searchTerms: []
        }, {
            title: "fas fa-franc-sign",
            searchTerms: [ "French Franc Sign", "currency" ]
        }, {
            title: "fab fa-free-code-camp",
            searchTerms: []
        }, {
            title: "fab fa-freebsd",
            searchTerms: []
        }, {
            title: "fas fa-frog",
            searchTerms: [ "amphibian", "bullfrog", "fauna", "hop", "kermit", "kiss", "prince", "ribbit", "toad", "wart" ]
        }, {
            title: "fab fa-fulcrum",
            searchTerms: []
        }, {
            title: "fas fa-futbol",
            searchTerms: [ "ball", "football", "mls", "soccer", "soccer ball" ]
        }, {
            title: "far fa-futbol",
            searchTerms: [ "ball", "football", "mls", "soccer", "soccer ball" ]
        }, {
            title: "fas fa-g",
            searchTerms: [ "Latin Capital Letter G", "Latin Small Letter G", "letter" ]
        }, {
            title: "fab fa-galactic-republic",
            searchTerms: [ "politics", "star wars" ]
        }, {
            title: "fab fa-galactic-senate",
            searchTerms: [ "star wars" ]
        }, {
            title: "fas fa-gamepad",
            searchTerms: [ "arcade", "controller", "d-pad", "joystick", "video", "video game" ]
        }, {
            title: "fas fa-gas-pump",
            searchTerms: [ "car", "diesel", "fuel", "fuel pump", "fuelpump", "gas", "gasoline", "petrol", "pump", "station" ]
        }, {
            title: "fas fa-gauge",
            searchTerms: [ "dashboard", "fast", "odometer", "speed", "speedometer" ]
        }, {
            title: "fas fa-gauge-high",
            searchTerms: [ "dashboard", "fast", "odometer", "speed", "speedometer" ]
        }, {
            title: "fas fa-gauge-simple",
            searchTerms: [ "dashboard", "fast", "odometer", "speed", "speedometer" ]
        }, {
            title: "fas fa-gauge-simple-high",
            searchTerms: [ "dashboard", "fast", "odometer", "speed", "speedometer" ]
        }, {
            title: "fas fa-gavel",
            searchTerms: [ "hammer", "judge", "law", "lawyer", "opinion" ]
        }, {
            title: "fas fa-gear",
            searchTerms: [ "cog", "cogwheel", "gear", "mechanical", "settings", "sprocket", "tool", "wheel" ]
        }, {
            title: "fas fa-gears",
            searchTerms: [ "gears", "mechanical", "settings", "sprocket", "wheel" ]
        }, {
            title: "fas fa-gem",
            searchTerms: [ "diamond", "gem", "gem stone", "jewel", "jewelry", "sapphire", "stone", "treasure" ]
        }, {
            title: "far fa-gem",
            searchTerms: [ "diamond", "gem", "gem stone", "jewel", "jewelry", "sapphire", "stone", "treasure" ]
        }, {
            title: "fas fa-genderless",
            searchTerms: [ "androgynous", "asexual", "gender", "sexless" ]
        }, {
            title: "fab fa-get-pocket",
            searchTerms: []
        }, {
            title: "fab fa-gg",
            searchTerms: []
        }, {
            title: "fab fa-gg-circle",
            searchTerms: []
        }, {
            title: "fas fa-ghost",
            searchTerms: [ "apparition", "blinky", "clyde", "creature", "face", "fairy tale", "fantasy", "floating", "ghost", "halloween", "holiday", "inky", "monster", "pacman", "pinky", "spirit" ]
        }, {
            title: "fas fa-gift",
            searchTerms: [ "box", "celebration", "christmas", "generosity", "gift", "giving", "holiday", "party", "present", "wrapped", "wrapped gift", "xmas" ]
        }, {
            title: "fas fa-gifts",
            searchTerms: [ "christmas", "generosity", "giving", "holiday", "party", "present", "wrapped", "xmas" ]
        }, {
            title: "fab fa-git",
            searchTerms: []
        }, {
            title: "fab fa-git-alt",
            searchTerms: []
        }, {
            title: "fab fa-github",
            searchTerms: [ "octocat" ]
        }, {
            title: "fab fa-github-alt",
            searchTerms: [ "octocat" ]
        }, {
            title: "fab fa-gitkraken",
            searchTerms: []
        }, {
            title: "fab fa-gitlab",
            searchTerms: [ "Axosoft" ]
        }, {
            title: "fab fa-gitter",
            searchTerms: []
        }, {
            title: "fas fa-glass-water",
            searchTerms: [ "potable", "water" ]
        }, {
            title: "fas fa-glass-water-droplet",
            searchTerms: [ "potable", "water" ]
        }, {
            title: "fas fa-glasses",
            searchTerms: [ "hipster", "nerd", "reading", "sight", "spectacles", "vision" ]
        }, {
            title: "fab fa-glide",
            searchTerms: []
        }, {
            title: "fab fa-glide-g",
            searchTerms: []
        }, {
            title: "fas fa-globe",
            searchTerms: [ "all", "coordinates", "country", "earth", "global", "globe", "globe with meridians", "gps", "internet", "language", "localize", "location", "map", "meridians", "network", "online", "place", "planet", "translate", "travel", "world" ]
        }, {
            title: "fab fa-gofore",
            searchTerms: []
        }, {
            title: "fab fa-golang",
            searchTerms: []
        }, {
            title: "fas fa-golf-ball-tee",
            searchTerms: [ "caddy", "eagle", "putt", "tee" ]
        }, {
            title: "fab fa-goodreads",
            searchTerms: []
        }, {
            title: "fab fa-goodreads-g",
            searchTerms: []
        }, {
            title: "fab fa-google",
            searchTerms: []
        }, {
            title: "fab fa-google-drive",
            searchTerms: []
        }, {
            title: "fab fa-google-pay",
            searchTerms: []
        }, {
            title: "fab fa-google-play",
            searchTerms: []
        }, {
            title: "fab fa-google-plus",
            searchTerms: [ "google-plus-circle", "google-plus-official" ]
        }, {
            title: "fab fa-google-plus-g",
            searchTerms: [ "google-plus", "social network" ]
        }, {
            title: "fab fa-google-wallet",
            searchTerms: []
        }, {
            title: "fas fa-gopuram",
            searchTerms: [ "building", "entrance", "hinduism", "temple", "tower" ]
        }, {
            title: "fas fa-graduation-cap",
            searchTerms: [ "cap", "celebration", "ceremony", "clothing", "college", "graduate", "graduation", "graduation cap", "hat", "learning", "school", "student" ]
        }, {
            title: "fab fa-gratipay",
            searchTerms: [ "favorite", "heart", "like", "love" ]
        }, {
            title: "fab fa-grav",
            searchTerms: []
        }, {
            title: "fas fa-greater-than",
            searchTerms: [ "Greater-Than Sign", "arithmetic", "compare", "math" ]
        }, {
            title: "fas fa-greater-than-equal",
            searchTerms: [ "arithmetic", "compare", "math" ]
        }, {
            title: "fas fa-grip",
            searchTerms: [ "affordance", "drag", "drop", "grab", "handle" ]
        }, {
            title: "fas fa-grip-lines",
            searchTerms: [ "affordance", "drag", "drop", "grab", "handle" ]
        }, {
            title: "fas fa-grip-lines-vertical",
            searchTerms: [ "affordance", "drag", "drop", "grab", "handle" ]
        }, {
            title: "fas fa-grip-vertical",
            searchTerms: [ "affordance", "drag", "drop", "grab", "handle" ]
        }, {
            title: "fab fa-gripfire",
            searchTerms: []
        }, {
            title: "fas fa-group-arrows-rotate",
            searchTerms: [ "community", "engagement", "spin", "sync" ]
        }, {
            title: "fab fa-grunt",
            searchTerms: []
        }, {
            title: "fas fa-guarani-sign",
            searchTerms: [ "Guarani Sign", "currency" ]
        }, {
            title: "fab fa-guilded",
            searchTerms: []
        }, {
            title: "fas fa-guitar",
            searchTerms: [ "acoustic", "instrument", "music", "rock", "rock and roll", "song", "strings" ]
        }, {
            title: "fab fa-gulp",
            searchTerms: []
        }, {
            title: "fas fa-gun",
            searchTerms: [ "firearm", "pistol", "weapon" ]
        }, {
            title: "fas fa-h",
            searchTerms: [ "Latin Capital Letter H", "Latin Small Letter H", "letter" ]
        }, {
            title: "fab fa-hacker-news",
            searchTerms: []
        }, {
            title: "fab fa-hackerrank",
            searchTerms: []
        }, {
            title: "fas fa-hammer",
            searchTerms: [ "admin", "fix", "hammer", "recovery", "repair", "settings", "tool" ]
        }, {
            title: "fas fa-hamsa",
            searchTerms: [ "amulet", "christianity", "islam", "jewish", "judaism", "muslim", "protection" ]
        }, {
            title: "fas fa-hand",
            searchTerms: [ "Raised Hand", "backhand", "game", "halt", "palm", "raised", "raised back of hand", "roshambo", "stop" ]
        }, {
            title: "far fa-hand",
            searchTerms: [ "Raised Hand", "backhand", "game", "halt", "palm", "raised", "raised back of hand", "roshambo", "stop" ]
        }, {
            title: "fas fa-hand-back-fist",
            searchTerms: [ "fist", "game", "roshambo" ]
        }, {
            title: "far fa-hand-back-fist",
            searchTerms: [ "fist", "game", "roshambo" ]
        }, {
            title: "fas fa-hand-dots",
            searchTerms: [ "allergy", "freckles", "hand", "hives", "palm", "pox", "skin", "spots" ]
        }, {
            title: "fas fa-hand-fist",
            searchTerms: [ "Dungeons & Dragons", "clenched", "d&d", "dnd", "fantasy", "fist", "hand", "ki", "monk", "punch", "raised fist", "resist", "strength", "unarmed combat" ]
        }, {
            title: "fas fa-hand-holding",
            searchTerms: [ "carry", "lift" ]
        }, {
            title: "fas fa-hand-holding-dollar",
            searchTerms: [ "$", "carry", "dollar sign", "donation", "giving", "lift", "money", "price" ]
        }, {
            title: "fas fa-hand-holding-droplet",
            searchTerms: [ "carry", "covid-19", "drought", "grow", "lift", "sanitation" ]
        }, {
            title: "fas fa-hand-holding-hand",
            searchTerms: [ "care", "give", "help", "hold", "protect" ]
        }, {
            title: "fas fa-hand-holding-heart",
            searchTerms: [ "carry", "charity", "gift", "lift", "package" ]
        }, {
            title: "fas fa-hand-holding-medical",
            searchTerms: [ "care", "covid-19", "donate", "help" ]
        }, {
            title: "fas fa-hand-lizard",
            searchTerms: [ "game", "roshambo" ]
        }, {
            title: "far fa-hand-lizard",
            searchTerms: [ "game", "roshambo" ]
        }, {
            title: "fas fa-hand-middle-finger",
            searchTerms: [ "finger", "flip the bird", "gesture", "hand", "hate", "middle finger", "rude" ]
        }, {
            title: "fas fa-hand-peace",
            searchTerms: [ "hand", "rest", "truce", "v", "victory", "victory hand" ]
        }, {
            title: "far fa-hand-peace",
            searchTerms: [ "hand", "rest", "truce", "v", "victory", "victory hand" ]
        }, {
            title: "fas fa-hand-point-down",
            searchTerms: [ "finger", "hand-o-down", "point" ]
        }, {
            title: "far fa-hand-point-down",
            searchTerms: [ "finger", "hand-o-down", "point" ]
        }, {
            title: "fas fa-hand-point-left",
            searchTerms: [ "back", "finger", "hand-o-left", "left", "point", "previous" ]
        }, {
            title: "far fa-hand-point-left",
            searchTerms: [ "back", "finger", "hand-o-left", "left", "point", "previous" ]
        }, {
            title: "fas fa-hand-point-right",
            searchTerms: [ "finger", "forward", "hand-o-right", "next", "point", "right" ]
        }, {
            title: "far fa-hand-point-right",
            searchTerms: [ "finger", "forward", "hand-o-right", "next", "point", "right" ]
        }, {
            title: "fas fa-hand-point-up",
            searchTerms: [ "finger", "hand", "hand-o-up", "index", "index pointing up", "point", "up" ]
        }, {
            title: "far fa-hand-point-up",
            searchTerms: [ "finger", "hand", "hand-o-up", "index", "index pointing up", "point", "up" ]
        }, {
            title: "fas fa-hand-pointer",
            searchTerms: [ "arrow", "cursor", "select" ]
        }, {
            title: "far fa-hand-pointer",
            searchTerms: [ "arrow", "cursor", "select" ]
        }, {
            title: "fas fa-hand-scissors",
            searchTerms: [ "cut", "game", "roshambo" ]
        }, {
            title: "far fa-hand-scissors",
            searchTerms: [ "cut", "game", "roshambo" ]
        }, {
            title: "fas fa-hand-sparkles",
            searchTerms: [ "clean", "covid-19", "hygiene", "magic", "palm", "soap", "wash" ]
        }, {
            title: "fas fa-hand-spock",
            searchTerms: [ "finger", "hand", "live long", "palm", "prosper", "salute", "spock", "star trek", "vulcan", "vulcan salute" ]
        }, {
            title: "far fa-hand-spock",
            searchTerms: [ "finger", "hand", "live long", "palm", "prosper", "salute", "spock", "star trek", "vulcan", "vulcan salute" ]
        }, {
            title: "fas fa-handcuffs",
            searchTerms: [ "arrest", "criminal", "handcuffs", "jail", "lock", "police", "wrist" ]
        }, {
            title: "fas fa-hands",
            searchTerms: [ "Translate", "asl", "deaf", "hands" ]
        }, {
            title: "fas fa-hands-asl-interpreting",
            searchTerms: [ "asl", "deaf", "finger", "hand", "interpret", "speak" ]
        }, {
            title: "fas fa-hands-bound",
            searchTerms: [ "abduction", "bound", "handcuff", "wrist" ]
        }, {
            title: "fas fa-hands-bubbles",
            searchTerms: [ "covid-19", "hygiene", "soap", "wash" ]
        }, {
            title: "fas fa-hands-clapping",
            searchTerms: [ "applause", "clap", "clapping hands", "hand" ]
        }, {
            title: "fas fa-hands-holding",
            searchTerms: [ "carry", "hold", "lift" ]
        }, {
            title: "fas fa-hands-holding-child",
            searchTerms: [ "care", "give", "help", "hold", "protect" ]
        }, {
            title: "fas fa-hands-holding-circle",
            searchTerms: [ "circle", "gift", "protection" ]
        }, {
            title: "fas fa-hands-praying",
            searchTerms: [ "kneel", "preach", "religion", "worship" ]
        }, {
            title: "fas fa-handshake",
            searchTerms: [ "agreement", "greeting", "meeting", "partnership" ]
        }, {
            title: "far fa-handshake",
            searchTerms: [ "agreement", "greeting", "meeting", "partnership" ]
        }, {
            title: "fas fa-handshake-angle",
            searchTerms: [ "aid", "assistance", "handshake", "partnership", "volunteering" ]
        }, {
            title: "fas fa-handshake-simple",
            searchTerms: [ "agreement", "greeting", "hand", "handshake", "meeting", "partnership", "shake" ]
        }, {
            title: "fas fa-handshake-simple-slash",
            searchTerms: [ "broken", "covid-19", "social distance" ]
        }, {
            title: "fas fa-handshake-slash",
            searchTerms: [ "broken", "covid-19", "social distance" ]
        }, {
            title: "fas fa-hanukiah",
            searchTerms: [ "candelabrum", "candle", "candlestick", "hanukkah", "jewish", "judaism", "light", "menorah", "religion" ]
        }, {
            title: "fas fa-hard-drive",
            searchTerms: [ "Hard Disk", "cpu", "hard drive", "harddrive", "machine", "save", "storage" ]
        }, {
            title: "far fa-hard-drive",
            searchTerms: [ "Hard Disk", "cpu", "hard drive", "harddrive", "machine", "save", "storage" ]
        }, {
            title: "fab fa-hashnode",
            searchTerms: []
        }, {
            title: "fas fa-hashtag",
            searchTerms: [ "Number Sign", "Twitter", "instagram", "pound", "social media", "tag" ]
        }, {
            title: "fas fa-hat-cowboy",
            searchTerms: [ "buckaroo", "horse", "jackeroo", "john b.", "old west", "pardner", "ranch", "rancher", "rodeo", "western", "wrangler" ]
        }, {
            title: "fas fa-hat-cowboy-side",
            searchTerms: [ "buckaroo", "horse", "jackeroo", "john b.", "old west", "pardner", "ranch", "rancher", "rodeo", "western", "wrangler" ]
        }, {
            title: "fas fa-hat-wizard",
            searchTerms: [ "Dungeons & Dragons", "accessory", "buckle", "clothing", "d&d", "dnd", "fantasy", "halloween", "head", "holiday", "mage", "magic", "pointy", "witch" ]
        }, {
            title: "fas fa-head-side-cough",
            searchTerms: [ "cough", "covid-19", "germs", "lungs", "respiratory", "sick" ]
        }, {
            title: "fas fa-head-side-cough-slash",
            searchTerms: [ "cough", "covid-19", "germs", "lungs", "respiratory", "sick" ]
        }, {
            title: "fas fa-head-side-mask",
            searchTerms: [ "breath", "coronavirus", "covid-19", "filter", "flu", "infection", "pandemic", "respirator", "virus" ]
        }, {
            title: "fas fa-head-side-virus",
            searchTerms: [ "cold", "coronavirus", "covid-19", "flu", "infection", "pandemic", "sick" ]
        }, {
            title: "fas fa-heading",
            searchTerms: [ "format", "header", "text", "title" ]
        }, {
            title: "fas fa-headphones",
            searchTerms: [ "audio", "earbud", "headphone", "listen", "music", "sound", "speaker" ]
        }, {
            title: "fas fa-headphones-simple",
            searchTerms: [ "audio", "listen", "music", "sound", "speaker" ]
        }, {
            title: "fas fa-headset",
            searchTerms: [ "audio", "gamer", "gaming", "listen", "live chat", "microphone", "shot caller", "sound", "support", "telemarketer" ]
        }, {
            title: "fas fa-heart",
            searchTerms: [ "black", "black heart", "blue", "blue heart", "brown", "brown heart", "card", "evil", "favorite", "game", "green", "green heart", "heart", "heart suit", "like", "love", "orange", "orange heart", "purple", "purple heart", "red heart", "relationship", "valentine", "white", "white heart", "wicked", "yellow", "yellow heart" ]
        }, {
            title: "far fa-heart",
            searchTerms: [ "black", "black heart", "blue", "blue heart", "brown", "brown heart", "card", "evil", "favorite", "game", "green", "green heart", "heart", "heart suit", "like", "love", "orange", "orange heart", "purple", "purple heart", "red heart", "relationship", "valentine", "white", "white heart", "wicked", "yellow", "yellow heart" ]
        }, {
            title: "fas fa-heart-circle-bolt",
            searchTerms: [ "cardiogram", "ekg", "electric", "heart", "love", "pacemaker" ]
        }, {
            title: "fas fa-heart-circle-check",
            searchTerms: [ "favorite", "heart", "love", "not affected", "ok", "okay" ]
        }, {
            title: "fas fa-heart-circle-exclamation",
            searchTerms: [ "favorite", "heart", "love" ]
        }, {
            title: "fas fa-heart-circle-minus",
            searchTerms: [ "favorite", "heart", "love" ]
        }, {
            title: "fas fa-heart-circle-plus",
            searchTerms: [ "favorite", "heart", "love" ]
        }, {
            title: "fas fa-heart-circle-xmark",
            searchTerms: [ "favorite", "heart", "love" ]
        }, {
            title: "fas fa-heart-crack",
            searchTerms: [ "break", "breakup", "broken", "broken heart", "crushed", "dislike", "dumped", "grief", "love", "lovesick", "relationship", "sad" ]
        }, {
            title: "fas fa-heart-pulse",
            searchTerms: [ "ekg", "electrocardiogram", "health", "lifeline", "vital signs" ]
        }, {
            title: "fas fa-helicopter",
            searchTerms: [ "airwolf", "apache", "chopper", "flight", "fly", "helicopter", "travel", "vehicle" ]
        }, {
            title: "fas fa-helicopter-symbol",
            searchTerms: [ "chopper", "helicopter", "landing pad", "whirlybird" ]
        }, {
            title: "fas fa-helmet-safety",
            searchTerms: [ "construction", "hardhat", "helmet", "safety" ]
        }, {
            title: "fas fa-helmet-un",
            searchTerms: [ "helmet", "united nations" ]
        }, {
            title: "fas fa-highlighter",
            searchTerms: [ "edit", "marker", "sharpie", "update", "write" ]
        }, {
            title: "fas fa-hill-avalanche",
            searchTerms: [ "mudslide", "snow", "winter" ]
        }, {
            title: "fas fa-hill-rockslide",
            searchTerms: [ "mudslide" ]
        }, {
            title: "fas fa-hippo",
            searchTerms: [ "animal", "fauna", "hippo", "hippopotamus", "hungry", "mammal" ]
        }, {
            title: "fab fa-hips",
            searchTerms: []
        }, {
            title: "fab fa-hire-a-helper",
            searchTerms: []
        }, {
            title: "fab fa-hive",
            searchTerms: []
        }, {
            title: "fas fa-hockey-puck",
            searchTerms: [ "ice", "nhl", "sport" ]
        }, {
            title: "fas fa-holly-berry",
            searchTerms: [ "catwoman", "christmas", "decoration", "flora", "halle", "holiday", "ororo munroe", "plant", "storm", "xmas" ]
        }, {
            title: "fab fa-hooli",
            searchTerms: []
        }, {
            title: "fab fa-hornbill",
            searchTerms: []
        }, {
            title: "fas fa-horse",
            searchTerms: [ "equestrian", "equus", "fauna", "horse", "mammmal", "mare", "neigh", "pony", "racehorse", "racing" ]
        }, {
            title: "fas fa-horse-head",
            searchTerms: [ "equus", "fauna", "mammmal", "mare", "neigh", "pony" ]
        }, {
            title: "fas fa-hospital",
            searchTerms: [ "building", "covid-19", "doctor", "emergency room", "hospital", "medical center", "medicine" ]
        }, {
            title: "far fa-hospital",
            searchTerms: [ "building", "covid-19", "doctor", "emergency room", "hospital", "medical center", "medicine" ]
        }, {
            title: "fas fa-hospital-user",
            searchTerms: [ "covid-19", "doctor", "network", "patient", "primary care" ]
        }, {
            title: "fas fa-hot-tub-person",
            searchTerms: [ "jacuzzi", "spa" ]
        }, {
            title: "fas fa-hotdog",
            searchTerms: [ "bun", "chili", "frankfurt", "frankfurter", "hot dog", "hotdog", "kosher", "polish", "sandwich", "sausage", "vienna", "weiner" ]
        }, {
            title: "fas fa-hotel",
            searchTerms: [ "building", "hotel", "inn", "lodging", "motel", "resort", "travel" ]
        }, {
            title: "fab fa-hotjar",
            searchTerms: []
        }, {
            title: "fas fa-hourglass",
            searchTerms: [ "hour", "hourglass", "hourglass not done", "minute", "sand", "stopwatch", "time", "timer" ]
        }, {
            title: "far fa-hourglass",
            searchTerms: [ "hour", "hourglass", "hourglass not done", "minute", "sand", "stopwatch", "time", "timer" ]
        }, {
            title: "fas fa-hourglass-end",
            searchTerms: [ "hour", "hourglass done", "minute", "sand", "stopwatch", "time", "timer" ]
        }, {
            title: "fas fa-hourglass-half",
            searchTerms: [ "hour", "minute", "sand", "stopwatch", "time" ]
        }, {
            title: "far fa-hourglass-half",
            searchTerms: [ "hour", "minute", "sand", "stopwatch", "time" ]
        }, {
            title: "fas fa-hourglass-start",
            searchTerms: [ "hour", "minute", "sand", "stopwatch", "time" ]
        }, {
            title: "fas fa-house",
            searchTerms: [ "abode", "building", "home", "house", "main", "residence" ]
        }, {
            title: "fas fa-house-chimney",
            searchTerms: [ "abode", "building", "chimney", "house", "main", "residence", "smokestack" ]
        }, {
            title: "fas fa-house-chimney-crack",
            searchTerms: [ "building", "devastation", "disaster", "earthquake", "home", "insurance" ]
        }, {
            title: "fas fa-house-chimney-medical",
            searchTerms: [ "covid-19", "doctor", "general practitioner", "hospital", "infirmary", "medicine", "office", "outpatient" ]
        }, {
            title: "fas fa-house-chimney-user",
            searchTerms: [ "covid-19", "home", "isolation", "quarantine" ]
        }, {
            title: "fas fa-house-chimney-window",
            searchTerms: [ "abode", "building", "family", "home", "residence" ]
        }, {
            title: "fas fa-house-circle-check",
            searchTerms: [ "abode", "home", "house", "not affected", "ok", "okay" ]
        }, {
            title: "fas fa-house-circle-exclamation",
            searchTerms: [ "abode", "affected", "home", "house" ]
        }, {
            title: "fas fa-house-circle-xmark",
            searchTerms: [ "abode", "destroy", "home", "house" ]
        }, {
            title: "fas fa-house-crack",
            searchTerms: [ "building", "devastation", "disaster", "earthquake", "home", "insurance" ]
        }, {
            title: "fas fa-house-fire",
            searchTerms: [ "burn", "emergency", "home" ]
        }, {
            title: "fas fa-house-flag",
            searchTerms: [ "camp", "home" ]
        }, {
            title: "fas fa-house-flood-water",
            searchTerms: [ "damage", "flood", "water" ]
        }, {
            title: "fas fa-house-flood-water-circle-arrow-right",
            searchTerms: [ "damage", "flood", "water" ]
        }, {
            title: "fas fa-house-laptop",
            searchTerms: [ "computer", "covid-19", "device", "office", "remote", "work from home" ]
        }, {
            title: "fas fa-house-lock",
            searchTerms: [ "closed", "home", "house", "lockdown", "quarantine" ]
        }, {
            title: "fas fa-house-medical",
            searchTerms: [ "covid-19", "doctor", "facility", "general practitioner", "health", "hospital", "infirmary", "medicine", "office", "outpatient" ]
        }, {
            title: "fas fa-house-medical-circle-check",
            searchTerms: [ "clinic", "hospital", "not affected", "ok", "okay" ]
        }, {
            title: "fas fa-house-medical-circle-exclamation",
            searchTerms: [ "affected", "clinic", "hospital" ]
        }, {
            title: "fas fa-house-medical-circle-xmark",
            searchTerms: [ "clinic", "destroy", "hospital" ]
        }, {
            title: "fas fa-house-medical-flag",
            searchTerms: [ "clinic", "hospital", "mash" ]
        }, {
            title: "fas fa-house-signal",
            searchTerms: [ "abode", "building", "connect", "family", "home", "residence", "smart home", "wifi" ]
        }, {
            title: "fas fa-house-tsunami",
            searchTerms: [ "damage", "flood", "tidal wave", "wave" ]
        }, {
            title: "fas fa-house-user",
            searchTerms: [ "house" ]
        }, {
            title: "fab fa-houzz",
            searchTerms: []
        }, {
            title: "fas fa-hryvnia-sign",
            searchTerms: [ "Hryvnia Sign", "currency" ]
        }, {
            title: "fab fa-html5",
            searchTerms: []
        }, {
            title: "fab fa-hubspot",
            searchTerms: []
        }, {
            title: "fas fa-hurricane",
            searchTerms: [ "coriolis effect", "eye", "storm", "tropical cyclone", "typhoon" ]
        }, {
            title: "fas fa-i",
            searchTerms: [ "Latin Capital Letter I", "Latin Small Letter I", "letter" ]
        }, {
            title: "fas fa-i-cursor",
            searchTerms: [ "editing", "i-beam", "type", "writing" ]
        }, {
            title: "fas fa-ice-cream",
            searchTerms: [ "chocolate", "cone", "cream", "dessert", "frozen", "ice", "ice cream", "scoop", "sorbet", "sweet", "vanilla", "yogurt" ]
        }, {
            title: "fas fa-icicles",
            searchTerms: [ "cold", "frozen", "hanging", "ice", "seasonal", "sharp" ]
        }, {
            title: "fas fa-icons",
            searchTerms: [ "bolt", "emoji", "heart", "image", "music", "photo", "symbols" ]
        }, {
            title: "fas fa-id-badge",
            searchTerms: [ "address", "contact", "identification", "license", "profile" ]
        }, {
            title: "far fa-id-badge",
            searchTerms: [ "address", "contact", "identification", "license", "profile" ]
        }, {
            title: "fas fa-id-card",
            searchTerms: [ "contact", "demographics", "document", "identification", "issued", "profile", "registration" ]
        }, {
            title: "far fa-id-card",
            searchTerms: [ "contact", "demographics", "document", "identification", "issued", "profile", "registration" ]
        }, {
            title: "fas fa-id-card-clip",
            searchTerms: [ "contact", "demographics", "document", "identification", "issued", "profile" ]
        }, {
            title: "fab fa-ideal",
            searchTerms: []
        }, {
            title: "fas fa-igloo",
            searchTerms: [ "dome", "dwelling", "eskimo", "home", "house", "ice", "snow" ]
        }, {
            title: "fas fa-image",
            searchTerms: [ "album", "landscape", "photo", "picture" ]
        }, {
            title: "far fa-image",
            searchTerms: [ "album", "landscape", "photo", "picture" ]
        }, {
            title: "fas fa-image-portrait",
            searchTerms: [ "id", "image", "photo", "picture", "selfie" ]
        }, {
            title: "fas fa-images",
            searchTerms: [ "album", "landscape", "photo", "picture" ]
        }, {
            title: "far fa-images",
            searchTerms: [ "album", "landscape", "photo", "picture" ]
        }, {
            title: "fab fa-imdb",
            searchTerms: []
        }, {
            title: "fas fa-inbox",
            searchTerms: [ "archive", "desk", "email", "mail", "message" ]
        }, {
            title: "fas fa-indent",
            searchTerms: [ "align", "justify", "paragraph", "tab" ]
        }, {
            title: "fas fa-indian-rupee-sign",
            searchTerms: [ "Indian Rupee Sign", "currency" ]
        }, {
            title: "fas fa-industry",
            searchTerms: [ "building", "factory", "industrial", "manufacturing", "mill", "warehouse" ]
        }, {
            title: "fas fa-infinity",
            searchTerms: [ "Infinity", "eternity", "forever", "infinity", "math", "unbounded", "universal" ]
        }, {
            title: "fas fa-info",
            searchTerms: [ "details", "help", "information", "more", "support" ]
        }, {
            title: "fab fa-instagram",
            searchTerms: []
        }, {
            title: "fab fa-instalod",
            searchTerms: []
        }, {
            title: "fab fa-intercom",
            searchTerms: [ "app", "customer", "messenger" ]
        }, {
            title: "fab fa-internet-explorer",
            searchTerms: [ "browser", "ie" ]
        }, {
            title: "fab fa-invision",
            searchTerms: [ "app", "design", "interface" ]
        }, {
            title: "fab fa-ioxhost",
            searchTerms: []
        }, {
            title: "fas fa-italic",
            searchTerms: [ "edit", "emphasis", "font", "format", "text", "type" ]
        }, {
            title: "fab fa-itch-io",
            searchTerms: []
        }, {
            title: "fab fa-itunes",
            searchTerms: []
        }, {
            title: "fab fa-itunes-note",
            searchTerms: []
        }, {
            title: "fas fa-j",
            searchTerms: [ "Latin Capital Letter J", "Latin Small Letter J", "letter" ]
        }, {
            title: "fas fa-jar",
            searchTerms: [ "jam", "jelly", "storage" ]
        }, {
            title: "fas fa-jar-wheat",
            searchTerms: [ "flour", "storage" ]
        }, {
            title: "fab fa-java",
            searchTerms: []
        }, {
            title: "fas fa-jedi",
            searchTerms: [ "crest", "force", "sith", "skywalker", "star wars", "yoda" ]
        }, {
            title: "fab fa-jedi-order",
            searchTerms: [ "star wars" ]
        }, {
            title: "fab fa-jenkins",
            searchTerms: []
        }, {
            title: "fas fa-jet-fighter",
            searchTerms: [ "airforce", "airplane", "airport", "fast", "fly", "goose", "marines", "maverick", "military", "plane", "quick", "top gun", "transportation", "travel" ]
        }, {
            title: "fas fa-jet-fighter-up",
            searchTerms: [ "airforce", "airplane", "airport", "fast", "fly", "goose", "marines", "maverick", "military", "plane", "quick", "top gun", "transportation", "travel" ]
        }, {
            title: "fab fa-jira",
            searchTerms: [ "atlassian" ]
        }, {
            title: "fab fa-joget",
            searchTerms: []
        }, {
            title: "fas fa-joint",
            searchTerms: [ "blunt", "cannabis", "doobie", "drugs", "marijuana", "roach", "smoke", "smoking", "spliff" ]
        }, {
            title: "fab fa-joomla",
            searchTerms: []
        }, {
            title: "fab fa-js",
            searchTerms: []
        }, {
            title: "fab fa-jsfiddle",
            searchTerms: []
        }, {
            title: "fas fa-jug-detergent",
            searchTerms: [ "detergent", "laundry", "soap", "wash" ]
        }, {
            title: "fas fa-k",
            searchTerms: [ "Latin Capital Letter K", "Latin Small Letter K", "letter" ]
        }, {
            title: "fas fa-kaaba",
            searchTerms: [ "Muslim", "building", "cube", "islam", "kaaba", "muslim", "religion" ]
        }, {
            title: "fab fa-kaggle",
            searchTerms: []
        }, {
            title: "fas fa-key",
            searchTerms: [ "key", "lock", "password", "private", "secret", "unlock" ]
        }, {
            title: "fab fa-keybase",
            searchTerms: []
        }, {
            title: "fas fa-keyboard",
            searchTerms: [ "accessory", "computer", "edit", "input", "keyboard", "text", "type", "write" ]
        }, {
            title: "far fa-keyboard",
            searchTerms: [ "accessory", "computer", "edit", "input", "keyboard", "text", "type", "write" ]
        }, {
            title: "fab fa-keycdn",
            searchTerms: []
        }, {
            title: "fas fa-khanda",
            searchTerms: [ "Adi Shakti", "chakkar", "sikh", "sikhism", "sword" ]
        }, {
            title: "fab fa-kickstarter",
            searchTerms: []
        }, {
            title: "fab fa-kickstarter-k",
            searchTerms: []
        }, {
            title: "fas fa-kip-sign",
            searchTerms: [ "Kip Sign", "currency" ]
        }, {
            title: "fas fa-kit-medical",
            searchTerms: [ "emergency", "emt", "health", "medical", "rescue" ]
        }, {
            title: "fas fa-kitchen-set",
            searchTerms: [ "chef", "cook", "cup", "kitchen", "pan", "pot", "skillet" ]
        }, {
            title: "fas fa-kiwi-bird",
            searchTerms: [ "bird", "fauna", "new zealand" ]
        }, {
            title: "fab fa-korvue",
            searchTerms: []
        }, {
            title: "fas fa-l",
            searchTerms: [ "Latin Capital Letter L", "Latin Small Letter L", "letter" ]
        }, {
            title: "fas fa-land-mine-on",
            searchTerms: [ "bomb", "danger", "explosion", "war" ]
        }, {
            title: "fas fa-landmark",
            searchTerms: [ "building", "classical", "historic", "memorable", "monument", "museum", "politics" ]
        }, {
            title: "fas fa-landmark-dome",
            searchTerms: [ "building", "historic", "memorable", "monument", "politics" ]
        }, {
            title: "fas fa-landmark-flag",
            searchTerms: [ "capitol", "flag", "landmark", "memorial" ]
        }, {
            title: "fas fa-language",
            searchTerms: [ "dialect", "idiom", "localize", "speech", "translate", "vernacular" ]
        }, {
            title: "fas fa-laptop",
            searchTerms: [ "computer", "cpu", "dell", "demo", "device", "laptop", "mac", "macbook", "machine", "pc", "personal" ]
        }, {
            title: "fas fa-laptop-code",
            searchTerms: [ "computer", "cpu", "dell", "demo", "develop", "device", "mac", "macbook", "machine", "pc" ]
        }, {
            title: "fas fa-laptop-file",
            searchTerms: [ "computer", "education", "laptop", "learning", "remote work" ]
        }, {
            title: "fas fa-laptop-medical",
            searchTerms: [ "computer", "device", "ehr", "electronic health records", "history" ]
        }, {
            title: "fab fa-laravel",
            searchTerms: []
        }, {
            title: "fas fa-lari-sign",
            searchTerms: [ "Lari Sign", "currency" ]
        }, {
            title: "fab fa-lastfm",
            searchTerms: []
        }, {
            title: "fas fa-layer-group",
            searchTerms: [ "arrange", "develop", "layers", "map", "stack" ]
        }, {
            title: "fas fa-leaf",
            searchTerms: [ "eco", "flora", "nature", "plant", "vegan" ]
        }, {
            title: "fab fa-leanpub",
            searchTerms: []
        }, {
            title: "fas fa-left-long",
            searchTerms: [ "back", "long-arrow-left", "previous" ]
        }, {
            title: "fas fa-left-right",
            searchTerms: [ "arrow", "arrows-h", "expand", "horizontal", "landscape", "left-right arrow", "resize", "wide" ]
        }, {
            title: "fas fa-lemon",
            searchTerms: [ "citrus", "fruit", "lemon", "lemonade", "lime", "tart" ]
        }, {
            title: "far fa-lemon",
            searchTerms: [ "citrus", "fruit", "lemon", "lemonade", "lime", "tart" ]
        }, {
            title: "fab fa-less",
            searchTerms: []
        }, {
            title: "fas fa-less-than",
            searchTerms: [ "Less-Than Sign", "arithmetic", "compare", "math" ]
        }, {
            title: "fas fa-less-than-equal",
            searchTerms: [ "arithmetic", "compare", "math" ]
        }, {
            title: "fas fa-life-ring",
            searchTerms: [ "coast guard", "help", "overboard", "save", "support" ]
        }, {
            title: "far fa-life-ring",
            searchTerms: [ "coast guard", "help", "overboard", "save", "support" ]
        }, {
            title: "fas fa-lightbulb",
            searchTerms: [ "  comic", "  electric", "  idea", "  innovation", "  inspiration", "  light", "  light bulb", " bulb", "bulb", "comic", "electric", "energy", "idea", "inspiration", "mechanical" ]
        }, {
            title: "far fa-lightbulb",
            searchTerms: [ "  comic", "  electric", "  idea", "  innovation", "  inspiration", "  light", "  light bulb", " bulb", "bulb", "comic", "electric", "energy", "idea", "inspiration", "mechanical" ]
        }, {
            title: "fab fa-line",
            searchTerms: []
        }, {
            title: "fas fa-lines-leaning",
            searchTerms: [ "canted", "domino", "falling", "resilience", "resilient", "tipped" ]
        }, {
            title: "fas fa-link",
            searchTerms: [ "attach", "attachment", "chain", "connect", "lin", "link" ]
        }, {
            title: "fas fa-link-slash",
            searchTerms: [ "attachment", "chain", "chain-broken", "remove" ]
        }, {
            title: "fab fa-linkedin",
            searchTerms: [ "linkedin-square" ]
        }, {
            title: "fab fa-linkedin-in",
            searchTerms: [ "linkedin" ]
        }, {
            title: "fab fa-linode",
            searchTerms: []
        }, {
            title: "fab fa-linux",
            searchTerms: [ "tux" ]
        }, {
            title: "fas fa-lira-sign",
            searchTerms: [ "Lira Sign", "currency" ]
        }, {
            title: "fas fa-list",
            searchTerms: [ "checklist", "completed", "done", "finished", "ol", "todo", "ul" ]
        }, {
            title: "fas fa-list-check",
            searchTerms: [ "checklist", "downloading", "downloads", "loading", "progress", "project management", "settings", "to do" ]
        }, {
            title: "fas fa-list-ol",
            searchTerms: [ "checklist", "completed", "done", "finished", "numbers", "ol", "todo", "ul" ]
        }, {
            title: "fas fa-list-ul",
            searchTerms: [ "checklist", "completed", "done", "finished", "ol", "todo", "ul" ]
        }, {
            title: "fas fa-litecoin-sign",
            searchTerms: [ "currency" ]
        }, {
            title: "fas fa-location-arrow",
            searchTerms: [ "address", "compass", "coordinate", "direction", "gps", "map", "navigation", "place" ]
        }, {
            title: "fas fa-location-crosshairs",
            searchTerms: [ "address", "coordinate", "direction", "gps", "location", "map", "navigation", "place", "where" ]
        }, {
            title: "fas fa-location-dot",
            searchTerms: [ "address", "coordinates", "destination", "gps", "localize", "location", "map", "navigation", "paper", "pin", "place", "point of interest", "position", "route", "travel" ]
        }, {
            title: "fas fa-location-pin",
            searchTerms: [ "address", "coordinates", "destination", "gps", "localize", "location", "map", "navigation", "paper", "pin", "place", "point of interest", "position", "route", "travel" ]
        }, {
            title: "fas fa-location-pin-lock",
            searchTerms: [ "closed", "lockdown", "map", "quarantine" ]
        }, {
            title: "fas fa-lock",
            searchTerms: [ "admin", "closed", "lock", "locked", "open", "password", "private", "protect", "security" ]
        }, {
            title: "fas fa-lock-open",
            searchTerms: [ "admin", "lock", "open", "password", "private", "protect", "security", "unlock" ]
        }, {
            title: "fas fa-locust",
            searchTerms: [ "horde", "infestation", "locust", "plague", "swarm" ]
        }, {
            title: "fas fa-lungs",
            searchTerms: [ "air", "breath", "covid-19", "exhalation", "inhalation", "lungs", "organ", "respiration", "respiratory" ]
        }, {
            title: "fas fa-lungs-virus",
            searchTerms: [ "breath", "coronavirus", "covid-19", "flu", "infection", "pandemic", "respiratory", "sick" ]
        }, {
            title: "fab fa-lyft",
            searchTerms: []
        }, {
            title: "fas fa-m",
            searchTerms: [ "Latin Capital Letter M", "Latin Small Letter M", "letter" ]
        }, {
            title: "fab fa-magento",
            searchTerms: []
        }, {
            title: "fas fa-magnet",
            searchTerms: [ "Attract", "attraction", "horseshoe", "lodestone", "magnet", "magnetic", "tool" ]
        }, {
            title: "fas fa-magnifying-glass",
            searchTerms: [ "bigger", "enlarge", "find", "glass", "magnify", "magnifying", "magnifying glass tilted left", "preview", "search", "tool", "zoom" ]
        }, {
            title: "fas fa-magnifying-glass-arrow-right",
            searchTerms: [ "find", "next", "search" ]
        }, {
            title: "fas fa-magnifying-glass-chart",
            searchTerms: [ " data", " graph", " intelligence", "analysis", "chart", "market" ]
        }, {
            title: "fas fa-magnifying-glass-dollar",
            searchTerms: [ "bigger", "enlarge", "find", "magnify", "money", "preview", "zoom" ]
        }, {
            title: "fas fa-magnifying-glass-location",
            searchTerms: [ "bigger", "enlarge", "find", "magnify", "preview", "zoom" ]
        }, {
            title: "fas fa-magnifying-glass-minus",
            searchTerms: [ "minify", "negative", "smaller", "zoom", "zoom out" ]
        }, {
            title: "fas fa-magnifying-glass-plus",
            searchTerms: [ "bigger", "enlarge", "magnify", "positive", "zoom", "zoom in" ]
        }, {
            title: "fab fa-mailchimp",
            searchTerms: []
        }, {
            title: "fas fa-manat-sign",
            searchTerms: [ "Manat Sign", "currency" ]
        }, {
            title: "fab fa-mandalorian",
            searchTerms: []
        }, {
            title: "fas fa-map",
            searchTerms: [ "address", "coordinates", "destination", "gps", "localize", "location", "map", "navigation", "paper", "pin", "place", "point of interest", "position", "route", "travel", "world", "world map" ]
        }, {
            title: "far fa-map",
            searchTerms: [ "address", "coordinates", "destination", "gps", "localize", "location", "map", "navigation", "paper", "pin", "place", "point of interest", "position", "route", "travel", "world", "world map" ]
        }, {
            title: "fas fa-map-location",
            searchTerms: [ "address", "coordinates", "destination", "gps", "localize", "location", "map", "navigation", "paper", "pin", "place", "point of interest", "position", "route", "travel" ]
        }, {
            title: "fas fa-map-location-dot",
            searchTerms: [ "address", "coordinates", "destination", "gps", "localize", "location", "map", "navigation", "paper", "pin", "place", "point of interest", "position", "route", "travel" ]
        }, {
            title: "fas fa-map-pin",
            searchTerms: [ "address", "agree", "coordinates", "destination", "gps", "localize", "location", "map", "marker", "navigation", "pin", "place", "position", "pushpin", "round pushpin", "travel" ]
        }, {
            title: "fab fa-markdown",
            searchTerms: []
        }, {
            title: "fas fa-marker",
            searchTerms: [ "design", "edit", "sharpie", "update", "write" ]
        }, {
            title: "fas fa-mars",
            searchTerms: [ "gender", "male", "male sign", "man" ]
        }, {
            title: "fas fa-mars-and-venus",
            searchTerms: [ "Male and Female Sign", "female", "gender", "intersex", "male", "transgender" ]
        }, {
            title: "fas fa-mars-and-venus-burst",
            searchTerms: [ "gender", "violence" ]
        }, {
            title: "fas fa-mars-double",
            searchTerms: [ "Doubled Male Sign", "gay", "gender", "male", "men" ]
        }, {
            title: "fas fa-mars-stroke",
            searchTerms: [ "Male with Stroke Sign", "gender", "transgender" ]
        }, {
            title: "fas fa-mars-stroke-right",
            searchTerms: [ "Horizontal Male with Stroke Sign", "gender" ]
        }, {
            title: "fas fa-mars-stroke-up",
            searchTerms: [ "Vertical Male with Stroke Sign", "gender" ]
        }, {
            title: "fas fa-martini-glass",
            searchTerms: [ "alcohol", "bar", "beverage", "cocktail", "cocktail glass", "drink", "glass", "liquor" ]
        }, {
            title: "fas fa-martini-glass-citrus",
            searchTerms: [ "alcohol", "beverage", "drink", "gin", "glass", "margarita", "martini", "vodka" ]
        }, {
            title: "fas fa-martini-glass-empty",
            searchTerms: [ "alcohol", "bar", "beverage", "drink", "liquor" ]
        }, {
            title: "fas fa-mask",
            searchTerms: [ "carnivale", "costume", "disguise", "halloween", "secret", "super hero" ]
        }, {
            title: "fas fa-mask-face",
            searchTerms: [ "breath", "coronavirus", "covid-19", "filter", "flu", "infection", "pandemic", "respirator", "virus" ]
        }, {
            title: "fas fa-mask-ventilator",
            searchTerms: [ "breath", "gas", "mask", "oxygen", "respirator", "ventilator" ]
        }, {
            title: "fas fa-masks-theater",
            searchTerms: [ "art", "comedy", "mask", "perform", "performing", "performing arts", "theater", "theatre", "tragedy" ]
        }, {
            title: "fab fa-mastodon",
            searchTerms: []
        }, {
            title: "fas fa-mattress-pillow",
            searchTerms: [ "air mattress", "mattress", "pillow", "rest", "sleep" ]
        }, {
            title: "fab fa-maxcdn",
            searchTerms: []
        }, {
            title: "fas fa-maximize",
            searchTerms: [ "bigger", "enlarge", "fullscreen", "move", "resize" ]
        }, {
            title: "fab fa-mdb",
            searchTerms: []
        }, {
            title: "fas fa-medal",
            searchTerms: [ "award", "medal", "ribbon", "sports medal", "star", "trophy" ]
        }, {
            title: "fab fa-medapps",
            searchTerms: []
        }, {
            title: "fab fa-medium",
            searchTerms: []
        }, {
            title: "fab fa-medrt",
            searchTerms: []
        }, {
            title: "fab fa-meetup",
            searchTerms: []
        }, {
            title: "fab fa-megaport",
            searchTerms: []
        }, {
            title: "fas fa-memory",
            searchTerms: [ "DIMM", "RAM", "hardware", "storage", "technology" ]
        }, {
            title: "fab fa-mendeley",
            searchTerms: []
        }, {
            title: "fas fa-menorah",
            searchTerms: [ "candle", "hanukkah", "jewish", "judaism", "light" ]
        }, {
            title: "fas fa-mercury",
            searchTerms: [ "Mercury", "gender", "hybrid", "transgender" ]
        }, {
            title: "fas fa-message",
            searchTerms: [ "bubble", "chat", "commenting", "conversation", "feedback", "message", "note", "notification", "sms", "speech", "texting" ]
        }, {
            title: "far fa-message",
            searchTerms: [ "bubble", "chat", "commenting", "conversation", "feedback", "message", "note", "notification", "sms", "speech", "texting" ]
        }, {
            title: "fab fa-meta",
            searchTerms: []
        }, {
            title: "fas fa-meteor",
            searchTerms: [ "armageddon", "asteroid", "comet", "shooting star", "space" ]
        }, {
            title: "fab fa-microblog",
            searchTerms: []
        }, {
            title: "fas fa-microchip",
            searchTerms: [ "cpu", "hardware", "processor", "technology" ]
        }, {
            title: "fas fa-microphone",
            searchTerms: [ "address", "audio", "information", "podcast", "public", "record", "sing", "sound", "voice" ]
        }, {
            title: "fas fa-microphone-lines",
            searchTerms: [ "audio", "mic", "microphone", "music", "podcast", "record", "sing", "sound", "studio", "studio microphone", "voice" ]
        }, {
            title: "fas fa-microphone-lines-slash",
            searchTerms: [ "audio", "disable", "mute", "podcast", "record", "sing", "sound", "voice" ]
        }, {
            title: "fas fa-microphone-slash",
            searchTerms: [ "audio", "disable", "mute", "podcast", "record", "sing", "sound", "voice" ]
        }, {
            title: "fas fa-microscope",
            searchTerms: [ "covid-19", "electron", "lens", "microscope", "optics", "science", "shrink", "testing", "tool" ]
        }, {
            title: "fab fa-microsoft",
            searchTerms: []
        }, {
            title: "fas fa-mill-sign",
            searchTerms: [ "Mill Sign", "currency" ]
        }, {
            title: "fas fa-minimize",
            searchTerms: [ "collapse", "fullscreen", "minimize", "move", "resize", "shrink", "smaller" ]
        }, {
            title: "fas fa-minus",
            searchTerms: [ "En Dash", "Minus Sign", "collapse", "delete", "hide", "math", "minify", "minus", "negative", "remove", "sign", "trash", "−" ]
        }, {
            title: "fas fa-mitten",
            searchTerms: [ "clothing", "cold", "glove", "hands", "knitted", "seasonal", "warmth" ]
        }, {
            title: "fab fa-mix",
            searchTerms: []
        }, {
            title: "fab fa-mixcloud",
            searchTerms: []
        }, {
            title: "fab fa-mixer",
            searchTerms: []
        }, {
            title: "fab fa-mizuni",
            searchTerms: []
        }, {
            title: "fas fa-mobile",
            searchTerms: [ "android", "call", "cell", "cell phone", "device", "mobile", "mobile phone", "number", "phone", "screen", "telephone", "text" ]
        }, {
            title: "fas fa-mobile-button",
            searchTerms: [ "apple", "call", "cell phone", "device", "iphone", "number", "screen", "telephone" ]
        }, {
            title: "fas fa-mobile-retro",
            searchTerms: [ "cellphone", "cellular", "phone" ]
        }, {
            title: "fas fa-mobile-screen",
            searchTerms: [ "android", "call", "cell phone", "device", "number", "screen", "telephone", "text" ]
        }, {
            title: "fas fa-mobile-screen-button",
            searchTerms: [ "apple", "call", "cell phone", "device", "iphone", "number", "screen", "telephone" ]
        }, {
            title: "fab fa-modx",
            searchTerms: []
        }, {
            title: "fab fa-monero",
            searchTerms: []
        }, {
            title: "fas fa-money-bill",
            searchTerms: [ "buy", "cash", "checkout", "money", "payment", "price", "purchase" ]
        }, {
            title: "fas fa-money-bill-1",
            searchTerms: [ "buy", "cash", "checkout", "money", "payment", "price", "purchase" ]
        }, {
            title: "far fa-money-bill-1",
            searchTerms: [ "buy", "cash", "checkout", "money", "payment", "price", "purchase" ]
        }, {
            title: "fas fa-money-bill-1-wave",
            searchTerms: [ "buy", "cash", "checkout", "money", "payment", "price", "purchase" ]
        }, {
            title: "fas fa-money-bill-transfer",
            searchTerms: [ "bank", "conversion", "deposit", "money", "transfer", "withdrawal" ]
        }, {
            title: "fas fa-money-bill-trend-up",
            searchTerms: [ "bank", "bonds", "inflation", "market", "stocks", "trade" ]
        }, {
            title: "fas fa-money-bill-wave",
            searchTerms: [ "buy", "cash", "checkout", "money", "payment", "price", "purchase" ]
        }, {
            title: "fas fa-money-bill-wheat",
            searchTerms: [ "agribusiness", "agriculture", "farming", "food", "livelihood", "subsidy" ]
        }, {
            title: "fas fa-money-bills",
            searchTerms: [ "atm", "cash", "money", "moolah" ]
        }, {
            title: "fas fa-money-check",
            searchTerms: [ "bank check", "buy", "checkout", "cheque", "money", "payment", "price", "purchase" ]
        }, {
            title: "fas fa-money-check-dollar",
            searchTerms: [ "bank check", "buy", "checkout", "cheque", "money", "payment", "price", "purchase" ]
        }, {
            title: "fas fa-monument",
            searchTerms: [ "building", "historic", "landmark", "memorable" ]
        }, {
            title: "fas fa-moon",
            searchTerms: [ "Power Sleep Symbol", "contrast", "crescent", "crescent moon", "dark", "lunar", "moon", "night" ]
        }, {
            title: "far fa-moon",
            searchTerms: [ "Power Sleep Symbol", "contrast", "crescent", "crescent moon", "dark", "lunar", "moon", "night" ]
        }, {
            title: "fas fa-mortar-pestle",
            searchTerms: [ "crush", "culinary", "grind", "medical", "mix", "pharmacy", "prescription", "spices" ]
        }, {
            title: "fas fa-mosque",
            searchTerms: [ "Muslim", "building", "islam", "landmark", "mosque", "muslim", "religion" ]
        }, {
            title: "fas fa-mosquito",
            searchTerms: [ "bite", "bug", "mosquito", "west nile" ]
        }, {
            title: "fas fa-mosquito-net",
            searchTerms: [ "bite", "malaria", "mosquito", "net" ]
        }, {
            title: "fas fa-motorcycle",
            searchTerms: [ "bike", "machine", "motorcycle", "racing", "transportation", "vehicle" ]
        }, {
            title: "fas fa-mound",
            searchTerms: [ "barrier", "hill", "pitcher", "speedbump" ]
        }, {
            title: "fas fa-mountain",
            searchTerms: [ "cold", "glacier", "hiking", "hill", "landscape", "mountain", "snow", "snow-capped mountain", "travel", "view" ]
        }, {
            title: "fas fa-mountain-city",
            searchTerms: [ "location", "rural", "urban" ]
        }, {
            title: "fas fa-mountain-sun",
            searchTerms: [ "country", "hiking", "landscape", "rural", "travel", "view" ]
        }, {
            title: "fas fa-mug-hot",
            searchTerms: [ "beverage", "caliente", "cocoa", "coffee", "cup", "drink", "holiday", "hot", "hot beverage", "hot chocolate", "steam", "steaming", "tea", "warmth" ]
        }, {
            title: "fas fa-mug-saucer",
            searchTerms: [ "beverage", "breakfast", "cafe", "drink", "fall", "morning", "mug", "seasonal", "tea" ]
        }, {
            title: "fas fa-music",
            searchTerms: [ "lyrics", "melody", "music", "musical note", "note", "sing", "sound" ]
        }, {
            title: "fas fa-n",
            searchTerms: [ "Latin Capital Letter N", "Latin Small Letter N", "letter", "nay", "no" ]
        }, {
            title: "fas fa-naira-sign",
            searchTerms: [ "Naira Sign", "currency" ]
        }, {
            title: "fab fa-napster",
            searchTerms: []
        }, {
            title: "fab fa-neos",
            searchTerms: []
        }, {
            title: "fas fa-network-wired",
            searchTerms: [ "computer", "connect", "ethernet", "internet", "intranet" ]
        }, {
            title: "fas fa-neuter",
            searchTerms: [ "Neuter", "gender" ]
        }, {
            title: "fas fa-newspaper",
            searchTerms: [ "article", "editorial", "headline", "journal", "journalism", "news", "newspaper", "paper", "press" ]
        }, {
            title: "far fa-newspaper",
            searchTerms: [ "article", "editorial", "headline", "journal", "journalism", "news", "newspaper", "paper", "press" ]
        }, {
            title: "fab fa-nfc-directional",
            searchTerms: [ "connect", "data", "near field communication", "nfc", "scan", "signal", "transfer", "wireless" ]
        }, {
            title: "fab fa-nfc-symbol",
            searchTerms: [ "connect", "data", "near field communication", "nfc", "scan", "signal", "transfer", "wireless" ]
        }, {
            title: "fab fa-nimblr",
            searchTerms: []
        }, {
            title: "fab fa-node",
            searchTerms: []
        }, {
            title: "fab fa-node-js",
            searchTerms: []
        }, {
            title: "fas fa-not-equal",
            searchTerms: [ "arithmetic", "compare", "math" ]
        }, {
            title: "fas fa-notdef",
            searchTerms: [ "close", "missing" ]
        }, {
            title: "fas fa-note-sticky",
            searchTerms: [ "message", "note", "paper", "reminder", "sticker" ]
        }, {
            title: "far fa-note-sticky",
            searchTerms: [ "message", "note", "paper", "reminder", "sticker" ]
        }, {
            title: "fas fa-notes-medical",
            searchTerms: [ "clipboard", "doctor", "ehr", "health", "history", "records" ]
        }, {
            title: "fab fa-npm",
            searchTerms: []
        }, {
            title: "fab fa-ns8",
            searchTerms: []
        }, {
            title: "fab fa-nutritionix",
            searchTerms: []
        }, {
            title: "fas fa-o",
            searchTerms: [ "Latin Capital Letter O", "Latin Small Letter O", "letter" ]
        }, {
            title: "fas fa-object-group",
            searchTerms: [ "combine", "copy", "design", "merge", "select" ]
        }, {
            title: "far fa-object-group",
            searchTerms: [ "combine", "copy", "design", "merge", "select" ]
        }, {
            title: "fas fa-object-ungroup",
            searchTerms: [ "copy", "design", "merge", "select", "separate" ]
        }, {
            title: "far fa-object-ungroup",
            searchTerms: [ "copy", "design", "merge", "select", "separate" ]
        }, {
            title: "fab fa-octopus-deploy",
            searchTerms: []
        }, {
            title: "fab fa-odnoklassniki",
            searchTerms: []
        }, {
            title: "fab fa-odysee",
            searchTerms: []
        }, {
            title: "fas fa-oil-can",
            searchTerms: [ "auto", "crude", "gasoline", "grease", "lubricate", "petroleum" ]
        }, {
            title: "fas fa-oil-well",
            searchTerms: [ "drill", "oil", "rig" ]
        }, {
            title: "fab fa-old-republic",
            searchTerms: [ "politics", "star wars" ]
        }, {
            title: "fas fa-om",
            searchTerms: [ "Hindu", "buddhism", "hinduism", "jainism", "mantra", "om", "religion" ]
        }, {
            title: "fab fa-opencart",
            searchTerms: []
        }, {
            title: "fab fa-openid",
            searchTerms: []
        }, {
            title: "fab fa-opera",
            searchTerms: []
        }, {
            title: "fab fa-optin-monster",
            searchTerms: []
        }, {
            title: "fab fa-orcid",
            searchTerms: []
        }, {
            title: "fab fa-osi",
            searchTerms: []
        }, {
            title: "fas fa-otter",
            searchTerms: [ "animal", "badger", "fauna", "fishing", "fur", "mammal", "marten", "otter", "playful" ]
        }, {
            title: "fas fa-outdent",
            searchTerms: [ "align", "justify", "paragraph", "tab" ]
        }, {
            title: "fas fa-p",
            searchTerms: [ "Latin Capital Letter P", "Latin Small Letter P", "letter" ]
        }, {
            title: "fab fa-padlet",
            searchTerms: []
        }, {
            title: "fab fa-page4",
            searchTerms: []
        }, {
            title: "fab fa-pagelines",
            searchTerms: [ "eco", "flora", "leaf", "leaves", "nature", "plant", "tree" ]
        }, {
            title: "fas fa-pager",
            searchTerms: [ "beeper", "cell phone", "communication", "page", "pager" ]
        }, {
            title: "fas fa-paint-roller",
            searchTerms: [ "acrylic", "art", "brush", "color", "fill", "paint", "pigment", "watercolor" ]
        }, {
            title: "fas fa-paintbrush",
            searchTerms: [ "acrylic", "art", "brush", "color", "fill", "paint", "paintbrush", "painting", "pigment", "watercolor" ]
        }, {
            title: "fas fa-palette",
            searchTerms: [ "acrylic", "art", "artist palette", "brush", "color", "fill", "museum", "paint", "painting", "palette", "pigment", "watercolor" ]
        }, {
            title: "fab fa-palfed",
            searchTerms: []
        }, {
            title: "fas fa-pallet",
            searchTerms: [ "archive", "box", "inventory", "shipping", "warehouse" ]
        }, {
            title: "fas fa-panorama",
            searchTerms: [ "image", "landscape", "photo", "wide" ]
        }, {
            title: "fas fa-paper-plane",
            searchTerms: [ "air", "float", "fold", "mail", "paper", "send" ]
        }, {
            title: "far fa-paper-plane",
            searchTerms: [ "air", "float", "fold", "mail", "paper", "send" ]
        }, {
            title: "fas fa-paperclip",
            searchTerms: [ "attach", "attachment", "connect", "link", "papercli", "paperclip" ]
        }, {
            title: "fas fa-parachute-box",
            searchTerms: [ "aid", "assistance", "goods", "relief", "rescue", "supplies" ]
        }, {
            title: "fas fa-paragraph",
            searchTerms: [ "Pilcrow Sign", "edit", "format", "text", "writing" ]
        }, {
            title: "fas fa-passport",
            searchTerms: [ "document", "id", "identification", "issued", "travel" ]
        }, {
            title: "fas fa-paste",
            searchTerms: [ "clipboard", "copy", "document", "paper" ]
        }, {
            title: "far fa-paste",
            searchTerms: [ "clipboard", "copy", "document", "paper" ]
        }, {
            title: "fab fa-patreon",
            searchTerms: []
        }, {
            title: "fas fa-pause",
            searchTerms: [ "bar", "double", "hold", "pause", "pause button", "vertical", "wait" ]
        }, {
            title: "fas fa-paw",
            searchTerms: [ "animal", "cat", "dog", "pet", "print" ]
        }, {
            title: "fab fa-paypal",
            searchTerms: []
        }, {
            title: "fas fa-peace",
            searchTerms: [ "peace", "peace symbol", "serenity", "tranquility", "truce", "war" ]
        }, {
            title: "fas fa-pen",
            searchTerms: [ "ballpoint", "design", "edit", "pen", "update", "write" ]
        }, {
            title: "fas fa-pen-clip",
            searchTerms: [ "design", "edit", "update", "write" ]
        }, {
            title: "fas fa-pen-fancy",
            searchTerms: [ "black nib", "design", "edit", "fountain", "fountain pen", "nib", "pen", "update", "write" ]
        }, {
            title: "fas fa-pen-nib",
            searchTerms: [ "design", "edit", "fountain pen", "update", "write" ]
        }, {
            title: "fas fa-pen-ruler",
            searchTerms: [ "design", "draft", "draw", "pencil" ]
        }, {
            title: "fas fa-pen-to-square",
            searchTerms: [ "edit", "pen", "pencil", "update", "write" ]
        }, {
            title: "far fa-pen-to-square",
            searchTerms: [ "edit", "pen", "pencil", "update", "write" ]
        }, {
            title: "fas fa-pencil",
            searchTerms: [ "Lower Left Pencil", "design", "draw", "edit", "lead", "pencil", "update", "write" ]
        }, {
            title: "fas fa-people-arrows",
            searchTerms: [ "distance", "isolation", "separate", "social distancing", "users-people" ]
        }, {
            title: "fas fa-people-carry-box",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-people-group",
            searchTerms: [ "family", "group", "team" ]
        }, {
            title: "fas fa-people-line",
            searchTerms: [ "group", "need" ]
        }, {
            title: "fas fa-people-pulling",
            searchTerms: [ "forced return", "yanking" ]
        }, {
            title: "fas fa-people-robbery",
            searchTerms: [ "criminal", "hands up", "looting", "robbery", "steal" ]
        }, {
            title: "fas fa-people-roof",
            searchTerms: [ "family", "group", "manage", "people", "safe", "shelter" ]
        }, {
            title: "fas fa-pepper-hot",
            searchTerms: [ "buffalo wings", "capsicum", "chili", "chilli", "habanero", "hot", "hot pepper", "jalapeno", "mexican", "pepper", "spicy", "tabasco", "vegetable" ]
        }, {
            title: "fab fa-perbyte",
            searchTerms: []
        }, {
            title: "fas fa-percent",
            searchTerms: [ "Percent Sign", "discount", "fraction", "proportion", "rate", "ratio" ]
        }, {
            title: "fab fa-periscope",
            searchTerms: []
        }, {
            title: "fas fa-person",
            searchTerms: [ "man", "person standing", "stand", "standing", "woman" ]
        }, {
            title: "fas fa-person-arrow-down-to-line",
            searchTerms: [ "ground", "indigenous", "native" ]
        }, {
            title: "fas fa-person-arrow-up-from-line",
            searchTerms: [ "population", "rise" ]
        }, {
            title: "fas fa-person-biking",
            searchTerms: [ "bicycle", "bike", "biking", "cyclist", "pedal", "person biking", "summer", "wheel" ]
        }, {
            title: "fas fa-person-booth",
            searchTerms: [ "changing room", "curtain", "vote", "voting" ]
        }, {
            title: "fas fa-person-breastfeeding",
            searchTerms: [ "baby", "child", "infant", "mother", "nutrition", "sustenance" ]
        }, {
            title: "fas fa-person-burst",
            searchTerms: [ "abuse", "accident", "crash", "explode", "violence" ]
        }, {
            title: "fas fa-person-cane",
            searchTerms: [ "aging", "cane", "elderly", "old", "staff" ]
        }, {
            title: "fas fa-person-chalkboard",
            searchTerms: [ "blackboard", "instructor", "keynote", "lesson", "presentation", "teacher" ]
        }, {
            title: "fas fa-person-circle-check",
            searchTerms: [ "approved", "not affected", "ok", "okay" ]
        }, {
            title: "fas fa-person-circle-exclamation",
            searchTerms: [ "affected", "alert", "lost", "missing" ]
        }, {
            title: "fas fa-person-circle-minus",
            searchTerms: [ "delete", "remove" ]
        }, {
            title: "fas fa-person-circle-plus",
            searchTerms: [ "add", "found" ]
        }, {
            title: "fas fa-person-circle-question",
            searchTerms: [ "lost", "missing" ]
        }, {
            title: "fas fa-person-circle-xmark",
            searchTerms: [ "dead", "removed" ]
        }, {
            title: "fas fa-person-digging",
            searchTerms: [ "bury", "construction", "debris", "dig", "men at work" ]
        }, {
            title: "fas fa-person-dots-from-line",
            searchTerms: [ "allergy", "diagnosis" ]
        }, {
            title: "fas fa-person-dress",
            searchTerms: [ "man", "skirt", "woman" ]
        }, {
            title: "fas fa-person-dress-burst",
            searchTerms: [ "abuse", "accident", "crash", "explode", "violence" ]
        }, {
            title: "fas fa-person-drowning",
            searchTerms: [ "drown", "emergency", "swim" ]
        }, {
            title: "fas fa-person-falling",
            searchTerms: [ "accident", "fall", "trip" ]
        }, {
            title: "fas fa-person-falling-burst",
            searchTerms: [ "accident", "crash", "death", "fall", "homicide", "murder" ]
        }, {
            title: "fas fa-person-half-dress",
            searchTerms: [ "gender", "man", "restroom", "transgender", "woman" ]
        }, {
            title: "fas fa-person-harassing",
            searchTerms: [ "abuse", "scream", "shame", "shout", "yell" ]
        }, {
            title: "fas fa-person-hiking",
            searchTerms: [ "autumn", "fall", "hike", "mountain", "outdoors", "summer", "walk" ]
        }, {
            title: "fas fa-person-military-pointing",
            searchTerms: [ "army", "customs", "guard" ]
        }, {
            title: "fas fa-person-military-rifle",
            searchTerms: [ "armed forces", "army", "military", "rifle", "war" ]
        }, {
            title: "fas fa-person-military-to-person",
            searchTerms: [ "civilian", "coordination", "military" ]
        }, {
            title: "fas fa-person-praying",
            searchTerms: [ "kneel", "place of worship", "religion", "thank", "worship" ]
        }, {
            title: "fas fa-person-pregnant",
            searchTerms: [ "baby", "birth", "child", "pregnant", "pregnant woman", "woman" ]
        }, {
            title: "fas fa-person-rays",
            searchTerms: [ "affected", "focus", "shine" ]
        }, {
            title: "fas fa-person-rifle",
            searchTerms: [ "army", "combatant", "gun", "military", "rifle", "war" ]
        }, {
            title: "fas fa-person-running",
            searchTerms: [ "exit", "flee", "marathon", "person running", "race", "running" ]
        }, {
            title: "fas fa-person-shelter",
            searchTerms: [ "house", "inside", "roof", "safe", "safety", "shelter" ]
        }, {
            title: "fas fa-person-skating",
            searchTerms: [ "figure skating", "ice", "olympics", "rink", "skate", "winter" ]
        }, {
            title: "fas fa-person-skiing",
            searchTerms: [ "downhill", "olympics", "ski", "skier", "snow", "winter" ]
        }, {
            title: "fas fa-person-skiing-nordic",
            searchTerms: [ "cross country", "olympics", "winter" ]
        }, {
            title: "fas fa-person-snowboarding",
            searchTerms: [ "olympics", "ski", "snow", "snowboard", "snowboarder", "winter" ]
        }, {
            title: "fas fa-person-swimming",
            searchTerms: [ "ocean", "person swimming", "pool", "sea", "swim", "water" ]
        }, {
            title: "fas fa-person-through-window",
            searchTerms: [ "door", "exit", "forced entry", "leave", "robbery", "steal", "window" ]
        }, {
            title: "fas fa-person-walking",
            searchTerms: [ "crosswalk", "exercise", "hike", "move", "person walking", "walk", "walking" ]
        }, {
            title: "fas fa-person-walking-arrow-loop-left",
            searchTerms: [ "population return", "return" ]
        }, {
            title: "fas fa-person-walking-arrow-right",
            searchTerms: [ "exit", "internally displaced", "leave", "refugee" ]
        }, {
            title: "fas fa-person-walking-dashed-line-arrow-right",
            searchTerms: [ "exit", "refugee" ]
        }, {
            title: "fas fa-person-walking-luggage",
            searchTerms: [ "bag", "baggage", "briefcase", "carry-on", "deployment", "rolling" ]
        }, {
            title: "fas fa-person-walking-with-cane",
            searchTerms: [ "blind", "cane" ]
        }, {
            title: "fas fa-peseta-sign",
            searchTerms: [ "Peseta Sign", "currency" ]
        }, {
            title: "fas fa-peso-sign",
            searchTerms: [ "Peso Sign", "currency" ]
        }, {
            title: "fab fa-phabricator",
            searchTerms: []
        }, {
            title: "fab fa-phoenix-framework",
            searchTerms: []
        }, {
            title: "fab fa-phoenix-squadron",
            searchTerms: []
        }, {
            title: "fas fa-phone",
            searchTerms: [ "Left Hand Telephone Receiver", "call", "earphone", "number", "phone", "receiver", "support", "telephone", "telephone receiver", "voice" ]
        }, {
            title: "fas fa-phone-flip",
            searchTerms: [ "Right Hand Telephone Receiver", "call", "earphone", "number", "support", "telephone", "voice" ]
        }, {
            title: "fas fa-phone-slash",
            searchTerms: [ "call", "cancel", "earphone", "mute", "number", "support", "telephone", "voice" ]
        }, {
            title: "fas fa-phone-volume",
            searchTerms: [ "call", "earphone", "number", "sound", "support", "telephone", "voice", "volume-control-phone" ]
        }, {
            title: "fas fa-photo-film",
            searchTerms: [ "av", "film", "image", "library", "media" ]
        }, {
            title: "fab fa-php",
            searchTerms: []
        }, {
            title: "fab fa-pied-piper",
            searchTerms: []
        }, {
            title: "fab fa-pied-piper-alt",
            searchTerms: []
        }, {
            title: "fab fa-pied-piper-hat",
            searchTerms: [ "clothing" ]
        }, {
            title: "fab fa-pied-piper-pp",
            searchTerms: []
        }, {
            title: "fas fa-piggy-bank",
            searchTerms: [ "bank", "save", "savings" ]
        }, {
            title: "fas fa-pills",
            searchTerms: [ "drugs", "medicine", "prescription", "tablets" ]
        }, {
            title: "fab fa-pinterest",
            searchTerms: []
        }, {
            title: "fab fa-pinterest-p",
            searchTerms: []
        }, {
            title: "fab fa-pix",
            searchTerms: []
        }, {
            title: "fas fa-pizza-slice",
            searchTerms: [ "cheese", "chicago", "italian", "mozzarella", "new york", "pepperoni", "pie", "slice", "teenage mutant ninja turtles", "tomato" ]
        }, {
            title: "fas fa-place-of-worship",
            searchTerms: [ "building", "church", "holy", "mosque", "synagogue" ]
        }, {
            title: "fas fa-plane",
            searchTerms: [ "airplane", "airport", "destination", "fly", "location", "mode", "travel", "trip" ]
        }, {
            title: "fas fa-plane-arrival",
            searchTerms: [ "aeroplane", "airplane", "airplane arrival", "airport", "arrivals", "arriving", "destination", "fly", "land", "landing", "location", "mode", "travel", "trip" ]
        }, {
            title: "fas fa-plane-circle-check",
            searchTerms: [ "airplane", "airport", "flight", "fly", "not affected", "ok", "okay", "travel" ]
        }, {
            title: "fas fa-plane-circle-exclamation",
            searchTerms: [ "affected", "airplane", "airport", "flight", "fly", "travel" ]
        }, {
            title: "fas fa-plane-circle-xmark",
            searchTerms: [ "airplane", "airport", "destroy", "flight", "fly", "travel" ]
        }, {
            title: "fas fa-plane-departure",
            searchTerms: [ "aeroplane", "airplane", "airplane departure", "airport", "check-in", "departing", "departure", "departures", "destination", "fly", "location", "mode", "take off", "taking off", "travel", "trip" ]
        }, {
            title: "fas fa-plane-lock",
            searchTerms: [ "airplane", "airport", "closed", "flight", "fly", "lockdown", "quarantine", "travel" ]
        }, {
            title: "fas fa-plane-slash",
            searchTerms: [ "airplane mode", "airport", "canceled", "covid-19", "delayed", "grounded", "travel" ]
        }, {
            title: "fas fa-plane-up",
            searchTerms: [ "airplane", "airport", "internet", "signal", "sky", "wifi", "wireless" ]
        }, {
            title: "fas fa-plant-wilt",
            searchTerms: [ "drought", "planting", "vegetation", "wilt" ]
        }, {
            title: "fas fa-plate-wheat",
            searchTerms: [ "bowl", "hunger", "rations", "wheat" ]
        }, {
            title: "fas fa-play",
            searchTerms: [ "arrow", "audio", "music", "play", "play button", "playing", "right", "sound", "start", "triangle", "video" ]
        }, {
            title: "fab fa-playstation",
            searchTerms: []
        }, {
            title: "fas fa-plug",
            searchTerms: [ "connect", "electric", "electric plug", "electricity", "online", "plug", "power" ]
        }, {
            title: "fas fa-plug-circle-bolt",
            searchTerms: [ "electric", "electricity", "plug", "power" ]
        }, {
            title: "fas fa-plug-circle-check",
            searchTerms: [ "electric", "electricity", "not affected", "ok", "okay", "plug", "power" ]
        }, {
            title: "fas fa-plug-circle-exclamation",
            searchTerms: [ "affected", "electric", "electricity", "plug", "power" ]
        }, {
            title: "fas fa-plug-circle-minus",
            searchTerms: [ "electric", "electricity", "plug", "power" ]
        }, {
            title: "fas fa-plug-circle-plus",
            searchTerms: [ "electric", "electricity", "plug", "power" ]
        }, {
            title: "fas fa-plug-circle-xmark",
            searchTerms: [ "destroy", "electric", "electricity", "outage", "plug", "power" ]
        }, {
            title: "fas fa-plus",
            searchTerms: [ "+", "Plus Sign", "add", "create", "expand", "math", "new", "plus", "positive", "shape", "sign" ]
        }, {
            title: "fas fa-plus-minus",
            searchTerms: [ "Plus-Minus Sign", "add", "math", "subtract" ]
        }, {
            title: "fas fa-podcast",
            searchTerms: [ "audio", "broadcast", "music", "sound" ]
        }, {
            title: "fas fa-poo",
            searchTerms: [ "crap", "dung", "face", "monster", "pile of poo", "poo", "poop", "shit", "smile", "turd" ]
        }, {
            title: "fas fa-poo-storm",
            searchTerms: [ "bolt", "cloud", "euphemism", "lightning", "mess", "poop", "shit", "turd" ]
        }, {
            title: "fas fa-poop",
            searchTerms: [ "crap", "poop", "shit", "smile", "turd" ]
        }, {
            title: "fas fa-power-off",
            searchTerms: [ "Power Symbol", "cancel", "computer", "on", "reboot", "restart" ]
        }, {
            title: "fas fa-prescription",
            searchTerms: [ "drugs", "medical", "medicine", "pharmacy", "rx" ]
        }, {
            title: "fas fa-prescription-bottle",
            searchTerms: [ "drugs", "medical", "medicine", "pharmacy", "rx" ]
        }, {
            title: "fas fa-prescription-bottle-medical",
            searchTerms: [ "drugs", "medical", "medicine", "pharmacy", "rx" ]
        }, {
            title: "fas fa-print",
            searchTerms: [ "Print Screen Symbol", "Printer Icon", "business", "computer", "copy", "document", "office", "paper", "printer" ]
        }, {
            title: "fab fa-product-hunt",
            searchTerms: []
        }, {
            title: "fas fa-pump-medical",
            searchTerms: [ "anti-bacterial", "clean", "covid-19", "disinfect", "hygiene", "medical grade", "sanitizer", "soap" ]
        }, {
            title: "fas fa-pump-soap",
            searchTerms: [ "anti-bacterial", "clean", "covid-19", "disinfect", "hygiene", "sanitizer", "soap" ]
        }, {
            title: "fab fa-pushed",
            searchTerms: []
        }, {
            title: "fas fa-puzzle-piece",
            searchTerms: [ "add-on", "addon", "clue", "game", "interlocking", "jigsaw", "piece", "puzzle", "puzzle piece", "section" ]
        }, {
            title: "fab fa-python",
            searchTerms: []
        }, {
            title: "fas fa-q",
            searchTerms: [ "Latin Capital Letter Q", "Latin Small Letter Q", "letter" ]
        }, {
            title: "fab fa-qq",
            searchTerms: []
        }, {
            title: "fas fa-qrcode",
            searchTerms: [ "barcode", "info", "information", "scan" ]
        }, {
            title: "fas fa-question",
            searchTerms: [ "?", "Question Mark", "help", "information", "mark", "outlined", "punctuation", "question", "red question mark", "support", "unknown", "white question mark" ]
        }, {
            title: "fab fa-quinscape",
            searchTerms: []
        }, {
            title: "fab fa-quora",
            searchTerms: []
        }, {
            title: "fas fa-quote-left",
            searchTerms: [ "Left Double Quotation Mark", "mention", "note", "phrase", "text", "type" ]
        }, {
            title: "fas fa-quote-right",
            searchTerms: [ "Right Double Quotation Mark", "mention", "note", "phrase", "text", "type" ]
        }, {
            title: "fas fa-r",
            searchTerms: [ "Latin Capital Letter R", "Latin Small Letter R", "letter" ]
        }, {
            title: "fab fa-r-project",
            searchTerms: []
        }, {
            title: "fas fa-radiation",
            searchTerms: [ "danger", "dangerous", "deadly", "hazard", "nuclear", "radioactive", "warning" ]
        }, {
            title: "fas fa-radio",
            searchTerms: [ "am", "broadcast", "fm", "frequency", "music", "news", "radio", "receiver", "transmitter", "tuner", "video" ]
        }, {
            title: "fas fa-rainbow",
            searchTerms: [ "gold", "leprechaun", "prism", "rain", "rainbow", "sky" ]
        }, {
            title: "fas fa-ranking-star",
            searchTerms: [ "chart", "first place", "podium", "rank", "win" ]
        }, {
            title: "fab fa-raspberry-pi",
            searchTerms: []
        }, {
            title: "fab fa-ravelry",
            searchTerms: []
        }, {
            title: "fab fa-react",
            searchTerms: []
        }, {
            title: "fab fa-reacteurope",
            searchTerms: []
        }, {
            title: "fab fa-readme",
            searchTerms: []
        }, {
            title: "fab fa-rebel",
            searchTerms: []
        }, {
            title: "fas fa-receipt",
            searchTerms: [ "accounting", "bookkeeping", "check", "evidence", "invoice", "money", "pay", "proof", "receipt", "table" ]
        }, {
            title: "fas fa-record-vinyl",
            searchTerms: [ "LP", "album", "analog", "music", "phonograph", "sound" ]
        }, {
            title: "fas fa-rectangle-ad",
            searchTerms: [ "advertisement", "media", "newspaper", "promotion", "publicity" ]
        }, {
            title: "fas fa-rectangle-list",
            searchTerms: [ "checklist", "completed", "done", "finished", "ol", "todo", "ul" ]
        }, {
            title: "far fa-rectangle-list",
            searchTerms: [ "checklist", "completed", "done", "finished", "ol", "todo", "ul" ]
        }, {
            title: "fas fa-rectangle-xmark",
            searchTerms: [ "browser", "cancel", "computer", "development" ]
        }, {
            title: "far fa-rectangle-xmark",
            searchTerms: [ "browser", "cancel", "computer", "development" ]
        }, {
            title: "fas fa-recycle",
            searchTerms: [ "Recycling Symbol For Generic Materials", "Universal Recycling Symbol", "Waste", "compost", "garbage", "recycle", "recycling symbol", "reuse", "trash" ]
        }, {
            title: "fab fa-red-river",
            searchTerms: []
        }, {
            title: "fab fa-reddit",
            searchTerms: []
        }, {
            title: "fab fa-reddit-alien",
            searchTerms: []
        }, {
            title: "fab fa-redhat",
            searchTerms: [ "linux", "operating system", "os" ]
        }, {
            title: "fas fa-registered",
            searchTerms: [ "copyright", "mark", "r", "registered", "trademark" ]
        }, {
            title: "far fa-registered",
            searchTerms: [ "copyright", "mark", "r", "registered", "trademark" ]
        }, {
            title: "fab fa-renren",
            searchTerms: []
        }, {
            title: "fas fa-repeat",
            searchTerms: [ "arrow", "clockwise", "flip", "reload", "repeat", "repeat button", "rewind", "switch" ]
        }, {
            title: "fas fa-reply",
            searchTerms: [ "mail", "message", "respond" ]
        }, {
            title: "fas fa-reply-all",
            searchTerms: [ "mail", "message", "respond" ]
        }, {
            title: "fab fa-replyd",
            searchTerms: []
        }, {
            title: "fas fa-republican",
            searchTerms: [ "american", "conservative", "election", "elephant", "politics", "republican party", "right", "right-wing", "usa" ]
        }, {
            title: "fab fa-researchgate",
            searchTerms: []
        }, {
            title: "fab fa-resolving",
            searchTerms: []
        }, {
            title: "fas fa-restroom",
            searchTerms: [ "bathroom", "toilet", "water closet", "wc" ]
        }, {
            title: "fas fa-retweet",
            searchTerms: [ "refresh", "reload", "share", "swap" ]
        }, {
            title: "fab fa-rev",
            searchTerms: []
        }, {
            title: "fas fa-ribbon",
            searchTerms: [ "badge", "cause", "celebration", "lapel", "pin", "reminder", "reminder ribbon", "ribbon" ]
        }, {
            title: "fas fa-right-from-bracket",
            searchTerms: [ "arrow", "exit", "leave", "log out", "logout", "sign-out" ]
        }, {
            title: "fas fa-right-left",
            searchTerms: [ "arrow", "arrows", "exchange", "reciprocate", "return", "swap", "transfer" ]
        }, {
            title: "fas fa-right-long",
            searchTerms: [ "forward", "long-arrow-right", "next" ]
        }, {
            title: "fas fa-right-to-bracket",
            searchTerms: [ "arrow", "enter", "join", "log in", "login", "sign in", "sign up", "sign-in", "signin", "signup" ]
        }, {
            title: "fas fa-ring",
            searchTerms: [ "Dungeons & Dragons", "Gollum", "band", "binding", "d&d", "dnd", "engagement", "fantasy", "gold", "jewelry", "marriage", "precious" ]
        }, {
            title: "fas fa-road",
            searchTerms: [ "highway", "map", "motorway", "pavement", "road", "route", "street", "travel" ]
        }, {
            title: "fas fa-road-barrier",
            searchTerms: [ "block", "border", "no entry", "roadblock" ]
        }, {
            title: "fas fa-road-bridge",
            searchTerms: [ "bridge", "infrastructure", "road", "travel" ]
        }, {
            title: "fas fa-road-circle-check",
            searchTerms: [ "freeway", "highway", "not affected", "ok", "okay", "pavement", "road" ]
        }, {
            title: "fas fa-road-circle-exclamation",
            searchTerms: [ "affected", "freeway", "highway", "pavement", "road" ]
        }, {
            title: "fas fa-road-circle-xmark",
            searchTerms: [ "destroy", "freeway", "highway", "pavement", "road" ]
        }, {
            title: "fas fa-road-lock",
            searchTerms: [ "closed", "freeway", "highway", "lockdown", "pavement", "quarantine", "road" ]
        }, {
            title: "fas fa-road-spikes",
            searchTerms: [ "barrier", "roadblock", "spikes" ]
        }, {
            title: "fas fa-robot",
            searchTerms: [ "android", "automate", "computer", "cyborg", "face", "monster", "robot" ]
        }, {
            title: "fas fa-rocket",
            searchTerms: [ "aircraft", "app", "jet", "launch", "nasa", "space" ]
        }, {
            title: "fab fa-rocketchat",
            searchTerms: []
        }, {
            title: "fab fa-rockrms",
            searchTerms: []
        }, {
            title: "fas fa-rotate",
            searchTerms: [ "anticlockwise", "arrow", "counterclockwise", "counterclockwise arrows button", "exchange", "refresh", "reload", "rotate", "swap", "withershins" ]
        }, {
            title: "fas fa-rotate-left",
            searchTerms: [ "back", "control z", "exchange", "oops", "return", "swap" ]
        }, {
            title: "fas fa-rotate-right",
            searchTerms: [ "forward", "refresh", "reload", "repeat" ]
        }, {
            title: "fas fa-route",
            searchTerms: [ "directions", "navigation", "travel" ]
        }, {
            title: "fas fa-rss",
            searchTerms: [ "blog", "feed", "journal", "news", "writing" ]
        }, {
            title: "fas fa-ruble-sign",
            searchTerms: [ "Ruble Sign", "currency" ]
        }, {
            title: "fas fa-rug",
            searchTerms: [ "blanket", "carpet", "rug", "textile" ]
        }, {
            title: "fas fa-ruler",
            searchTerms: [ "design", "draft", "length", "measure", "planning", "ruler", "straight edge", "straight ruler" ]
        }, {
            title: "fas fa-ruler-combined",
            searchTerms: [ "design", "draft", "length", "measure", "planning" ]
        }, {
            title: "fas fa-ruler-horizontal",
            searchTerms: [ "design", "draft", "length", "measure", "planning" ]
        }, {
            title: "fas fa-ruler-vertical",
            searchTerms: [ "design", "draft", "length", "measure", "planning" ]
        }, {
            title: "fas fa-rupee-sign",
            searchTerms: [ "Rupee Sign", "currency" ]
        }, {
            title: "fas fa-rupiah-sign",
            searchTerms: [ "currency" ]
        }, {
            title: "fab fa-rust",
            searchTerms: []
        }, {
            title: "fas fa-s",
            searchTerms: [ "Latin Capital Letter S", "Latin Small Letter S", "letter" ]
        }, {
            title: "fas fa-sack-dollar",
            searchTerms: [ "bag", "burlap", "cash", "dollar", "money", "money bag", "moneybag", "robber", "santa", "usd" ]
        }, {
            title: "fas fa-sack-xmark",
            searchTerms: [ "bag", "burlap", "rations" ]
        }, {
            title: "fab fa-safari",
            searchTerms: [ "browser" ]
        }, {
            title: "fas fa-sailboat",
            searchTerms: [ "dinghy", "mast", "sailboat", "sailing", "yacht" ]
        }, {
            title: "fab fa-salesforce",
            searchTerms: []
        }, {
            title: "fab fa-sass",
            searchTerms: []
        }, {
            title: "fas fa-satellite",
            searchTerms: [ "communications", "hardware", "orbit", "satellite", "space" ]
        }, {
            title: "fas fa-satellite-dish",
            searchTerms: [ "SETI", "antenna", "communications", "dish", "hardware", "radar", "receiver", "satellite", "satellite antenna", "saucer", "signal", "space" ]
        }, {
            title: "fas fa-scale-balanced",
            searchTerms: [ "Libra", "balance", "balance scale", "balanced", "justice", "law", "legal", "measure", "rule", "scale", "weight", "zodiac" ]
        }, {
            title: "fas fa-scale-unbalanced",
            searchTerms: [ "justice", "legal", "measure", "unbalanced", "weight" ]
        }, {
            title: "fas fa-scale-unbalanced-flip",
            searchTerms: [ "justice", "legal", "measure", "unbalanced", "weight" ]
        }, {
            title: "fab fa-schlix",
            searchTerms: []
        }, {
            title: "fas fa-school",
            searchTerms: [ "building", "education", "learn", "school", "student", "teacher" ]
        }, {
            title: "fas fa-school-circle-check",
            searchTerms: [ "not affected", "ok", "okay", "schoolhouse" ]
        }, {
            title: "fas fa-school-circle-exclamation",
            searchTerms: [ "affected", "schoolhouse" ]
        }, {
            title: "fas fa-school-circle-xmark",
            searchTerms: [ "destroy", "schoolhouse" ]
        }, {
            title: "fas fa-school-flag",
            searchTerms: [ "educate", "flag", "school", "schoolhouse" ]
        }, {
            title: "fas fa-school-lock",
            searchTerms: [ "closed", "lockdown", "quarantine", "schoolhouse" ]
        }, {
            title: "fas fa-scissors",
            searchTerms: [ "Black Safety Scissors", "White Scissors", "clip", "cutting", "scissors", "snip", "tool" ]
        }, {
            title: "fab fa-screenpal",
            searchTerms: []
        }, {
            title: "fas fa-screwdriver",
            searchTerms: [ "admin", "fix", "mechanic", "repair", "screw", "screwdriver", "settings", "tool" ]
        }, {
            title: "fas fa-screwdriver-wrench",
            searchTerms: [ "admin", "fix", "repair", "screwdriver", "settings", "tools", "wrench" ]
        }, {
            title: "fab fa-scribd",
            searchTerms: []
        }, {
            title: "fas fa-scroll",
            searchTerms: [ "Dungeons & Dragons", "announcement", "d&d", "dnd", "fantasy", "paper", "script", "scroll" ]
        }, {
            title: "fas fa-scroll-torah",
            searchTerms: [ "book", "jewish", "judaism", "religion", "scroll" ]
        }, {
            title: "fas fa-sd-card",
            searchTerms: [ "image", "memory", "photo", "save" ]
        }, {
            title: "fab fa-searchengin",
            searchTerms: []
        }, {
            title: "fas fa-section",
            searchTerms: [ "Section Sign", "law", "legal", "silcrow" ]
        }, {
            title: "fas fa-seedling",
            searchTerms: [ "environment", "flora", "grow", "plant", "sapling", "seedling", "vegan", "young" ]
        }, {
            title: "fab fa-sellcast",
            searchTerms: [ "eercast" ]
        }, {
            title: "fab fa-sellsy",
            searchTerms: []
        }, {
            title: "fas fa-server",
            searchTerms: [ "computer", "cpu", "database", "hardware", "network" ]
        }, {
            title: "fab fa-servicestack",
            searchTerms: []
        }, {
            title: "fas fa-shapes",
            searchTerms: [ "blocks", "build", "circle", "square", "triangle" ]
        }, {
            title: "fas fa-share",
            searchTerms: [ "forward", "save", "send", "social" ]
        }, {
            title: "fas fa-share-from-square",
            searchTerms: [ "forward", "save", "send", "social" ]
        }, {
            title: "far fa-share-from-square",
            searchTerms: [ "forward", "save", "send", "social" ]
        }, {
            title: "fas fa-share-nodes",
            searchTerms: [ "forward", "save", "send", "social" ]
        }, {
            title: "fas fa-sheet-plastic",
            searchTerms: [ "plastic", "plastic wrap", "protect", "tarp", "tarpaulin", "waterproof" ]
        }, {
            title: "fas fa-shekel-sign",
            searchTerms: [ "New Sheqel Sign", "currency", "ils", "money" ]
        }, {
            title: "fas fa-shield",
            searchTerms: [ "achievement", "armor", "award", "block", "cleric", "defend", "defense", "holy", "paladin", "protect", "safety", "security", "shield", "weapon", "winner" ]
        }, {
            title: "fas fa-shield-cat",
            searchTerms: [ "animal", "feline", "pet", "protect", "safety", "veterinary" ]
        }, {
            title: "fas fa-shield-dog",
            searchTerms: [ "animal", "canine", "pet", "protect", "safety", "veterinary" ]
        }, {
            title: "fas fa-shield-halved",
            searchTerms: [ "achievement", "armor", "award", "block", "cleric", "defend", "defense", "holy", "paladin", "security", "shield", "weapon", "winner" ]
        }, {
            title: "fas fa-shield-heart",
            searchTerms: [ "love", "protect", "safe", "safety", "shield" ]
        }, {
            title: "fas fa-shield-virus",
            searchTerms: [ "antibodies", "barrier", "coronavirus", "covid-19", "flu", "health", "infection", "pandemic", "protect", "safety", "vaccine" ]
        }, {
            title: "fas fa-ship",
            searchTerms: [ "boat", "passenger", "sea", "ship", "water" ]
        }, {
            title: "fas fa-shirt",
            searchTerms: [ "clothing", "fashion", "garment", "shirt", "short sleeve", "t-shirt", "tshirt" ]
        }, {
            title: "fab fa-shirtsinbulk",
            searchTerms: []
        }, {
            title: "fas fa-shoe-prints",
            searchTerms: [ "feet", "footprints", "steps", "walk" ]
        }, {
            title: "fas fa-shop",
            searchTerms: [ "bodega", "building", "buy", "market", "purchase", "shopping", "store" ]
        }, {
            title: "fas fa-shop-lock",
            searchTerms: [ "bodega", "building", "buy", "closed", "lock", "lockdown", "market", "purchase", "quarantine", "shop", "shopping", "store" ]
        }, {
            title: "fas fa-shop-slash",
            searchTerms: [ "building", "buy", "closed", "covid-19", "purchase", "shopping" ]
        }, {
            title: "fab fa-shopify",
            searchTerms: []
        }, {
            title: "fab fa-shopware",
            searchTerms: []
        }, {
            title: "fas fa-shower",
            searchTerms: [ "bath", "clean", "faucet", "shower", "water" ]
        }, {
            title: "fas fa-shrimp",
            searchTerms: [ "allergy", "crustacean", "prawn", "seafood", "shellfish", "shrimp", "tail" ]
        }, {
            title: "fas fa-shuffle",
            searchTerms: [ "arrow", "arrows", "crossed", "shuffle", "shuffle tracks button", "sort", "swap", "switch", "transfer" ]
        }, {
            title: "fas fa-shuttle-space",
            searchTerms: [ "astronaut", "machine", "nasa", "rocket", "space", "transportation" ]
        }, {
            title: "fas fa-sign-hanging",
            searchTerms: [ "directions", "real estate", "signage", "wayfinding" ]
        }, {
            title: "fas fa-signal",
            searchTerms: [ "antenna", "antenna bars", "bar", "bars", "cell", "graph", "mobile", "online", "phone", "reception", "status" ]
        }, {
            title: "fas fa-signature",
            searchTerms: [ "John Hancock", "cursive", "name", "writing" ]
        }, {
            title: "fas fa-signs-post",
            searchTerms: [ "directions", "directory", "map", "signage", "wayfinding" ]
        }, {
            title: "fas fa-sim-card",
            searchTerms: [ "hard drive", "hardware", "portable", "storage", "technology", "tiny" ]
        }, {
            title: "fab fa-simplybuilt",
            searchTerms: []
        }, {
            title: "fas fa-sink",
            searchTerms: [ "bathroom", "covid-19", "faucet", "kitchen", "wash" ]
        }, {
            title: "fab fa-sistrix",
            searchTerms: []
        }, {
            title: "fas fa-sitemap",
            searchTerms: [ "directory", "hierarchy", "ia", "information architecture", "organization" ]
        }, {
            title: "fab fa-sith",
            searchTerms: []
        }, {
            title: "fab fa-sitrox",
            searchTerms: []
        }, {
            title: "fab fa-sketch",
            searchTerms: [ "app", "design", "interface" ]
        }, {
            title: "fas fa-skull",
            searchTerms: [ "bones", "death", "face", "fairy tale", "monster", "skeleton", "skull", "x-ray", "yorick" ]
        }, {
            title: "fas fa-skull-crossbones",
            searchTerms: [ "Black Skull and Crossbones", "Dungeons & Dragons", "alert", "bones", "crossbones", "d&d", "danger", "dangerous area", "dead", "deadly", "death", "dnd", "face", "fantasy", "halloween", "holiday", "jolly-roger", "monster", "pirate", "poison", "skeleton", "skull", "skull and crossbones", "warning" ]
        }, {
            title: "fab fa-skyatlas",
            searchTerms: []
        }, {
            title: "fab fa-skype",
            searchTerms: []
        }, {
            title: "fab fa-slack",
            searchTerms: [ "anchor", "hash", "hashtag" ]
        }, {
            title: "fas fa-slash",
            searchTerms: [ "cancel", "close", "mute", "off", "stop", "x" ]
        }, {
            title: "fas fa-sleigh",
            searchTerms: [ "christmas", "claus", "fly", "holiday", "santa", "sled", "snow", "xmas" ]
        }, {
            title: "fas fa-sliders",
            searchTerms: [ "adjust", "settings", "sliders", "toggle" ]
        }, {
            title: "fab fa-slideshare",
            searchTerms: []
        }, {
            title: "fas fa-smog",
            searchTerms: [ "dragon", "fog", "haze", "pollution", "smoke", "weather" ]
        }, {
            title: "fas fa-smoking",
            searchTerms: [ "cancer", "cigarette", "nicotine", "smoking", "smoking status", "tobacco" ]
        }, {
            title: "fab fa-snapchat",
            searchTerms: []
        }, {
            title: "fas fa-snowflake",
            searchTerms: [ "Heavy Chevron Snowflake", "cold", "precipitation", "rain", "snow", "snowfall", "snowflake", "winter" ]
        }, {
            title: "far fa-snowflake",
            searchTerms: [ "Heavy Chevron Snowflake", "cold", "precipitation", "rain", "snow", "snowfall", "snowflake", "winter" ]
        }, {
            title: "fas fa-snowman",
            searchTerms: [ "cold", "decoration", "frost", "frosty", "holiday", "snow", "snowman", "snowman without snow" ]
        }, {
            title: "fas fa-snowplow",
            searchTerms: [ "clean up", "cold", "road", "storm", "winter" ]
        }, {
            title: "fas fa-soap",
            searchTerms: [ "bar", "bathing", "bubbles", "clean", "cleaning", "covid-19", "hygiene", "lather", "soap", "soapdish", "wash" ]
        }, {
            title: "fas fa-socks",
            searchTerms: [ "business socks", "business time", "clothing", "feet", "flight of the conchords", "socks", "stocking", "wednesday" ]
        }, {
            title: "fas fa-solar-panel",
            searchTerms: [ "clean", "eco-friendly", "energy", "green", "sun" ]
        }, {
            title: "fas fa-sort",
            searchTerms: [ "filter", "order" ]
        }, {
            title: "fas fa-sort-down",
            searchTerms: [ "arrow", "descending", "filter", "order", "sort-desc" ]
        }, {
            title: "fas fa-sort-up",
            searchTerms: [ "arrow", "ascending", "filter", "order", "sort-asc" ]
        }, {
            title: "fab fa-soundcloud",
            searchTerms: []
        }, {
            title: "fab fa-sourcetree",
            searchTerms: []
        }, {
            title: "fas fa-spa",
            searchTerms: [ "flora", "massage", "mindfulness", "plant", "wellness" ]
        }, {
            title: "fab fa-space-awesome",
            searchTerms: [ "adventure", "rocket", "ship", "shuttle" ]
        }, {
            title: "fas fa-spaghetti-monster-flying",
            searchTerms: [ "agnosticism", "atheism", "flying spaghetti monster", "fsm" ]
        }, {
            title: "fab fa-speakap",
            searchTerms: []
        }, {
            title: "fab fa-speaker-deck",
            searchTerms: []
        }, {
            title: "fas fa-spell-check",
            searchTerms: [ "dictionary", "edit", "editor", "grammar", "text" ]
        }, {
            title: "fas fa-spider",
            searchTerms: [ "arachnid", "bug", "charlotte", "crawl", "eight", "halloween", "insect", "spider" ]
        }, {
            title: "fas fa-spinner",
            searchTerms: [ "circle", "loading", "progress" ]
        }, {
            title: "fas fa-splotch",
            searchTerms: [ "Ink", "blob", "blotch", "glob", "stain" ]
        }, {
            title: "fas fa-spoon",
            searchTerms: [ "cutlery", "dining", "scoop", "silverware", "spoon", "tableware" ]
        }, {
            title: "fab fa-spotify",
            searchTerms: []
        }, {
            title: "fas fa-spray-can",
            searchTerms: [ "Paint", "aerosol", "design", "graffiti", "tag" ]
        }, {
            title: "fas fa-spray-can-sparkles",
            searchTerms: [ "car", "clean", "deodorize", "fresh", "pine", "scent" ]
        }, {
            title: "fas fa-square",
            searchTerms: [ "Black Square", "black medium square", "block", "box", "geometric", "shape", "square", "white medium square" ]
        }, {
            title: "far fa-square",
            searchTerms: [ "Black Square", "black medium square", "block", "box", "geometric", "shape", "square", "white medium square" ]
        }, {
            title: "fas fa-square-arrow-up-right",
            searchTerms: [ "diagonal", "new", "open", "send", "share" ]
        }, {
            title: "fab fa-square-behance",
            searchTerms: []
        }, {
            title: "fas fa-square-caret-down",
            searchTerms: [ "arrow", "caret-square-o-down", "dropdown", "expand", "menu", "more", "triangle" ]
        }, {
            title: "far fa-square-caret-down",
            searchTerms: [ "arrow", "caret-square-o-down", "dropdown", "expand", "menu", "more", "triangle" ]
        }, {
            title: "fas fa-square-caret-left",
            searchTerms: [ "arrow", "back", "caret-square-o-left", "previous", "triangle" ]
        }, {
            title: "far fa-square-caret-left",
            searchTerms: [ "arrow", "back", "caret-square-o-left", "previous", "triangle" ]
        }, {
            title: "fas fa-square-caret-right",
            searchTerms: [ "arrow", "caret-square-o-right", "forward", "next", "triangle" ]
        }, {
            title: "far fa-square-caret-right",
            searchTerms: [ "arrow", "caret-square-o-right", "forward", "next", "triangle" ]
        }, {
            title: "fas fa-square-caret-up",
            searchTerms: [ "arrow", "caret-square-o-up", "collapse", "triangle", "upload" ]
        }, {
            title: "far fa-square-caret-up",
            searchTerms: [ "arrow", "caret-square-o-up", "collapse", "triangle", "upload" ]
        }, {
            title: "fas fa-square-check",
            searchTerms: [ "accept", "agree", "box", "button", "check", "check box with check", "check mark button", "checkmark", "confirm", "correct", "done", "mark", "ok", "select", "success", "tick", "todo", "yes", "✓" ]
        }, {
            title: "far fa-square-check",
            searchTerms: [ "accept", "agree", "box", "button", "check", "check box with check", "check mark button", "checkmark", "confirm", "correct", "done", "mark", "ok", "select", "success", "tick", "todo", "yes", "✓" ]
        }, {
            title: "fab fa-square-dribbble",
            searchTerms: []
        }, {
            title: "fas fa-square-envelope",
            searchTerms: [ "e-mail", "email", "letter", "mail", "message", "notification", "support" ]
        }, {
            title: "fab fa-square-facebook",
            searchTerms: [ "social network" ]
        }, {
            title: "fab fa-square-font-awesome",
            searchTerms: []
        }, {
            title: "fab fa-square-font-awesome-stroke",
            searchTerms: []
        }, {
            title: "fas fa-square-full",
            searchTerms: [ "black large square", "block", "blue", "blue square", "box", "brown", "brown square", "geometric", "green", "green square", "orange", "orange square", "purple", "purple square", "red", "red square", "shape", "square", "white large square", "yellow", "yellow square" ]
        }, {
            title: "far fa-square-full",
            searchTerms: [ "black large square", "block", "blue", "blue square", "box", "brown", "brown square", "geometric", "green", "green square", "orange", "orange square", "purple", "purple square", "red", "red square", "shape", "square", "white large square", "yellow", "yellow square" ]
        }, {
            title: "fab fa-square-git",
            searchTerms: []
        }, {
            title: "fab fa-square-github",
            searchTerms: [ "octocat" ]
        }, {
            title: "fab fa-square-gitlab",
            searchTerms: []
        }, {
            title: "fab fa-square-google-plus",
            searchTerms: [ "social network" ]
        }, {
            title: "fas fa-square-h",
            searchTerms: [ "directions", "emergency", "hospital", "hotel", "letter", "map" ]
        }, {
            title: "fab fa-square-hacker-news",
            searchTerms: []
        }, {
            title: "fab fa-square-instagram",
            searchTerms: []
        }, {
            title: "fab fa-square-js",
            searchTerms: []
        }, {
            title: "fab fa-square-lastfm",
            searchTerms: []
        }, {
            title: "fas fa-square-minus",
            searchTerms: [ "collapse", "delete", "hide", "minify", "negative", "remove", "shape", "trash" ]
        }, {
            title: "far fa-square-minus",
            searchTerms: [ "collapse", "delete", "hide", "minify", "negative", "remove", "shape", "trash" ]
        }, {
            title: "fas fa-square-nfi",
            searchTerms: [ "non-food item", "supplies" ]
        }, {
            title: "fab fa-square-odnoklassniki",
            searchTerms: []
        }, {
            title: "fas fa-square-parking",
            searchTerms: [ "auto", "car", "garage", "meter", "parking" ]
        }, {
            title: "fas fa-square-pen",
            searchTerms: [ "edit", "pencil-square", "update", "write" ]
        }, {
            title: "fas fa-square-person-confined",
            searchTerms: [ "captivity", "confined" ]
        }, {
            title: "fas fa-square-phone",
            searchTerms: [ "call", "earphone", "number", "support", "telephone", "voice" ]
        }, {
            title: "fas fa-square-phone-flip",
            searchTerms: [ "call", "earphone", "number", "support", "telephone", "voice" ]
        }, {
            title: "fab fa-square-pied-piper",
            searchTerms: []
        }, {
            title: "fab fa-square-pinterest",
            searchTerms: []
        }, {
            title: "fas fa-square-plus",
            searchTerms: [ "add", "create", "expand", "new", "positive", "shape" ]
        }, {
            title: "far fa-square-plus",
            searchTerms: [ "add", "create", "expand", "new", "positive", "shape" ]
        }, {
            title: "fas fa-square-poll-horizontal",
            searchTerms: [ "chart", "graph", "results", "survey", "trend", "vote", "voting" ]
        }, {
            title: "fas fa-square-poll-vertical",
            searchTerms: [ "chart", "graph", "results", "survey", "trend", "vote", "voting" ]
        }, {
            title: "fab fa-square-reddit",
            searchTerms: []
        }, {
            title: "fas fa-square-root-variable",
            searchTerms: [ "arithmetic", "calculus", "division", "math" ]
        }, {
            title: "fas fa-square-rss",
            searchTerms: [ "blog", "feed", "journal", "news", "writing" ]
        }, {
            title: "fas fa-square-share-nodes",
            searchTerms: [ "forward", "save", "send", "social" ]
        }, {
            title: "fab fa-square-snapchat",
            searchTerms: []
        }, {
            title: "fab fa-square-steam",
            searchTerms: []
        }, {
            title: "fab fa-square-tumblr",
            searchTerms: []
        }, {
            title: "fab fa-square-twitter",
            searchTerms: [ "social network", "tweet" ]
        }, {
            title: "fas fa-square-up-right",
            searchTerms: [ "arrow", "diagonal", "direction", "external-link-square", "intercardinal", "new", "northeast", "open", "share", "up-right arrow" ]
        }, {
            title: "fab fa-square-viadeo",
            searchTerms: []
        }, {
            title: "fab fa-square-vimeo",
            searchTerms: []
        }, {
            title: "fas fa-square-virus",
            searchTerms: [ "coronavirus", "covid-19", "disease", "flu", "infection", "pandemic" ]
        }, {
            title: "fab fa-square-whatsapp",
            searchTerms: []
        }, {
            title: "fab fa-square-xing",
            searchTerms: []
        }, {
            title: "fas fa-square-xmark",
            searchTerms: [ "close", "cross", "cross mark button", "incorrect", "mark", "notice", "notification", "notify", "problem", "square", "window", "wrong", "x", "×" ]
        }, {
            title: "fab fa-square-youtube",
            searchTerms: []
        }, {
            title: "fab fa-squarespace",
            searchTerms: []
        }, {
            title: "fab fa-stack-exchange",
            searchTerms: []
        }, {
            title: "fab fa-stack-overflow",
            searchTerms: []
        }, {
            title: "fab fa-stackpath",
            searchTerms: []
        }, {
            title: "fas fa-staff-snake",
            searchTerms: [ "asclepius", "asklepian", "health", "serpent", "wellness" ]
        }, {
            title: "fas fa-stairs",
            searchTerms: [ "exit", "steps", "up" ]
        }, {
            title: "fas fa-stamp",
            searchTerms: [ "art", "certificate", "imprint", "rubber", "seal" ]
        }, {
            title: "fas fa-stapler",
            searchTerms: [ "desktop", "milton", "office", "paperclip", "staple" ]
        }, {
            title: "fas fa-star",
            searchTerms: [ "achievement", "award", "favorite", "important", "night", "rating", "score", "star" ]
        }, {
            title: "far fa-star",
            searchTerms: [ "achievement", "award", "favorite", "important", "night", "rating", "score", "star" ]
        }, {
            title: "fas fa-star-and-crescent",
            searchTerms: [ "Muslim", "islam", "muslim", "religion", "star and crescent" ]
        }, {
            title: "fas fa-star-half",
            searchTerms: [ "achievement", "award", "rating", "score", "star-half-empty", "star-half-full" ]
        }, {
            title: "far fa-star-half",
            searchTerms: [ "achievement", "award", "rating", "score", "star-half-empty", "star-half-full" ]
        }, {
            title: "fas fa-star-half-stroke",
            searchTerms: [ "achievement", "award", "rating", "score", "star-half-empty", "star-half-full" ]
        }, {
            title: "far fa-star-half-stroke",
            searchTerms: [ "achievement", "award", "rating", "score", "star-half-empty", "star-half-full" ]
        }, {
            title: "fas fa-star-of-david",
            searchTerms: [ "David", "Jew", "Jewish", "jewish", "judaism", "religion", "star", "star of David" ]
        }, {
            title: "fas fa-star-of-life",
            searchTerms: [ "doctor", "emt", "first aid", "health", "medical" ]
        }, {
            title: "fab fa-staylinked",
            searchTerms: []
        }, {
            title: "fab fa-steam",
            searchTerms: []
        }, {
            title: "fab fa-steam-symbol",
            searchTerms: []
        }, {
            title: "fas fa-sterling-sign",
            searchTerms: [ "Pound Sign", "currency" ]
        }, {
            title: "fas fa-stethoscope",
            searchTerms: [ "covid-19", "diagnosis", "doctor", "general practitioner", "heart", "hospital", "infirmary", "medicine", "office", "outpatient", "stethoscope" ]
        }, {
            title: "fab fa-sticker-mule",
            searchTerms: []
        }, {
            title: "fas fa-stop",
            searchTerms: [ "block", "box", "square", "stop", "stop button" ]
        }, {
            title: "fas fa-stopwatch",
            searchTerms: [ "clock", "reminder", "stopwatch", "time" ]
        }, {
            title: "fas fa-stopwatch-20",
            searchTerms: [ "ABCs", "countdown", "covid-19", "happy birthday", "i will survive", "reminder", "seconds", "time", "timer" ]
        }, {
            title: "fas fa-store",
            searchTerms: [ "bodega", "building", "buy", "market", "purchase", "shopping", "store" ]
        }, {
            title: "fas fa-store-slash",
            searchTerms: [ "building", "buy", "closed", "covid-19", "purchase", "shopping" ]
        }, {
            title: "fab fa-strava",
            searchTerms: []
        }, {
            title: "fas fa-street-view",
            searchTerms: [ "directions", "location", "map", "navigation" ]
        }, {
            title: "fas fa-strikethrough",
            searchTerms: [ "cancel", "edit", "font", "format", "text", "type" ]
        }, {
            title: "fab fa-stripe",
            searchTerms: []
        }, {
            title: "fab fa-stripe-s",
            searchTerms: []
        }, {
            title: "fas fa-stroopwafel",
            searchTerms: [ "caramel", "cookie", "dessert", "sweets", "waffle" ]
        }, {
            title: "fab fa-stubber",
            searchTerms: []
        }, {
            title: "fab fa-studiovinari",
            searchTerms: []
        }, {
            title: "fab fa-stumbleupon",
            searchTerms: []
        }, {
            title: "fab fa-stumbleupon-circle",
            searchTerms: []
        }, {
            title: "fas fa-subscript",
            searchTerms: [ "edit", "font", "format", "text", "type" ]
        }, {
            title: "fas fa-suitcase",
            searchTerms: [ "baggage", "luggage", "move", "packing", "suitcase", "travel", "trip" ]
        }, {
            title: "fas fa-suitcase-medical",
            searchTerms: [ "first aid", "firstaid", "health", "help", "medical", "supply", "support" ]
        }, {
            title: "fas fa-suitcase-rolling",
            searchTerms: [ "baggage", "luggage", "move", "suitcase", "travel", "trip" ]
        }, {
            title: "fas fa-sun",
            searchTerms: [ "bright", "brighten", "contrast", "day", "lighter", "rays", "sol", "solar", "star", "sun", "sunny", "weather" ]
        }, {
            title: "far fa-sun",
            searchTerms: [ "bright", "brighten", "contrast", "day", "lighter", "rays", "sol", "solar", "star", "sun", "sunny", "weather" ]
        }, {
            title: "fas fa-sun-plant-wilt",
            searchTerms: [ "arid", "droop", "drought" ]
        }, {
            title: "fab fa-superpowers",
            searchTerms: []
        }, {
            title: "fas fa-superscript",
            searchTerms: [ "edit", "exponential", "font", "format", "text", "type" ]
        }, {
            title: "fab fa-supple",
            searchTerms: []
        }, {
            title: "fab fa-suse",
            searchTerms: [ "linux", "operating system", "os" ]
        }, {
            title: "fas fa-swatchbook",
            searchTerms: [ "Pantone", "color", "design", "hue", "palette" ]
        }, {
            title: "fab fa-swift",
            searchTerms: []
        }, {
            title: "fab fa-symfony",
            searchTerms: []
        }, {
            title: "fas fa-synagogue",
            searchTerms: [ "Jew", "Jewish", "building", "jewish", "judaism", "religion", "star of david", "synagogue", "temple" ]
        }, {
            title: "fas fa-syringe",
            searchTerms: [ "covid-19", "doctor", "immunizations", "medical", "medicine", "needle", "shot", "sick", "syringe", "vaccinate", "vaccine" ]
        }, {
            title: "fas fa-t",
            searchTerms: [ "Latin Capital Letter T", "Latin Small Letter T", "letter" ]
        }, {
            title: "fas fa-table",
            searchTerms: [ "data", "excel", "spreadsheet" ]
        }, {
            title: "fas fa-table-cells",
            searchTerms: [ "blocks", "boxes", "grid", "squares" ]
        }, {
            title: "fas fa-table-cells-large",
            searchTerms: [ "blocks", "boxes", "grid", "squares" ]
        }, {
            title: "fas fa-table-columns",
            searchTerms: [ "browser", "dashboard", "organize", "panes", "split" ]
        }, {
            title: "fas fa-table-list",
            searchTerms: [ "checklist", "completed", "done", "finished", "ol", "todo", "ul" ]
        }, {
            title: "fas fa-table-tennis-paddle-ball",
            searchTerms: [ "ball", "bat", "game", "paddle", "ping pong", "table tennis" ]
        }, {
            title: "fas fa-tablet",
            searchTerms: [ "device", "kindle", "screen" ]
        }, {
            title: "fas fa-tablet-button",
            searchTerms: [ "apple", "device", "ipad", "kindle", "screen" ]
        }, {
            title: "fas fa-tablet-screen-button",
            searchTerms: [ "apple", "device", "ipad", "kindle", "screen" ]
        }, {
            title: "fas fa-tablets",
            searchTerms: [ "drugs", "medicine", "pills", "prescription" ]
        }, {
            title: "fas fa-tachograph-digital",
            searchTerms: [ "data", "distance", "speed", "tachometer" ]
        }, {
            title: "fas fa-tag",
            searchTerms: [ "discount", "labe", "label", "price", "shopping" ]
        }, {
            title: "fas fa-tags",
            searchTerms: [ "discount", "label", "price", "shopping" ]
        }, {
            title: "fas fa-tape",
            searchTerms: [ "design", "package", "sticky" ]
        }, {
            title: "fas fa-tarp",
            searchTerms: [ "protection", "tarp", "tent", "waterproof" ]
        }, {
            title: "fas fa-tarp-droplet",
            searchTerms: [ "protection", "tarp", "tent", "waterproof" ]
        }, {
            title: "fas fa-taxi",
            searchTerms: [ "cab", "cabbie", "car", "car service", "lyft", "machine", "oncoming", "oncoming taxi", "taxi", "transportation", "travel", "uber", "vehicle" ]
        }, {
            title: "fab fa-teamspeak",
            searchTerms: []
        }, {
            title: "fas fa-teeth",
            searchTerms: [ "bite", "dental", "dentist", "gums", "mouth", "smile", "tooth" ]
        }, {
            title: "fas fa-teeth-open",
            searchTerms: [ "dental", "dentist", "gums bite", "mouth", "smile", "tooth" ]
        }, {
            title: "fab fa-telegram",
            searchTerms: []
        }, {
            title: "fas fa-temperature-arrow-down",
            searchTerms: [ "air conditioner", "cold", "heater", "mercury", "thermometer", "winter" ]
        }, {
            title: "fas fa-temperature-arrow-up",
            searchTerms: [ "air conditioner", "cold", "heater", "mercury", "thermometer", "winter" ]
        }, {
            title: "fas fa-temperature-empty",
            searchTerms: [ "cold", "mercury", "status", "temperature" ]
        }, {
            title: "fas fa-temperature-full",
            searchTerms: [ "fever", "hot", "mercury", "status", "temperature" ]
        }, {
            title: "fas fa-temperature-half",
            searchTerms: [ "mercury", "status", "temperature", "thermometer", "weather" ]
        }, {
            title: "fas fa-temperature-high",
            searchTerms: [ "cook", "covid-19", "mercury", "summer", "thermometer", "warm" ]
        }, {
            title: "fas fa-temperature-low",
            searchTerms: [ "cold", "cool", "covid-19", "mercury", "thermometer", "winter" ]
        }, {
            title: "fas fa-temperature-quarter",
            searchTerms: [ "mercury", "status", "temperature" ]
        }, {
            title: "fas fa-temperature-three-quarters",
            searchTerms: [ "mercury", "status", "temperature" ]
        }, {
            title: "fab fa-tencent-weibo",
            searchTerms: []
        }, {
            title: "fas fa-tenge-sign",
            searchTerms: [ "Tenge Sign", "currency" ]
        }, {
            title: "fas fa-tent",
            searchTerms: [ "bivouac", "campground", "refugee", "shelter", "tent" ]
        }, {
            title: "fas fa-tent-arrow-down-to-line",
            searchTerms: [ "permanent", "refugee", "shelter" ]
        }, {
            title: "fas fa-tent-arrow-left-right",
            searchTerms: [ "refugee", "shelter", "transition" ]
        }, {
            title: "fas fa-tent-arrow-turn-left",
            searchTerms: [ "refugee", "shelter", "temporary" ]
        }, {
            title: "fas fa-tent-arrows-down",
            searchTerms: [ "refugee", "shelter", "spontaneous" ]
        }, {
            title: "fas fa-tents",
            searchTerms: [ "bivouac", "campground", "refugee", "shelter", "tent" ]
        }, {
            title: "fas fa-terminal",
            searchTerms: [ "code", "coding", "command", "console", "development", "prompt", "terminal" ]
        }, {
            title: "fas fa-text-height",
            searchTerms: [ "edit", "font", "format", "text", "type" ]
        }, {
            title: "fas fa-text-slash",
            searchTerms: [ "cancel", "font", "format", "remove", "style", "text" ]
        }, {
            title: "fas fa-text-width",
            searchTerms: [ "edit", "font", "format", "text", "type" ]
        }, {
            title: "fab fa-the-red-yeti",
            searchTerms: []
        }, {
            title: "fab fa-themeco",
            searchTerms: []
        }, {
            title: "fab fa-themeisle",
            searchTerms: []
        }, {
            title: "fas fa-thermometer",
            searchTerms: [ "covid-19", "mercury", "status", "temperature" ]
        }, {
            title: "fab fa-think-peaks",
            searchTerms: []
        }, {
            title: "fas fa-thumbs-down",
            searchTerms: [ "-1", "disagree", "disapprove", "dislike", "down", "hand", "social", "thumb", "thumbs down", "thumbs-o-down" ]
        }, {
            title: "far fa-thumbs-down",
            searchTerms: [ "-1", "disagree", "disapprove", "dislike", "down", "hand", "social", "thumb", "thumbs down", "thumbs-o-down" ]
        }, {
            title: "fas fa-thumbs-up",
            searchTerms: [ "+1", "agree", "approve", "favorite", "hand", "like", "ok", "okay", "social", "success", "thumb", "thumbs up", "thumbs-o-up", "up", "yes", "you got it dude" ]
        }, {
            title: "far fa-thumbs-up",
            searchTerms: [ "+1", "agree", "approve", "favorite", "hand", "like", "ok", "okay", "social", "success", "thumb", "thumbs up", "thumbs-o-up", "up", "yes", "you got it dude" ]
        }, {
            title: "fas fa-thumbtack",
            searchTerms: [ "Black Pushpin", "coordinates", "location", "marker", "pin", "pushpin", "thumb-tack" ]
        }, {
            title: "fas fa-ticket",
            searchTerms: [ "admission", "admission tickets", "movie", "pass", "support", "ticket" ]
        }, {
            title: "fas fa-ticket-simple",
            searchTerms: [ "movie", "pass", "support", "ticket" ]
        }, {
            title: "fab fa-tiktok",
            searchTerms: []
        }, {
            title: "fas fa-timeline",
            searchTerms: [ "chronological", "deadline", "history", "linear" ]
        }, {
            title: "fas fa-toggle-off",
            searchTerms: [ "button", "off", "on", "switch" ]
        }, {
            title: "fas fa-toggle-on",
            searchTerms: [ "button", "off", "on", "switch" ]
        }, {
            title: "fas fa-toilet",
            searchTerms: [ "bathroom", "flush", "john", "loo", "pee", "plumbing", "poop", "porcelain", "potty", "restroom", "throne", "toile", "toilet", "washroom", "waste", "wc" ]
        }, {
            title: "fas fa-toilet-paper",
            searchTerms: [ "bathroom", "covid-19", "halloween", "holiday", "lavatory", "paper towels", "prank", "privy", "restroom", "roll", "roll of paper", "toilet", "toilet paper", "wipe" ]
        }, {
            title: "fas fa-toilet-paper-slash",
            searchTerms: [ "bathroom", "covid-19", "halloween", "holiday", "lavatory", "leaves", "prank", "privy", "restroom", "roll", "toilet", "trouble", "ut oh", "wipe" ]
        }, {
            title: "fas fa-toilet-portable",
            searchTerms: [ "outhouse", "toilet" ]
        }, {
            title: "fas fa-toilets-portable",
            searchTerms: [ "outhouse", "toilet" ]
        }, {
            title: "fas fa-toolbox",
            searchTerms: [ "admin", "chest", "container", "fix", "mechanic", "repair", "settings", "tool", "toolbox", "tools" ]
        }, {
            title: "fas fa-tooth",
            searchTerms: [ "bicuspid", "dental", "dentist", "molar", "mouth", "teeth", "tooth" ]
        }, {
            title: "fas fa-torii-gate",
            searchTerms: [ "building", "religion", "shinto", "shinto shrine", "shintoism", "shrine" ]
        }, {
            title: "fas fa-tornado",
            searchTerms: [ "cloud", "cyclone", "dorothy", "landspout", "tornado", "toto", "twister", "vortext", "waterspout", "weather", "whirlwind" ]
        }, {
            title: "fas fa-tower-broadcast",
            searchTerms: [ "airwaves", "antenna", "communication", "emergency", "radio", "reception", "waves" ]
        }, {
            title: "fas fa-tower-cell",
            searchTerms: [ "airwaves", "antenna", "communication", "radio", "reception", "waves" ]
        }, {
            title: "fas fa-tower-observation",
            searchTerms: [ "fire tower", "view" ]
        }, {
            title: "fas fa-tractor",
            searchTerms: [ "agriculture", "farm", "tractor", "vehicle" ]
        }, {
            title: "fab fa-trade-federation",
            searchTerms: []
        }, {
            title: "fas fa-trademark",
            searchTerms: [ "copyright", "mark", "register", "symbol", "tm", "trade mark", "trademark" ]
        }, {
            title: "fas fa-traffic-light",
            searchTerms: [ "direction", "light", "road", "signal", "traffic", "travel", "vertical traffic light" ]
        }, {
            title: "fas fa-trailer",
            searchTerms: [ "carry", "haul", "moving", "travel" ]
        }, {
            title: "fas fa-train",
            searchTerms: [ "bullet", "commute", "locomotive", "railway", "subway", "train" ]
        }, {
            title: "fas fa-train-subway",
            searchTerms: [ "machine", "railway", "train", "transportation", "vehicle" ]
        }, {
            title: "fas fa-train-tram",
            searchTerms: [ "crossing", "machine", "mountains", "seasonal", "tram", "transportation", "trolleybus" ]
        }, {
            title: "fas fa-transgender",
            searchTerms: [ "female", "gender", "intersex", "male", "transgender", "transgender symbol" ]
        }, {
            title: "fas fa-trash",
            searchTerms: [ "delete", "garbage", "hide", "remove" ]
        }, {
            title: "fas fa-trash-arrow-up",
            searchTerms: [ "back", "control z", "delete", "garbage", "hide", "oops", "remove", "undo" ]
        }, {
            title: "fas fa-trash-can",
            searchTerms: [ "delete", "garbage", "hide", "remove", "trash-o" ]
        }, {
            title: "far fa-trash-can",
            searchTerms: [ "delete", "garbage", "hide", "remove", "trash-o" ]
        }, {
            title: "fas fa-trash-can-arrow-up",
            searchTerms: [ "back", "control z", "delete", "garbage", "hide", "oops", "remove", "undo" ]
        }, {
            title: "fas fa-tree",
            searchTerms: [ "bark", "evergreen tree", "fall", "flora", "forest", "nature", "plant", "seasonal", "tree" ]
        }, {
            title: "fas fa-tree-city",
            searchTerms: [ "building", "city", "urban" ]
        }, {
            title: "fab fa-trello",
            searchTerms: [ "atlassian" ]
        }, {
            title: "fas fa-triangle-exclamation",
            searchTerms: [ "alert", "danger", "error", "important", "notice", "notification", "notify", "problem", "warnin", "warning" ]
        }, {
            title: "fas fa-trophy",
            searchTerms: [ "achievement", "award", "cup", "game", "prize", "trophy", "winner" ]
        }, {
            title: "fas fa-trowel",
            searchTerms: [ "build", "construction", "tool" ]
        }, {
            title: "fas fa-trowel-bricks",
            searchTerms: [ "build", "construction", "reconstruction", "tool" ]
        }, {
            title: "fas fa-truck",
            searchTerms: [ "Black Truck", "cargo", "delivery", "delivery truck", "shipping", "truck", "vehicle" ]
        }, {
            title: "fas fa-truck-arrow-right",
            searchTerms: [ "access", "fast", "shipping", "transport" ]
        }, {
            title: "fas fa-truck-droplet",
            searchTerms: [ "thirst", "truck", "water", "water supply" ]
        }, {
            title: "fas fa-truck-fast",
            searchTerms: [ "express", "fedex", "mail", "overnight", "package", "ups" ]
        }, {
            title: "fas fa-truck-field",
            searchTerms: [ "supplies", "truck" ]
        }, {
            title: "fas fa-truck-field-un",
            searchTerms: [ "supplies", "truck", "united nations" ]
        }, {
            title: "fas fa-truck-front",
            searchTerms: [ "shuttle", "truck", "van" ]
        }, {
            title: "fas fa-truck-medical",
            searchTerms: [ "ambulance", "clinic", "covid-19", "emergency", "emt", "er", "help", "hospital", "mobile", "support", "vehicle" ]
        }, {
            title: "fas fa-truck-monster",
            searchTerms: [ "offroad", "vehicle", "wheel" ]
        }, {
            title: "fas fa-truck-moving",
            searchTerms: [ "cargo", "inventory", "rental", "vehicle" ]
        }, {
            title: "fas fa-truck-pickup",
            searchTerms: [ "cargo", "pick-up", "pickup", "pickup truck", "truck", "vehicle" ]
        }, {
            title: "fas fa-truck-plane",
            searchTerms: [ "airplane", "plane", "transportation", "truck", "vehicle" ]
        }, {
            title: "fas fa-truck-ramp-box",
            searchTerms: [ "box", "cargo", "delivery", "inventory", "moving", "rental", "vehicle" ]
        }, {
            title: "fas fa-tty",
            searchTerms: [ "communication", "deaf", "telephone", "teletypewriter", "text" ]
        }, {
            title: "fab fa-tumblr",
            searchTerms: []
        }, {
            title: "fas fa-turkish-lira-sign",
            searchTerms: [ "Turkish Lira Sign", "currency" ]
        }, {
            title: "fas fa-turn-down",
            searchTerms: [ "arrow", "down", "level-down", "right arrow curving down" ]
        }, {
            title: "fas fa-turn-up",
            searchTerms: [ "arrow", "level-up", "right arrow curving up" ]
        }, {
            title: "fas fa-tv",
            searchTerms: [ "computer", "display", "monitor", "television" ]
        }, {
            title: "fab fa-twitch",
            searchTerms: []
        }, {
            title: "fab fa-twitter",
            searchTerms: [ "social network", "tweet" ]
        }, {
            title: "fab fa-typo3",
            searchTerms: []
        }, {
            title: "fas fa-u",
            searchTerms: [ "Latin Capital Letter U", "Latin Small Letter U", "letter" ]
        }, {
            title: "fab fa-uber",
            searchTerms: []
        }, {
            title: "fab fa-ubuntu",
            searchTerms: [ "linux", "operating system", "os" ]
        }, {
            title: "fab fa-uikit",
            searchTerms: []
        }, {
            title: "fab fa-umbraco",
            searchTerms: []
        }, {
            title: "fas fa-umbrella",
            searchTerms: [ "protection", "rain", "storm", "wet" ]
        }, {
            title: "fas fa-umbrella-beach",
            searchTerms: [ "beach", "beach with umbrella", "protection", "recreation", "sand", "shade", "summer", "sun", "umbrella" ]
        }, {
            title: "fab fa-uncharted",
            searchTerms: []
        }, {
            title: "fas fa-underline",
            searchTerms: [ "edit", "emphasis", "format", "text", "writing" ]
        }, {
            title: "fab fa-uniregistry",
            searchTerms: []
        }, {
            title: "fab fa-unity",
            searchTerms: []
        }, {
            title: "fas fa-universal-access",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-unlock",
            searchTerms: [ "admin", "lock", "open", "password", "private", "protect", "unlock", "unlocked" ]
        }, {
            title: "fas fa-unlock-keyhole",
            searchTerms: [ "admin", "lock", "password", "private", "protect" ]
        }, {
            title: "fab fa-unsplash",
            searchTerms: []
        }, {
            title: "fab fa-untappd",
            searchTerms: []
        }, {
            title: "fas fa-up-down",
            searchTerms: [ "Up Down Black Arrow", "arrow", "arrows-v", "expand", "portrait", "resize", "tall", "up-down arrow", "vertical" ]
        }, {
            title: "fas fa-up-down-left-right",
            searchTerms: [ "arrow", "arrows", "bigger", "enlarge", "expand", "fullscreen", "move", "position", "reorder", "resize" ]
        }, {
            title: "fas fa-up-long",
            searchTerms: [ "long-arrow-up", "upload" ]
        }, {
            title: "fas fa-up-right-and-down-left-from-center",
            searchTerms: [ "arrows", "bigger", "enlarge", "fullscreen", "resize" ]
        }, {
            title: "fas fa-up-right-from-square",
            searchTerms: [ "external-link", "new", "open", "share" ]
        }, {
            title: "fas fa-upload",
            searchTerms: [ "hard drive", "import", "publish" ]
        }, {
            title: "fab fa-ups",
            searchTerms: [ "United Parcel Service", "package", "shipping" ]
        }, {
            title: "fab fa-usb",
            searchTerms: []
        }, {
            title: "fas fa-user",
            searchTerms: [ "adult", "bust", "bust in silhouette", "gender-neutral", "person", "profile", "silhouette", "unspecified gender", "users-people" ]
        }, {
            title: "far fa-user",
            searchTerms: [ "adult", "bust", "bust in silhouette", "gender-neutral", "person", "profile", "silhouette", "unspecified gender", "users-people" ]
        }, {
            title: "fas fa-user-astronaut",
            searchTerms: [ "avatar", "clothing", "cosmonaut", "nasa", "space", "suit" ]
        }, {
            title: "fas fa-user-check",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-clock",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-doctor",
            searchTerms: [ "covid-19", "health", "job", "medical", "nurse", "occupation", "physician", "profile", "surgeon", "worker" ]
        }, {
            title: "fas fa-user-gear",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-graduate",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-group",
            searchTerms: [ "bust", "busts in silhouette", "silhouette", "users-people" ]
        }, {
            title: "fas fa-user-injured",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-large",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-large-slash",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-lock",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-minus",
            searchTerms: [ "delete", "negative", "remove" ]
        }, {
            title: "fas fa-user-ninja",
            searchTerms: [ "assassin", "avatar", "dangerous", "deadly", "fighter", "hidden", "ninja", "sneaky", "stealth" ]
        }, {
            title: "fas fa-user-nurse",
            searchTerms: [ "covid-19", "doctor", "health", "md", "medical", "midwife", "physician", "practitioner", "surgeon", "worker" ]
        }, {
            title: "fas fa-user-pen",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-plus",
            searchTerms: [ "add", "avatar", "positive", "sign up", "signup", "team" ]
        }, {
            title: "fas fa-user-secret",
            searchTerms: [ "detective", "sleuth", "spy", "users-people" ]
        }, {
            title: "fas fa-user-shield",
            searchTerms: [ "protect", "safety" ]
        }, {
            title: "fas fa-user-slash",
            searchTerms: [ "ban", "delete", "remove" ]
        }, {
            title: "fas fa-user-tag",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-user-tie",
            searchTerms: [ "avatar", "business", "clothing", "formal", "professional", "suit" ]
        }, {
            title: "fas fa-user-xmark",
            searchTerms: [ "archive", "delete", "remove", "x" ]
        }, {
            title: "fas fa-users",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-users-between-lines",
            searchTerms: [ "covered", "group", "people" ]
        }, {
            title: "fas fa-users-gear",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-users-line",
            searchTerms: [ "group", "need", "people" ]
        }, {
            title: "fas fa-users-rays",
            searchTerms: [ "affected", "focused", "group", "people" ]
        }, {
            title: "fas fa-users-rectangle",
            searchTerms: [ "focus", "group", "people", "reached" ]
        }, {
            title: "fas fa-users-slash",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-users-viewfinder",
            searchTerms: [ "focus", "group", "people", "targeted" ]
        }, {
            title: "fab fa-usps",
            searchTerms: [ "american", "package", "shipping", "usa" ]
        }, {
            title: "fab fa-ussunnah",
            searchTerms: []
        }, {
            title: "fas fa-utensils",
            searchTerms: [ "cooking", "cutlery", "dining", "dinner", "eat", "food", "fork", "fork and knife", "knife", "restaurant" ]
        }, {
            title: "fas fa-v",
            searchTerms: [ "Latin Capital Letter V", "Latin Small Letter V", "letter" ]
        }, {
            title: "fab fa-vaadin",
            searchTerms: []
        }, {
            title: "fas fa-van-shuttle",
            searchTerms: [ "airport", "bus", "machine", "minibus", "public-transportation", "transportation", "travel", "vehicle" ]
        }, {
            title: "fas fa-vault",
            searchTerms: [ "bank", "important", "lock", "money", "safe" ]
        }, {
            title: "fas fa-vector-square",
            searchTerms: [ "anchors", "lines", "object", "render", "shape" ]
        }, {
            title: "fas fa-venus",
            searchTerms: [ "female", "female sign", "gender", "woman" ]
        }, {
            title: "fas fa-venus-double",
            searchTerms: [ "Doubled Female Sign", "female", "gender", "lesbian" ]
        }, {
            title: "fas fa-venus-mars",
            searchTerms: [ "Interlocked Female and Male Sign", "female", "gender", "heterosexual", "male" ]
        }, {
            title: "fas fa-vest",
            searchTerms: [ "biker", "fashion", "style" ]
        }, {
            title: "fas fa-vest-patches",
            searchTerms: [ "biker", "fashion", "style" ]
        }, {
            title: "fab fa-viacoin",
            searchTerms: []
        }, {
            title: "fab fa-viadeo",
            searchTerms: []
        }, {
            title: "fas fa-vial",
            searchTerms: [ "ampule", "chemist", "chemistry", "experiment", "lab", "sample", "science", "test", "test tube" ]
        }, {
            title: "fas fa-vial-circle-check",
            searchTerms: [ "ampule", "chemist", "chemistry", "not affected", "ok", "okay", "success", "test tube", "tube", "vaccine" ]
        }, {
            title: "fas fa-vial-virus",
            searchTerms: [ "ampule", "coronavirus", "covid-19", "flue", "infection", "lab", "laboratory", "pandemic", "test", "test tube", "vaccine" ]
        }, {
            title: "fas fa-vials",
            searchTerms: [ "ampule", "experiment", "lab", "sample", "science", "test", "test tube" ]
        }, {
            title: "fab fa-viber",
            searchTerms: []
        }, {
            title: "fas fa-video",
            searchTerms: [ "camera", "film", "movie", "record", "video-camera" ]
        }, {
            title: "fas fa-video-slash",
            searchTerms: [ "add", "create", "film", "new", "positive", "record", "video" ]
        }, {
            title: "fas fa-vihara",
            searchTerms: [ "buddhism", "buddhist", "building", "monastery" ]
        }, {
            title: "fab fa-vimeo",
            searchTerms: []
        }, {
            title: "fab fa-vimeo-v",
            searchTerms: [ "vimeo" ]
        }, {
            title: "fab fa-vine",
            searchTerms: []
        }, {
            title: "fas fa-virus",
            searchTerms: [ "bug", "coronavirus", "covid-19", "flu", "health", "infection", "pandemic", "sick", "vaccine", "viral" ]
        }, {
            title: "fas fa-virus-covid",
            searchTerms: [ "bug", "covid-19", "flu", "health", "infection", "pandemic", "vaccine", "viral", "virus" ]
        }, {
            title: "fas fa-virus-covid-slash",
            searchTerms: [ "bug", "covid-19", "flu", "health", "infection", "pandemic", "vaccine", "viral", "virus" ]
        }, {
            title: "fas fa-virus-slash",
            searchTerms: [ "bug", "coronavirus", "covid-19", "cure", "eliminate", "flu", "health", "infection", "pandemic", "sick", "vaccine", "viral" ]
        }, {
            title: "fas fa-viruses",
            searchTerms: [ "bugs", "coronavirus", "covid-19", "flu", "health", "infection", "multiply", "pandemic", "sick", "spread", "vaccine", "viral" ]
        }, {
            title: "fab fa-vk",
            searchTerms: []
        }, {
            title: "fab fa-vnv",
            searchTerms: []
        }, {
            title: "fas fa-voicemail",
            searchTerms: [ "answer", "inbox", "message", "phone" ]
        }, {
            title: "fas fa-volcano",
            searchTerms: [ "caldera", "eruption", "lava", "magma", "mountain", "smoke", "volcano" ]
        }, {
            title: "fas fa-volleyball",
            searchTerms: [ "ball", "beach", "game", "olympics", "sport", "volleyball" ]
        }, {
            title: "fas fa-volume-high",
            searchTerms: [ "audio", "higher", "loud", "louder", "music", "sound", "speaker", "speaker high volume" ]
        }, {
            title: "fas fa-volume-low",
            searchTerms: [ "audio", "lower", "music", "quieter", "soft", "sound", "speaker", "speaker low volume" ]
        }, {
            title: "fas fa-volume-off",
            searchTerms: [ "audio", "ban", "music", "mute", "quiet", "silent", "sound" ]
        }, {
            title: "fas fa-volume-xmark",
            searchTerms: [ "audio", "music", "quiet", "sound", "speaker" ]
        }, {
            title: "fas fa-vr-cardboard",
            searchTerms: [ "3d", "augment", "google", "reality", "virtual" ]
        }, {
            title: "fab fa-vuejs",
            searchTerms: []
        }, {
            title: "fas fa-w",
            searchTerms: [ "Latin Capital Letter W", "Latin Small Letter W", "letter" ]
        }, {
            title: "fas fa-walkie-talkie",
            searchTerms: [ "communication", "copy", "intercom", "over", "portable", "radio", "two way radio" ]
        }, {
            title: "fas fa-wallet",
            searchTerms: [ "billfold", "cash", "currency", "money" ]
        }, {
            title: "fas fa-wand-magic",
            searchTerms: [ "autocomplete", "automatic", "mage", "magic", "spell", "wand", "witch", "wizard" ]
        }, {
            title: "fas fa-wand-magic-sparkles",
            searchTerms: [ "auto", "magic", "magic wand", "trick", "witch", "wizard" ]
        }, {
            title: "fas fa-wand-sparkles",
            searchTerms: [ "autocomplete", "automatic", "fantasy", "halloween", "holiday", "magic", "weapon", "witch", "wizard" ]
        }, {
            title: "fas fa-warehouse",
            searchTerms: [ "building", "capacity", "garage", "inventory", "storage" ]
        }, {
            title: "fab fa-watchman-monitoring",
            searchTerms: []
        }, {
            title: "fas fa-water",
            searchTerms: [ "lake", "liquid", "ocean", "sea", "swim", "wet" ]
        }, {
            title: "fas fa-water-ladder",
            searchTerms: [ "ladder", "recreation", "swim", "water" ]
        }, {
            title: "fas fa-wave-square",
            searchTerms: [ "frequency", "pulse", "signal" ]
        }, {
            title: "fab fa-waze",
            searchTerms: []
        }, {
            title: "fab fa-weebly",
            searchTerms: []
        }, {
            title: "fab fa-weibo",
            searchTerms: []
        }, {
            title: "fas fa-weight-hanging",
            searchTerms: [ "anvil", "heavy", "measurement" ]
        }, {
            title: "fas fa-weight-scale",
            searchTerms: [ "health", "measurement", "scale", "weight" ]
        }, {
            title: "fab fa-weixin",
            searchTerms: []
        }, {
            title: "fab fa-whatsapp",
            searchTerms: []
        }, {
            title: "fas fa-wheat-awn",
            searchTerms: [ "agriculture", "autumn", "fall", "farming", "grain" ]
        }, {
            title: "fas fa-wheat-awn-circle-exclamation",
            searchTerms: [ "affected", "famine", "food", "gluten", "hunger", "starve", "straw" ]
        }, {
            title: "fas fa-wheelchair",
            searchTerms: [ "users-people" ]
        }, {
            title: "fas fa-wheelchair-move",
            searchTerms: [ "access", "handicap", "impairment", "physical", "wheelchair symbol" ]
        }, {
            title: "fas fa-whiskey-glass",
            searchTerms: [ "alcohol", "bar", "beverage", "bourbon", "drink", "glass", "liquor", "neat", "rye", "scotch", "shot", "tumbler", "tumbler glass", "whisky" ]
        }, {
            title: "fab fa-whmcs",
            searchTerms: []
        }, {
            title: "fas fa-wifi",
            searchTerms: [ "connection", "hotspot", "internet", "network", "wireless" ]
        }, {
            title: "fab fa-wikipedia-w",
            searchTerms: []
        }, {
            title: "fas fa-wind",
            searchTerms: [ "air", "blow", "breeze", "fall", "seasonal", "weather" ]
        }, {
            title: "fas fa-window-maximize",
            searchTerms: [ "Maximize", "browser", "computer", "development", "expand" ]
        }, {
            title: "far fa-window-maximize",
            searchTerms: [ "Maximize", "browser", "computer", "development", "expand" ]
        }, {
            title: "fas fa-window-minimize",
            searchTerms: [ "Minimize", "browser", "collapse", "computer", "development" ]
        }, {
            title: "far fa-window-minimize",
            searchTerms: [ "Minimize", "browser", "collapse", "computer", "development" ]
        }, {
            title: "fas fa-window-restore",
            searchTerms: [ "browser", "computer", "development" ]
        }, {
            title: "far fa-window-restore",
            searchTerms: [ "browser", "computer", "development" ]
        }, {
            title: "fab fa-windows",
            searchTerms: [ "microsoft", "operating system", "os" ]
        }, {
            title: "fas fa-wine-bottle",
            searchTerms: [ "alcohol", "beverage", "cabernet", "drink", "glass", "grapes", "merlot", "sauvignon" ]
        }, {
            title: "fas fa-wine-glass",
            searchTerms: [ "alcohol", "bar", "beverage", "cabernet", "drink", "glass", "grapes", "merlot", "sauvignon", "wine", "wine glass" ]
        }, {
            title: "fas fa-wine-glass-empty",
            searchTerms: [ "alcohol", "beverage", "cabernet", "drink", "grapes", "merlot", "sauvignon" ]
        }, {
            title: "fab fa-wirsindhandwerk",
            searchTerms: []
        }, {
            title: "fab fa-wix",
            searchTerms: []
        }, {
            title: "fab fa-wizards-of-the-coast",
            searchTerms: [ "Dungeons & Dragons", "d&d", "dnd", "fantasy", "game", "gaming", "tabletop" ]
        }, {
            title: "fab fa-wodu",
            searchTerms: []
        }, {
            title: "fab fa-wolf-pack-battalion",
            searchTerms: []
        }, {
            title: "fas fa-won-sign",
            searchTerms: [ "Won Sign", "currency" ]
        }, {
            title: "fab fa-wordpress",
            searchTerms: []
        }, {
            title: "fab fa-wordpress-simple",
            searchTerms: []
        }, {
            title: "fas fa-worm",
            searchTerms: [ "dirt", "garden", "worm", "wriggle" ]
        }, {
            title: "fab fa-wpbeginner",
            searchTerms: []
        }, {
            title: "fab fa-wpexplorer",
            searchTerms: []
        }, {
            title: "fab fa-wpforms",
            searchTerms: []
        }, {
            title: "fab fa-wpressr",
            searchTerms: [ "rendact" ]
        }, {
            title: "fas fa-wrench",
            searchTerms: [ "construction", "fix", "mechanic", "plumbing", "settings", "spanner", "tool", "update", "wrench" ]
        }, {
            title: "fas fa-x",
            searchTerms: [ "Latin Capital Letter X", "Latin Small Letter X", "letter" ]
        }, {
            title: "fas fa-x-ray",
            searchTerms: [ "health", "medical", "radiological images", "radiology", "skeleton" ]
        }, {
            title: "fab fa-xbox",
            searchTerms: []
        }, {
            title: "fab fa-xing",
            searchTerms: []
        }, {
            title: "fas fa-xmark",
            searchTerms: [ "Cancellation X", "Multiplication Sign", "Multiplication X", "cancel", "close", "cross", "cross mark", "error", "exit", "incorrect", "mark", "multiplication", "multiply", "notice", "notification", "notify", "problem", "sign", "wrong", "x", "×" ]
        }, {
            title: "fas fa-xmarks-lines",
            searchTerms: [ "barricade", "barrier", "fence", "poison", "roadblock" ]
        }, {
            title: "fas fa-y",
            searchTerms: [ "Latin Capital Letter Y", "Latin Small Letter Y", "letter", "yay", "yes" ]
        }, {
            title: "fab fa-y-combinator",
            searchTerms: []
        }, {
            title: "fab fa-yahoo",
            searchTerms: []
        }, {
            title: "fab fa-yammer",
            searchTerms: []
        }, {
            title: "fab fa-yandex",
            searchTerms: []
        }, {
            title: "fab fa-yandex-international",
            searchTerms: []
        }, {
            title: "fab fa-yarn",
            searchTerms: []
        }, {
            title: "fab fa-yelp",
            searchTerms: []
        }, {
            title: "fas fa-yen-sign",
            searchTerms: [ "Yen Sign", "currency" ]
        }, {
            title: "fas fa-yin-yang",
            searchTerms: [ "daoism", "opposites", "religion", "tao", "taoism", "taoist", "yang", "yin", "yin yang" ]
        }, {
            title: "fab fa-yoast",
            searchTerms: []
        }, {
            title: "fab fa-youtube",
            searchTerms: [ "film", "video", "youtube-play", "youtube-square" ]
        }, {
            title: "fas fa-z",
            searchTerms: [ "Latin Capital Letter Z", "Latin Small Letter Z", "letter" ]
        }, {
            title: "fab fa-zhihu",
            searchTerms: []
        } ]
    });
});