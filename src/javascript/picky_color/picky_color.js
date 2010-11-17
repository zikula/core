/*
  PickyColor v0.1 (picky_color.js)
  Manages the dynamic addition, deletion, and serialization of form fields.
  http://code.google.com/p/picky-color

  Depends on prototype.js >= 1.5.1.1 and Scriptaculous >= 1.7.0.

  Copyright (c) 2007 Brandon Arbini/Sevenwire
  http://www.opensource.org/licenses/mit-license.php
*/

var PickyColor = Class.create()

PickyColor.prototype = {
  initialize: function(options) {
    // Small extension for an ugly IE 6 hack
    Prototype.Browser.IE6 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5))==6

    // Set the options
    this.options = Object.extend({
      color: '#AAAAAA',
      titleText: 'Choose a color',
      closeText: 'Close',
      colorPickerClass: 'color-picker',
      colorPickerHTML: '<div class="#{colorPickerClass}"><div class="color-picker-title-bar"><span class="color-picker-title">#{titleText}</span></div><div class="color-picker-color"></div><div class="color-picker-hex"></div><div class="color-picker-closer">#{closeText}</div><div class="color-picker-sv"><div class="color-picker-sv-slider"><img src="#{imageBase}sv_slider.png" alt="Saturation and value slider"></div></div><div class="color-picker-h"><div class="color-picker-h-slider"><img src="#{imageBase}h_slider.png" alt="Hue Slider"></div></div></div>',
      imageBase: 'javascript/picky_color/images/',
      showColor: true,
      showHex: true,
      showTitle: false,
      closable: true,
      draggable: true,
      startHidden: true,
      x: null,
      y: null,
      zIndex: 100
    }, options || {})

    // Actions based on options
    this.hex = this.options.color
    if (this.options.field) this.attachToField()
    if (this.options.colorWell) this.attachToColorWell()

    this.createColorPicker()
  },


  // CALLBACK EVENTS

  event: function(eventName, e) {
    if (this.options[eventName]) this.options[eventName](this, e)
  },


  // COLOR PICKER

  createColorPicker: function() {
    this.event('beforeCreate')

    new Insertion.Top(document.body, (new Template(this.options.colorPickerHTML)).evaluate({colorPickerClass:this.options.colorPickerClass, titleText:this.options.titleText, closeText:this.options.closeText, imageBase:this.options.imageBase}))
    this.picker = document.body.down("."+this.options.colorPickerClass)
    this.titleBarElement = $(this.picker).down('.color-picker-title-bar')
    this.titleElement = $(this.picker).down('.color-picker-title')
    this.closerElement = $(this.picker).down('.color-picker-closer')
    this.colorElement = $(this.picker).down('.color-picker-color')
    this.hexElement = $(this.picker).down('.color-picker-hex')
    this.svElement = $(this.picker).down('.color-picker-sv')
    this.svSliderElement = $(this.svElement).down('.color-picker-sv-slider')
    this.svElementDimensions = $(this.svElement).getDimensions()
    this.hElement = $(this.picker).down('.color-picker-h')
    this.hSliderElement = $(this.hElement).down('.color-picker-h-slider')
    this.hElementDimensions = $(this.hElement).getDimensions()

    if (this.options.startHidden) this.hide()
    this.updatePickerOffsets()
    this.updateHex(this.hex)

    $(this.picker).setStyle({
      position: "absolute",
      top: (this.options.y || 100)+'px',
      left: (this.options.x || 100)+'px',
      backgroundImage: "url("+this.options.imageBase+"body_bg.png)",
      padding: "15px",
      zIndex:this.options.zIndex
    })
    $(this.titleBarElement).setStyle({
      backgroundImage: "url("+this.options.imageBase+"head_bg.png)"
    })
    $(this.svElement).setStyle({
      backgroundImage: "url("+this.options.imageBase+"sv.png)"
    })
    $(this.hElement).setStyle({
      backgroundImage: "url("+this.options.imageBase+"h.png)"
    })

    // UGLY IE 6 HACK! :(
    if (Prototype.Browser.IE6) {
      $(this.svElement).setStyle({
        backgroundImage: 'none',
        filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+this.options.imageBase+"sv.png', sizingMethod='scale')"
      })
      $(this.hSliderElement).setStyle({
        filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+this.options.imageBase+"h_slider.png', sizingMethod='scale')"
      })
      $(this.hSliderElement).down('img').setStyle({
        display: 'none'
      })
      $(this.svSliderElement).setStyle({
        filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+this.options.imageBase+"sv_slider.png', sizingMethod='scale')"
      })
      $(this.svSliderElement).down('img').setStyle({
        display: 'none'
      })
    }

    if (!this.options.closable) {
      this.closerElement.remove()
    } else {
      Event.observe(this.closerElement, 'click', function() {
        this.hide()
      }.bindAsEventListener(this))
    }
    if (!this.options.showTitle) this.titleElement.remove()
    if (!this.options.showColor) this.colorElement.remove()
    if (!this.options.showHex) this.hexElement.remove()

    var snap = function(xMin,xMax,yMin,yMax,x,y,draggable) {
      x = Math.min(Math.max(xMin, x), xMax - draggable.element.offsetWidth)
      y = Math.min(Math.max(yMin, y), yMax - draggable.element.offsetHeight)

      return [x,y]
    }

    this.svSlider = new Draggable(this.svSliderElement, {
      snap: function(x,y,draggable) {
        return snap(-8,209,-8,209,x,y,draggable)
      },
      onStart: function(draggable) {
        this.updateSV((draggable.currentDelta()[0]+8)/this.svElementDimensions.width, 1-((draggable.currentDelta()[1]+8)/this.svElementDimensions.height))
      }.bind(this),
      change: function(draggable) {
        this.updateSV((draggable.currentDelta()[0]+8)/this.svElementDimensions.width, 1-((draggable.currentDelta()[1]+8)/this.svElementDimensions.height))
      }.bind(this),
      starteffect: null,
      endeffect: null
    })
    Event.observe(this.svElement, 'mousedown', function(e) {
      var clickX = Event.pointerX(e)-this.svElementOffset[0]
      var clickY = Event.pointerY(e)-this.svElementOffset[1]
      this.svSliderElement.setStyle({top: clickY-11+'px', left: clickX-11+'px'})
      this.svSlider.initDrag(e)
      this.svSlider.startDrag(e)
    }.bindAsEventListener(this))

    this.hSlider = new Draggable(this.hSliderElement, {
      constraint:'vertical',
      snap: function(x,y,draggable) {
        return snap(0,0,-5,207,x,y,draggable)
      },
      onStart: function(draggable) {
        this.updateH((draggable.currentDelta()[1]+5)/this.hElementDimensions.height)
      }.bind(this),
      change: function(draggable) {
        this.updateH((draggable.currentDelta()[1]+5)/this.hElementDimensions.height)
      }.bind(this),
      starteffect: null,
      endeffect: null
    })
    Event.observe(this.hElement, 'mousedown', function(e) {
      var clickY = Event.pointerY(e)-this.hElementOffset[1]
      this.hSliderElement.setStyle({top: clickY-8+'px'})
      this.hSlider.initDrag(e)
      this.hSlider.startDrag(e)
    }.bindAsEventListener(this))

    this.updateHSlider()
    this.updateSVSlider()
    this.updateSVBackground()

    if (this.options.draggable) this.makeDraggable()

    this.event('afterCreate')
  },

  show: function(e) {
    if (e) {
      $(this.picker).setStyle({
        top: (this.options.y || Event.pointerY(e)+20)+'px',
        left: (this.options.x || Event.pointerX(e))+'px'
      })
    }
    $(this.picker).show()
    this.updatePickerOffsets()

    this.dismissWithESC = function(e) {
      if (e.keyCode == Event.KEY_ESC) this.hide()
    }.bindAsEventListener(this)
    Event.observe(window, 'keypress', this.dismissWithESC)
  },

  hide: function() {
    $(this.picker).hide()
    if (this.dismissWithESC) Event.stopObserving(window, 'keypress', this.dismissWithESC)
  },

  attachToField: function() {
    Event.observe(this.options.field, "change", function(e) {
      this.updateHex(Event.element(e).value)
      this.updateSVBackground()
      this.updateHSlider()
      this.updateSVSlider()
    }.bindAsEventListener(this))
  },

  attachToColorWell: function() {
    Event.observe(this.options.colorWell, "click", function(e) {
      $(this.picker).visible() ? this.hide() : this.show(e)
    }.bindAsEventListener(this))
  },

  makeDraggable: function() {
    new Draggable(this.picker, {
      onEnd: function(draggable) {
        this.updatePickerOffsets()
      }.bind(this),
      starteffect: null,
      endeffect: null
    })
  },

  updatePickerOffsets: function() {
    this.hElementOffset = Position.cumulativeOffset(this.hElement)
    this.svElementOffset = Position.cumulativeOffset(this.svElement)
  },


  // INTERFACE UPDATES

  updateHSlider: function() {
    this.hSliderElement.setStyle({
      top: (this.hElementDimensions.height*this.HSV.H)-8+'px'
    })
  },

  updateSVSlider: function() {
    this.svSliderElement.setStyle({
      top: (this.svElementDimensions.height*(1-this.HSV.V))-8+'px',
      left: (this.svElementDimensions.width*this.HSV.S)-8+'px'
    })
  },


  // UPDATE COLORS

  updateH: function(H) {
    this.updateHSV(H, this.HSV.S, this.HSV.V)
    this.updateSVBackground()
  },

  updateSVBackground: function() {
    Element.setStyle(this.svElement, {
      backgroundColor: '#'+this.HSVToHex(this.HSV.H,1,1)
    })
  },

  updateSV: function (S,V) {
    this.updateHSV(this.HSV.H, S, V)
  },

  formattedHex: function(hex) {
    hex = hex.replace(/[^0-9a-f]/ig,'').toUpperCase()
    if (hex.length == 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2]
    return hex
  },

  updateHex: function(hex) {
    this.hex = this.formattedHex(hex)
    this.RGB = this.HexToRGB(hex)
    this.HSV = this.HexToHSV(hex)
    this.afterColorUpdate()
    this.event('afterUpdateHex')
  },

  updateHSV: function(H,S,V) {
    this.hex = this.HSVToHex(H,S,V)
    this.RGB = this.HexToRGB(this.hex)
    this.HSV = { H: H, S: S, V: V }
    this.afterColorUpdate()
    this.event('afterUpdateHSV')
  },

  updateRGB: function(R,G,B) {
    this.hex = this.RGBToHex(R,G,B)
    this.RGB = { R: R, G: G, B: B }
    this.HSV = this.RGBToHSV(R,G,B)
    this.afterColorUpdate()
    this.event('afterUpdateRGB')
  },

  afterColorUpdate: function() {
    if (this.options.field) $(this.options.field).value = '#'+this.hex
    if (this.options.showColor) $(this.colorElement).setStyle({ backgroundColor: '#'+this.hex})
    if (this.options.showHex) $(this.hexElement).update('#'+this.hex)
    if (this.options.colorWell) $(this.options.colorWell).setStyle({ backgroundColor: '#'+this.hex})
  },


  // FUN WITH COLOR
  // Brought to you by:
  // Brandon Arbini (sevenwire.com),
  // EasyRGB (easyrgb.com/math.php),
  // Yahoo.util.Color,
  // and Ulyses (ColorJack.com)

  HexToRGB: function(hex) {
    return {
      R: parseInt(hex.substring(0,2), 16),
      G: parseInt(hex.substring(2,4), 16),
      B: parseInt(hex.substring(4,6), 16)
    }
  },

  HexToHSV: function(hex) {
    var RGB = this.HexToRGB(hex)
    return this.RGBToHSV(RGB.R, RGB.G, RGB.B)
  },

  RGBToHSV: function(R,G,B) {
    var H, S, V, var_Min, var_Max, del_Max

    var_Min = Math.min(R,G,B)
    var_Max = Math.max(R,G,B)
    V = var_Max / 255
    del_Max = var_Max - var_Min

    if (del_Max == 0) return { H: 0, S: 0, V: V }

    S = del_Max / var_Max

    switch (var_Max) {
      case R: H = (G - B) / (6 * del_Max); break
      case G: H = (B - R) / (6 * del_Max) + 1/3; break
      case B: H = (R - G) / (6 * del_Max) + 2/3; break
    }

    if (H < 0) H += 1.0; else if (H > 1) H =- 1.0

    return { H: H, S: S, V: V }
  },

  HSVToHex: function(H,S,V) {
   var RGB = this.HSVToRGB(H,S,V)
   return this.RGBToHex(RGB.R, RGB.G, RGB.B)
  },

  HSVToRGB: function(H,S,V) {
    var V2, VS2, var_i, var_h, rgb_set
    
    V2 = V * 255

    var_h = H * 6 + 1.0
    if (var_h >= 6) var_h -= 6.0
    var_i = Math.floor( var_h )

    VS2 = V2 * S
    rgb_set = [V2, V2 - VS2, V2 - VS2]
    
    if (var_i & 1) rgb_set[1] += VS2 * (var_h - var_i)
      else         rgb_set[2] += VS2 * (1 - var_h + var_i)

    while (var_i > 1) { rgb_set.unshift(rgb_set.pop()); var_i -= 2 }
        
    return { R: rgb_set[0], G: rgb_set[1], B: rgb_set[2] }
  },

  RGBToHex: function(R,G,B) {
    return this.numToHex(R)+this.numToHex(G)+this.numToHex(B)
  },

  numToHex: function(n) {
    n = parseInt(n, 10)
    n = Math.min(Math.max(0, isNaN(n) ? 0 : n), 255)
    var h = n.toString(16).toUpperCase()
    return (h.length < 2) ? "0" + h : h
  }
}
