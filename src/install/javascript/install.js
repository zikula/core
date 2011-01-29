function setFocus(){
    if (document.getElementById('lang_form')) {
        document.getElementById('lang').focus();
    } else if (document.getElementById('dbinformation_form')) {
        document.getElementById('dbusername').focus();
    } else if (document.getElementById('createadmin_form')) {
        document.getElementById('username').focus();
    }
}
