/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/controls/hotkey/
 * @attribution http://www.quirksmode.org/js/cookies.html
 */

/*global document, Prototype, $A */

if(typeof(Prototype) == "undefined") {
  throw "Cookie requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
  throw "Cookie requires Object.Event to be loaded."; }

var Cookie = {
  build: function() {
    return $A(arguments).compact().join("; ");
  },
  secondsFromNow: function(seconds) {
    var d = new Date();
    d.setTime(d.getTime() + (seconds * 1000));
    return d.toGMTString();
  },
  set: function(name,value,seconds){
    Cookie.notify('set',name,value);
    var expiry = seconds ? 'expires=' + Cookie.secondsFromNow(seconds) : null;
    document.cookie = Cookie.build(name + "=" + value, expiry, "path=/");
  },
  get: function(name){
    Cookie.notify('get',name);
    var valueMatch = new RegExp(name + "=([^;]+)").exec(document.cookie);
    return valueMatch ? valueMatch[1] : null;
  },
  unset: function(name){
    Cookie.notify('unset',name);
    Cookie.set(name,'',-1);
  }
};
Object.Event.extend(Cookie);
