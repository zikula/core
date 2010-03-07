var Resizable = {
  einfo: false,
  minheight: 50,
  minwidth: 100,
  initialize: function(id) {
    var x = $(id);
    if (!x) return;
    Resizable.prepare(x);
  },
  prepare: function(container) {
    var parent  = container.parentNode;
    var wrapper = document.createElement('div');
    var handler = document.createElement('div');
    if (!parent || !wrapper || !handler) return;
    wrapper.style.width = container.offsetWidth + 'px';
    handler.addClassName('textarea-resizer');
    handler.style.width = (container.offsetWidth-2) + 'px';
    handler._wrapper   = wrapper;
    handler._container = container;
    parent.insertBefore(wrapper, container);
    wrapper.appendChild(container);
    wrapper.appendChild(handler);
    handler.onmousedown = function(e) { Resizable.onmousedown(e,this); }
  },
  onmousedown: function(e, handler) {
    if (this.einfo || !handler._wrapper) return;
    if (!e) e = window.event;
    this.einfo = {
      handler: handler,
      wrapper: handler._wrapper,
      container: handler._container,
      w: handler._container.offsetWidth,
      h: handler._container.offsetHeight,
      x: e.clientX,
      y: e.clientY
    };

    this.einfo.container.addClassName('textarea-active');
    this.einfo.wrapper.addClassName('textarea-wrapper');

    this.oldmousemove = document.onmousemove;
    this.oldmouseup   = document.onmouseup;
    document.onmousemove = function(e){ Resizable.onmousemove(e); }
    document.onmouseup   = function(e){ Resizable.onmouseup(e); }
  },
  onmouseup: function(e) {
    if (!this.einfo) return; 
    with(this.einfo) {
      container.removeClassName ('textarea-active');
      wrapper.removeClassName('textarea-wrapper');
    }
    this.einfo = false;
    document.onmousemove = this.oldmousemove;
    document.onmouseup   = this.oldmouseup;
  },
  onmousemove: function(e) {
    if (!this.einfo) return;
    if (!e) e = window.event;

    this.einfo.container.style.height = Math.max(this.minheight, this.einfo.h + e.clientY - this.einfo.y) + 'px';
    this.einfo.container.style.width  = Math.max(this.minwidth,  this.einfo.w + e.clientX - this.einfo.x) + 'px';
    this.einfo.handler.style.width = this.einfo.container.style.width;
    this.einfo.wrapper.style.width = this.einfo.container.style.width;

    if (e.preventDefault) {
        e.preventDefault();
        e.stopPropagation();
    } else {
        e.returnValue = false;
        e.cancelBubble = true;
    }
  }
};

