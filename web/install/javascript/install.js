function setFocus(){
    if (document.getElementById('lang_form')) {
        document.getElementById('lang').focus();
    } else if (document.getElementById('dbinformation_form')) {
        document.getElementById('dbusername').focus();
    } else if (document.getElementById('createadmin_form')) {
        document.getElementById('username').focus();
    }
}
document.observe('dom:loaded',function(){
    $$('form').invoke('observe','submit',showOverlay);
    $(document.body).insert(new Element('div', {id: 'ZikulaOverlay'}).setStyle({opacity: 0.7, display: 'none'}));
})
function showOverlay() {
    $('ZikulaOverlay').appear({to: 0.7, duration: 0.2});
}