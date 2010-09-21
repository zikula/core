// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).
function showdynamicsmenu()
{
  if (Element.hasClassName('profileadminlinks', 'z-hide')) {
      Element.removeClassName('profileadminlinks', 'z-hide');
  } else {
      Element.addClassName('profileadminlinks', 'z-hide');
  }
}

function liveusersearch()
{
    Element.removeClassName('liveusersearch', 'z-hide');
    Event.observe('modifyuser', 'click', function() { window.location.href=Zikula.Config.entrypoint + "?module=users&type=admin&func=modify&uname=" + $F('username');}, false);
    Event.observe('deleteuser', 'click', function() { window.location.href=Zikula.Config.entrypoint + "?module=users&type=admin&func=deleteusers&uname=" + $F('username');}, false);
    new Ajax.Autocompleter('username', 'username_choices', Zikula.Config.baseURL + 'ajax.php?module=users&func=getusers',
                           {paramName: 'fragment',
                            minChars: 3,
                            afterUpdateElement: function(data){
                                Event.observe('modifyuser', 'click', function() { window.location.href=Zikula.Config.entrypoint + "?module=users&type=admin&func=modify&userid=" + $($(data).value).value;}, false);
                                Event.observe('deleteuser', 'click', function() { window.location.href=Zikula.Config.entrypoint + "?module=users&type=admin&func=deleteusers&userid=" + $($(data).value).value;}, false);
                                }
                            }
                            );
}
