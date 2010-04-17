/**
 * Zikula Application Framework
 * @version $Id: Zikula.ImageViewer.js 28169 2010-01-30 10:09:37Z jusuff $
 *
 * Licensed to the Zikula Foundation under one or more contributor license
 * agreements. This work is licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option any later version).
 *
 * Please see the NOTICE and LICENSE files distributed with this source
 * code for further information regarding copyright ownership and licensing.
 */

if (typeof(Zikula) == 'undefined') {
    Zikula = {};
}
/**
 * Requires prototype.js, effects.js and dragdrop.js
 * To overwrite default settings use Zikula.ImageViewer.setup method.
 * Example:
 * Zikula.ImageViewer.setup({
 *      modal: false,
 *      langLabels: {close: 'Close this box'}
 * });
 */
Zikula._ImageViewer = Class.create({
    initialize: function() {
        this.setup();
        document.observe("dom:loaded",this.postInit.bind(this));
    },
    postInit: function() {
        this.galleries = new Hash();
        this.galleries.set('list', $$('a[rel^=lightbox], a[rel^=imageviewer]').pluck('rel').uniq());
        $$('a[rel^=lightbox], a[rel^=imageviewer]').invoke('observe','click',this.initViewer.bindAsEventListener(this));
    },
    setup:function () {
        this.config = Object.extend({
            speed: 1,
            draggable: true,
            caption: true,
            pager: true,
            modal: true,
            enablekeys: true,
            langLabels: {}
        }, arguments[0] || {});

        this.config.langLabels = Object.extend({
            close: 'Close',
            next: 'Next',
            prev: 'Prev',
            pager: 'Image #{index} of #{total}'
        },this.config.langLabels);
        this.config.langLabels.pager = new Template(this.config.langLabels.pager);
    },
    initViewer: function(event) {
        event.stop();
        if(this.started) {return;}
        this.started = true;
        this.element = event.findElement('a');
        this.referer = this.element.down('img') ? this.element.down('img') : this.element;
        this.isnew = true;
        this.isGallery = false;
        if(this.element.rel != 'lightbox' && this.element.rel != 'imageviewer') {
            if(!this.galleries.get(this.element.rel)) {
                this.galleries.set(this.element.rel,$$('a[rel="'+this.element.rel+'"]').collect(function(s) {return s.identify();}));
            }
            this.gallerySize = this.galleries.get(this.element.rel).length;
            this.isGallery = this.gallerySize > 1;
        }
        this.prepareBox();
    },
    prepareBox: function() {
        this.updateBox();
        this.imgPreloader = new Image();
        this.imgPreloader.onload = this.showBox.bindAsEventListener(this);
        this.imgPreloader.src =  this.element.readAttribute('href');
    },
    showBox: function() {
        if(this.config.modal) {
            this.ImageViewerOverlay.appear({to: 0.9, duration: this.config.speed/2});
        }
        this.ImageViewerImg.src = this.element.readAttribute('href');
        this.ImageViewerImg.width = this.imgPreloader.width;
        this.ImageViewerImg.height = this.imgPreloader.height;
        if(this.config.caption){
            this.ImageViewerTitle.update(this.element.readAttribute('title') || '&nbsp;');
            this.ImageViewerCapition.setStyle({width: this.imgPreloader.width+'px'});
        }
        if(this.config.pager && this.isGallery){
            this.ImageViewerPager.update(this.pagerInfo()).show();
        }
        this.imageBox.setStyle({width: 'auto', height: 'auto'});

        var dim = this.imageBox.getDimensions(),
            globalDim = document.viewport.getDimensions();

        this.imageBox.absolutize().clonePosition(this.referer);
        if(this.isnew) {
            this.imageBox.setStyle({overflow: 'hidden', opacity: 0.5})
        }
        var newTop = Math.floor((globalDim.height/2) - (dim.height/2) + document.viewport.getScrollOffsets().top),
            newLeft= Math.floor((globalDim.width/2) - (dim.width/2)),
            moveX = -(this.referer.cumulativeOffset().left - newLeft),
            moveY = -(this.referer.cumulativeOffset().top - newTop);
        if(this.isnew) {
            new Effect.Parallel([
                new Effect.Appear(this.imageBox, {from: 0.5, to: 1, sync:true}),
                new Effect.Move(this.imageBox, {x: moveX, y: moveY, sync: true}),
                new Effect.Morph(this.imageBox, {style: {width: dim.width + 'px', height: dim.height + 'px'}, sync: true})
            ],  {duration: this.config.speed, afterFinish: this.finishBox.bind(this)});
        } else {
            new Effect.Morph(this.imageBox, {style: {left: newLeft + 'px', top: newTop + 'px', width: dim.width + 'px', height: dim.height + 'px'},
                duration: this.config.speed, afterFinish: this.finishBox.bind(this)}
            );
        }
    },
    finishBox: function() {
        this.ImageViewerImg.appear({from: 0, to: 1, duration: this.config.speed/2,
            afterSetup: function(){this.imageBox.removeClassName('loading').setStyle({overflow: 'visible'});}.bind(this)
        });
        if (this.config.draggable){
            if(this.isGallery && this.config.caption) {
                this.drag = new Draggable(this.imageBox, {handle: this.ImageViewerCapition});
            } else {
                this.drag = new Draggable(this.imageBox);
            }
        }
        this.started = false;
        if(this.isGallery) {
            this.preloadAdjacentImages();
        }
    },
    clickBox: function(event) {
        event.stop();
        if(this.imageBox.visible()) {
            if(event.element() ==  $('ImageViewerPrev')) {
                this.moveBox('prev',event);
            } else if(event.element() ==  $('ImageViewerNext')) {
                this.moveBox('next',event);
            } else if(event.element() ==  $('ImageViewerClose')) {
                this.hideBox(event);
            }
        }
    },
    key: function(event) {
        if(this.imageBox.visible() && event.keyCode) {
            switch(event.keyCode) {
                case Event.KEY_LEFT:
                    this.moveBox('prev',event);
                    break;
                case Event.KEY_RIGHT:
                    this.moveBox('next',event);
                    break;
                case Event.KEY_ESC:
                    this.hideBox(event);
                    break;
            }
        }
    },
    hideBox: function(event) {
        if(!event || !event.isRightClick()) {
            this.imageBox.fade({duration:this.config.speed/4});
            if(this.config.modal) {
                this.ImageViewerOverlay.fade({duration: this.config.speed/2});
            }
            if(this.drag && this.drag.destroy) {
                this.drag.destroy();
            }
        }
    },
    moveBox: function(dir,event) {
        if(!this.isGallery) {return;}
        event.stop();
        var change = dir == 'prev' ? -1: 1,
            next = this.galleries.get(this.element.rel)[this.index+change];
        if(next) {
            this.element = $(next);
            this.referer = this.imageBox;
            this.isnew = false;
            this.prepareBox();
        }
    },
    pagerInfo: function() {
        return this.config.langLabels.pager.evaluate({index: this.index+1,total: this.gallerySize});
    },
    buildBox: function() {
        this.endBind = this.hideBox.bindAsEventListener(this);
        this.keyBind = this.key.bindAsEventListener(this);
        if(this.config.modal) {
            this.ImageViewerOverlay = new Element('div', {id: 'ImageViewerOverlay'}).setStyle({opacity: 0.9, display: 'none'})
            $(document.body).insert(this.ImageViewerOverlay);
            this.ImageViewerOverlay.observe('click', this.endBind);
        }
        this.imageBox = new Element('div', {id: 'ImageViewer'})
        $(document.body).insert(this.imageBox);
        if(this.config.caption) {
            this.ImageViewerCapition = new Element('p',{id: 'ImageViewerCapition'});
            this.ImageViewerTitle = new Element('span',{id: 'ImageViewerTitle'});
            this.ImageViewerClose = new Element('span',{id: 'ImageViewerClose', title: this.config.langLabels.close});
            this.imageBox.insert(this.ImageViewerCapition
                .insert(this.ImageViewerTitle)
                .insert(this.ImageViewerClose)
            );
        }
        this.ImageViewerImgContainer = new Element('div',{id: 'ImageViewerImgContainer'});
        this.ImageViewerImg = new Element('img',{id: 'ImageViewerImg', src: this.element.readAttribute('href'), alt: this.element.readAttribute('title') || ''});
        this.ImageViewerPrev = new Element('a',{id: 'ImageViewerPrev', title: this.config.langLabels.prev}).hide();
        this.ImageViewerNext = new Element('a',{id: 'ImageViewerNext', title: this.config.langLabels.next}).hide();
        this.imageBox.insert(this.ImageViewerImgContainer
            .insert(this.ImageViewerImg)
            .insert(this.ImageViewerPrev)
            .insert(this.ImageViewerNext)
        );
        if(this.config.pager) {
            this.ImageViewerPager = new Element('p',{id: 'ImageViewerPager'});
            this.imageBox.insert(this.ImageViewerPager);
        }
        this.imageBox.absolutize().clonePosition(this.referer).setStyle({overflow: 'hidden', opacity: 0.5});
        this.imageBox.observe('click',this.clickBox.bindAsEventListener(this));
        document.observe('click', this.endBind);
        if(this.config.enablekeys) {
            document.observe('keydown', this.keyBind);
        }
    },
    updateBox: function() {
        if(!this.imageBox) {
            this.buildBox();
        }
        if(this.drag && this.drag.destroy) {
            this.drag.destroy();
        }
        this.imageBox.className = '';
        this.imageBox.addClassName('loading');
        this.ImageViewerPager.hide();
        this.ImageViewerPrev.hide();
        this.ImageViewerNext.hide();
        if(this.isGallery) {
            this.index = this.galleries.get(this.element.rel).indexOf(this.element.identify());
            if(this.index == 0) {
                this.imageBox.addClassName('first');
            } else {
                this.ImageViewerPrev.show();
            }
            if (this.index + 1 == this.gallerySize) {
                this.imageBox.addClassName('last');
            } else {
                this.ImageViewerNext.show();
            }
            this.imageBox.addClassName('gallery');
        }
    },
    preloadAdjacentImages: function() {
        var prevImage, nextImage;
        if (this.index > 0){
            prevImage = new Image();
            prevImage.src = $(this.galleries.get(this.element.rel)[this.index-1]).href;
        }
        if (this.index + 1 < this.gallerySize){
            nextImage = new Image();
            nextImage.src = $(this.galleries.get(this.element.rel)[this.index+1]).href;
        }
    }
});
Zikula.ImageViewer = new Zikula._ImageViewer();

// fix for https://prototype.lighthouseapp.com/projects/8886-prototype/tickets/771
Element.addMethods({
  getOffsetParent: function(element) {
    if (element.offsetParent) return $(element.offsetParent);
    if (element == document.body) return $(element);

//    while ((element = element.parentNode) && element != document.body)
    while ((element = element.parentNode) && element != document.body && element != document)
      if (Element.getStyle(element, 'position') != 'static')
        return $(element);

    return $(document.body);
  }
});
