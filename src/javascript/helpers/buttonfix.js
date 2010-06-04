/**
 * Fixing multiple submit buttons in IE6/7
 * http://www.kopz.org/public/documents/css/multiple_buttons_ie_workaround.html
 */
function buttonfix() {
  var buttons = document.getElementsByTagName('button');
  for(var i=0; i<buttons.length; i++) {
    if(buttons[i].onclick) continue;
    buttons[i].onclick = function () {
      for(j=0; j<this.form.elements.length; j++) {
        if(this.form.elements[j].tagName == 'BUTTON') {
          this.form.elements[j].disabled = true;
        }
      }
      this.disabled = false;
      this.value = this.attributes.getNamedItem("value").nodeValue;
    }
  }
}
window.attachEvent("onload", buttonfix);
