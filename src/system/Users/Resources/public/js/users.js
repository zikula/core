// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).
function showdynamicsmenu()
{
    var menuLinks = $('profileadminlinks');
    if (menuLinks.hasClassName('z-hide')) {
        menuLinks.removeClassName('z-hide');
    } else {
        menuLinks.addClassName('z-hide');
    }
}

function liveusersearch()
{
    $('liveusersearch').removeClassName('z-hide');
    $('modifyuser').observe('click', function() { 
        window.location.href = Zikula.Config.entrypoint + "?module=users&type=admin&func=modify&uname=" + $F('username');
    });
    $('deleteuser').observe('click', function() { 
        window.location.href = Zikula.Config.entrypoint + "?module=users&type=admin&func=deleteusers&uname=" + $F('username');
    });
    var options = Zikula.Ajax.Request.defaultOptions({
        paramName: 'fragment',
        minChars: 3,
        afterUpdateElement: function(data){
            $('modifyuser').observe('click', function() {
                window.location.href = Zikula.Config.entrypoint + "?module=users&type=admin&func=modify&userid=" + $($(data).value).value;
            });
            $('deleteuser').observe('click', function() {
                window.location.href=Zikula.Config.entrypoint + "?module=users&type=admin&func=deleteusers&userid=" + $($(data).value).value;
            });
        }
    });
    new Ajax.Autocompleter('username', 'username_choices', Zikula.Config.baseURL + 'index.php?module=users&type=ajax&func=getusers', options);
}
