// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).
/**
 * @fileOverview Zikula.ImageViewer and ikula.ImageViewerUtil
 */
if (typeof(Zikula) == 'undefined') {
    Zikula = {};
}

Zikula.ImageViewerUtil = Class.create(/** @lends Zikula.ImageViewerUtil.prototype */{
    /**
     * Custom implementation for image zooming.
     * It was designed as Lightbox replacement and it works quite the same.
     * It's also backward compatible with Lightbox.
     * Due to nature of script it's always initialized as {@link Zikula.ImageViewer}.
     *
     * While initialization Zikula.ImageViewerUtil collets all links to images
     * with "rel" attribute containing "imageviewer" or "lightbox" value.
     * It also works for image galleries - "imageviewer[galleryname]" or  "imageviewer[lightbox]"
     *
     * @class Zikula.ImageViewerUtil
     * @constructs
     *
     * @return {Zikula.ImageViewerUtil} New Zikula.ImageViewerUtil instance
     */
    initialize: function() {
        this.setup();
        document.observe("dom:loaded",this.postInit.bind(this));
    },
    /**
     * Collects galery sets and binds event listener for images
     * @private
     */
    postInit: function() {
        this.galleries = new Hash();
        this.galleries.set('list', $$('a[rel^=lightbox], a[rel^=imageviewer]').pluck('rel').uniq());
        $$('a[rel^=lightbox], a[rel^=imageviewer]').invoke('observe','click',this.initViewer.bindAsEventListener(this));
    },
    /**
     * Public method to overwrite default ImageViewer settings.
     * Must be called before dom:loaded event fires
     *
     * @param {Object} config Config object
     * @param {Number} [config.speed=1] Factor for manipulating animation speed
     * @param {Boolean} [config.draggable=true] Should image window be draggable
     * @param {Boolean} [config.caption=true] Display image capition
     * @param {Boolean} [config.pager=true] Display pager
     * @param {Boolean} [config.modal=true] Should image window be modal
     * @param {Boolean} [config.enablekeys=true] Enable keyboard navigation (esc, prev, next)
     *
     * @return void
     */
    setup:function (config) {
        this.config = Object.extend({
            speed: 1,
            draggable: true,
            caption: true,
            pager: true,
            modal: true,
            enablekeys: true,
            langLabels: {}
        }, config || {});

        // change this to gettext
        this.config.langLabels = Object.extend({
            close: 'Close',
            next: 'Next',
            prev: 'Prev',
            pager: 'Image #{index} of #{total}'
        },this.config.langLabels);
        this.config.langLabels.pager = new Template(this.config.langLabels.pager);
    },
    /**
     * Initialize image window
     * @private
     * @param {Event} event Event which invoke ImageViewer
     * @return void
     */
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
    /**
     * Prepare image window and preloads image. When image is loaded showBox is called
     * @private
     * @return void
     */
    prepareBox: function() {
        this.updateBox();
        this.imgPreloader = new Image();
        this.imgPreloader.onload = this.showBox.bindAsEventListener(this);
        this.imgPreloader.src =  this.element.readAttribute('href');
    },
    /**
     * Open image window
     * @private
     * @return void
     */
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
    /**
     * Finalize window opening. Preloads adjacent images
     * @private
     * @return void
     */
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
    /**
     * Event hanlder for image window click events.
     * Redirects event to proper methods depending on event target
     * @private
     * @param {Event} event Click event on image window
     * @return void
     */
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
    /**
     * Event hanlder for image window key events.
     * Redirects event to proper methods depending on key letter
     * @private
     * @param {Event} event Key event on image window
     * @return void
     */
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
    /**
     * Hide box
     * @private
     * @param {Event} event It might be click or key event
     * @return void
     */
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
    /**
     * Changes image in window
     * @private
     * @param {String} dir Prev or next
     * @param {Event} event It might be click or key event
     * @return void
     */
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
    /**
     * Generates information for pager (total images in gallery, current image index)
     * @private
     * @return {Object} Object with info
     */
    pagerInfo: function() {
        return this.config.langLabels.pager.evaluate({index: this.index+1,total: this.gallerySize});
    },
    /**
     * Creates image window box
     * @private
     * @return void
     */
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
    /**
     * Updates image window box. If box does not exists - creates it
     * @private
     * @return void
     */
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
    /**
     * Preloads previous and next image
     * @private
     * @return void
     */
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
/**
 * Global ImageViewer ({@link Zikula.ImageViewerUtil}) instance used in core.
 * While ImageViewer is alwyas initialized it is possible to overwrite its config using
 * {@link Zikula.ImageViewerUtil#setup} method.
 *
 * @example
 * Zikula.ImageViewer.setup({
 *      modal: false,
 *      speed: 2
 * });
 *
 */
Zikula.ImageViewer = new Zikula.ImageViewerUtil();
