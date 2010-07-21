// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

var adding = Array();
 /**
 * Inits the ajax stuff: show ajax buttons, remove non ajax buttons etc.
 *
 *@params none;
 *@return none;
 *@author Frank Chestnut
 */
function groupinit(defgroupid, frstgroup, admingroupid)
{
    defaultgroup = defgroupid;
    firstgroup = frstgroup;
    admingroup = admingroupid;

    // craigh specialsuperusability extension :-)
    deleteiconhtml = $('groupeditdelete_'+firstgroup).innerHTML;
    canceliconhtml = $('groupeditcancel_'+firstgroup).innerHTML;

    appending = false;
    Element.removeClassName('appendajax', 'z-hide');

    // set observers on all existing groups images
    $$('button.z-imagebutton').each(
    function(singlebutton)
    {
        var groupid = singlebutton.id.split('_')[1];
        switch(singlebutton.id.split('_')[0])
        {
            case "groupeditsave":
                Event.observe('groupeditsave_'   + groupid, 'click', function(){ groupmodify(groupid)},       false);
                break;
            case "groupeditdelete":
                Event.observe('groupeditdelete_' + groupid, 'click', function(){ groupdelete(groupid)},       false);
                break;
            case "groupeditcancel":
                Event.observe('groupeditcancel_' + groupid, 'click', function(){ groupmodifycancel(groupid)}, false);
                break;
        }
    });
}

/**
 * Append a new permission at the end of the list
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function groupappend()
{
    if(appending == false) {
        appending = true;
        var pars = "module=Groups&func=creategroup&authid=" + $F('groupsauthid');
        var myAjax = new Ajax.Request(
            "ajax.php",
            {
                method: 'post',
                parameters: pars,
                onComplete: groupappend_response
            });
    }
}

/**
 * Ajax response function for appending a new group: adds a new li,
 * updates fields and makes them visible. More important: renames all ids
 *
 *@params req reponse from ajax call;
 *@return none;
 *@author Frank Schummertz
 */
function groupappend_response(req)
{
    appending = false;
    if(req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('groupsauthid').value = json.authid;

    // copy new group li from permission_1.
    var newgroup = $('group_'+firstgroup).cloneNode(true);

    // update the ids. We use the getElementsByTagName function from
    // protoype for this. The 6 tags here cover everything in a single li
    // that has a unique id
    newgroup.id   = 'group_' + json.gid;
    $A(newgroup.getElementsByTagName('a')).each(function(node)       { node.id = node.id.split('_')[0] + '_' + json.gid; });
    $A(newgroup.getElementsByTagName('div')).each(function(node)     { node.id = node.id.split('_')[0] + '_' + json.gid; });
    $A(newgroup.getElementsByTagName('span')).each(function(node)    { node.id = node.id.split('_')[0] + '_' + json.gid; });
    $A(newgroup.getElementsByTagName('input')).each(function(node)   { node.id = node.id.split('_')[0] + '_' + json.gid; node.value = ''; });
    $A(newgroup.getElementsByTagName('select')).each(function(node)  { node.id = node.id.split('_')[0] + '_' + json.gid; });
    $A(newgroup.getElementsByTagName('button')).each(function(node)  { node.id = node.id.split('_')[0] + '_' + json.gid; });
    $A(newgroup.getElementsByTagName('textarea')).each(function(node){ node.id = node.id.split('_')[0] + '_' + json.gid; });

    // append new group to the group list
    $('grouplist').appendChild(newgroup);

    // set initial values in input, hidden and select
    $('name_'            + json.gid).value = json.name;
    $('description_'     + json.gid).value = json.description;
    $('editgroupnbumax_' + json.gid).value = json.nbumax;
    $('members_'         + json.gid).href  = json.membersurl;

    pnsetselectoption('groupstate_' + json.gid, json.statelbl);
    pnsetselectoption('groupgtype_' + json.gid, json.gtypelbl);

    // hide cancel icon for new groups
//    Element.addClassName('groupeditcancel_' + json.gid, 'z-hide');
    // update delete icon to show cancel icon
//    Element.update('groupeditdelete_' + json.gid, canceliconhtml);

    // update some innerHTML
    Element.update('groupnbuser_'      + json.gid, json.nbuser);
    Element.update('groupnbumax_'      + json.gid, json.nbumax);
    Element.update('groupgid_'         + json.gid, json.gid);
    Element.update('groupname_'        + json.gid, json.name);
    Element.update('groupgtype_'       + json.gid, json.gtypelbl);
    Element.update('groupdescription_' + json.gid, json.description) + '&nbsp;';
    Element.update('groupstate_'       + json.gid, json.statelbl);
    //Element.update('members_'          + json.gid, json.membersurl);

    // add events
    Event.observe('modifyajax_'      + json.gid, 'click', function(){groupmodifyinit(json.gid)}, false);
    Event.observe('groupeditsave_'   + json.gid, 'click', function(){groupmodify(json.gid)}, false);
    Event.observe('groupeditdelete_' + json.gid, 'click', function(){groupdelete(json.gid)}, false);
    Event.observe('groupeditcancel_' + json.gid, 'click', function(){groupmodifycancel(json.gid)}, false);

    // remove class to make edit button visible
    Element.removeClassName('modifyajax_' + json.gid, 'z-hide');
    Event.observe('modifyajax_' + json.gid, 'click', function(){groupmodifyinit(json.gid)}, false);

    // turn on edit mode
    enableeditfields(json.gid);

    // we are ready now, make it visible
    Element.removeClassName('group_' + json.gid, 'z-hide');
    new Effect.Highlight('group_' + json.gid, { startcolor: '#ffff99', endcolor: '#ffffff' });


    // set flag: we are adding a new group
    adding[json.gid] = 1;
}

/**
 * Start edit of permissions: hide/show the neceaasty fields
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function groupmodifyinit(groupid)
{
    if(getmodifystatus(groupid) == 0) {
        pnsetselectoption('gtype_' + groupid, $F('gtypeid_' + groupid));
        pnsetselectoption('state_' + groupid, $F('state_' + groupid));

        if ((groupid == defaultgroup) || (groupid == admingroup)) {
            Element.addClassName('groupeditdelete_' + groupid, 'z-hide');
        } else {
            Element.removeClassName('groupeditdelete_' + groupid, 'z-hide');
        }
        enableeditfields(groupid);
    }
}

/**
 * Show/hide all fields needed for modifying a permission
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function enableeditfields(groupid)
{
    Element.addClassName('groupname_'               + groupid, 'z-hide');
    Element.addClassName('groupgtype_'              + groupid, 'z-hide');
    Element.addClassName('groupdescription_'        + groupid, 'z-hide');
    Element.addClassName('groupstate_'              + groupid, 'z-hide');
    Element.addClassName('groupnbumax_'             + groupid, 'z-hide');
    Element.addClassName('groupaction_'             + groupid, 'z-hide');
    Element.removeClassName('editgroupname_'        + groupid, 'z-hide');
    Element.removeClassName('editgroupgtype_'       + groupid, 'z-hide');
    Element.removeClassName('editgroupdescription_' + groupid, 'z-hide');
    Element.removeClassName('editgroupstate_'       + groupid, 'z-hide');
    Element.removeClassName('editgroupnbumax_'      + groupid, 'z-hide');
    Element.removeClassName('editgroupaction_'      + groupid, 'z-hide');
}

/**
 * Show/hide all fields needed for not modifying a permission
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function disableeditfields(groupid)
{
    Element.addClassName('editgroupname_'        + groupid, 'z-hide');
    Element.addClassName('editgroupgtype_'       + groupid, 'z-hide');
    Element.addClassName('editgroupdescription_' + groupid, 'z-hide');
    Element.addClassName('editgroupstate_'       + groupid, 'z-hide');
    Element.addClassName('editgroupnbumax_'      + groupid, 'z-hide');
    Element.addClassName('editgroupaction_'      + groupid, 'z-hide');
    Element.removeClassName('groupname_'         + groupid, 'z-hide');
    Element.removeClassName('groupgtype_'        + groupid, 'z-hide');
    Element.removeClassName('groupdescription_'  + groupid, 'z-hide');
    Element.removeClassName('groupstate_'        + groupid, 'z-hide');
    Element.removeClassName('groupnbumax_'       + groupid, 'z-hide');
    Element.removeClassName('groupaction_'       + groupid, 'z-hide');
}

/**
 * Cancel permission modification
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function groupmodifycancel(groupid)
{
    if(adding[groupid] == 1) {
        groupdelete(groupid);
        adding = adding.without(groupid);
        return;
    }
    disableeditfields(groupid);
    setmodifystatus(groupid, 0)
}

/**
 * Reads a hidden field that holds the modification status
 *
 *@params permid the permission id;
 *@return 1 if modification is in progress, otherwise 0;
 *@author Frank Schummertz
 */
function getmodifystatus(groupid)
{
    return $F('modifystatus_' + groupid);
}

/**
 * Set the hidden field the holds the modification status
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function setmodifystatus(groupid, newvalue)
{
    $('modifystatus_' + groupid).value = newvalue;
}

/**
 * Store updated permission in the database
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function groupmodify(groupid)
{
    disableeditfields(groupid);
    if(getmodifystatus(groupid) == 0) {
        setmodifystatus(groupid, 1);
        showinfo(groupid, updatinggroup);
        // store via ajax
        var pars = "module=Groups&func=updategroup&authid="
                   + $F('groupsauthid')
                   + "&gid="         + groupid
                   + "&name="        + encodeURIComponent($F('name_' + groupid))
                   + "&gtype="       + $F('gtype_' + groupid)
                   + "&description=" + encodeURIComponent($F('description_' + groupid))
                   + "&state="       + $F('state_' + groupid)
                   + "&nbumax="      + $F('nbumax_' + groupid);
        var myAjax = new Ajax.Request("ajax.php", { method: 'post',
                                                    parameters: pars,
                                                    onComplete: groupmodify_response,
                                                    onFailure: function(){groupfailure_response(groupid);}
                                                  });


    }
}


/**
 * Ajax response function for updating the permission: update fields, cleanup
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function groupmodify_response(req)
{
    if(req.status != 200 ) {
        showinfo();
        pnshowajaxerror(req.responseText);
        return;
    }

    var json = pndejsonize(req.responseText);
    pnupdateauthids(json.authid);
    $('groupsauthid').value = json.authid;

    // check for groups internal error
    if(json.error == 1) {
        showinfo();
        Element.addClassName($('groupinfo_' + json.gid), 'z-hide');
        Element.removeClassName($('groupcontent_' + json.gid), 'z-hide');

        /*
        // add events
        Event.observe('modifyajax_'      + json.gid, 'click', function(){groupmodifyinit(json.gid)}, false);
        Event.observe('groupeditsave_'   + json.gid, 'click', function(){groupmodify(json.gid)}, false);
        Event.observe('groupeditdelete_' + json.gid, 'click', function(){groupdelete(json.gid)}, false);
        Event.observe('groupeditcancel_' + json.gid, 'click', function(){groupmodifycancel(json.gid)}, false);
        enableeditfields(json.gid);
        */
        pnshowajaxerror(json.message);
        setmodifystatus(json.gid, 0);
        groupmodifyinit(json.gid);
        return;
    }

    $('gtype_' + json.gid).value = json.gtype;
    $('state_' + json.gid).value = json.state;

    Element.update('groupgtype_' + json.gid, json.gtypelbl);
    Element.update('groupname_' + json.gid, json.name);

    Element.update('groupdescription_' + json.gid, json.description + '&nbsp;');
    Element.update('groupstate_'       + json.gid, json.statelbl);
    Element.update('groupnbuser_'      + json.gid, json.nbuser);
    Element.update('groupnbumax_'      + json.gid, json.nbumax);

    adding = adding.without(json.gid);

    // show trascan icon for new permissions if necessary
    Element.removeClassName('groupeditcancel_' + json.gid, 'z-hide');
    // update delete icon to show trashcan icon
    Element.update('groupeditdelete_' + json.gid, deleteiconhtml);

    setmodifystatus(json.gid, 0);
    showinfo(json.gid);
}

/**
 * Delete a permission
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function groupdelete(groupid)
{
    if(confirm(confirmDeleteGroup) && getmodifystatus(groupid) == 0) {
        showinfo(groupid, deletinggroup);
        setmodifystatus(groupid, 1);
        // delete via ajax
        var pars = "module=Groups&func=deletegroup&authid="
                   + $F('groupsauthid')
                   + '&gid=' + groupid;
        var myAjax = new Ajax.Request(
            "ajax.php",
            {
                method: 'get',
                parameters: pars,
                onComplete: groupdelete_response,
                onFailure: function(){groupfailure_response(groupid);}
            });
    }
}

/**
 * Ajax response function for deleting a permission: simply remove the li
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function groupdelete_response(req)
{
    if(req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('groupsauthid').value = json.authid;

    setmodifystatus(json.gid, 0);
    Element.remove('group_' + json.gid);
}

/**
 * Generic Ajax response function for failures; restores previous view
 *
 *@params groupid group id;
 *@return none;
 */
function groupfailure_response(groupid)
{
    showinfo(groupid);
    disableeditfields(groupid);
    setmodifystatus(groupid, 0);
}



/**
 * Use to temporarily show an infotext instead of the permission. Must be
 * called twice:
 * #1: Show the infotext
 * #2: restore normal display
 * If both parameters are missing all infotext fields will be restored to
 * normal display
 *
 *@params permid the permission id;
 *@params infotext the text to show;
 *@return none;
 *@author Frank Schummertz
 */
function showinfo(groupid, infotext)
{

    if(groupid) {
        var groupinfo = 'groupinfo_' + groupid;
        var group = 'groupcontent_' + groupid;
        if(!Element.hasClassName(groupinfo, 'z-hide')) {
            Element.update(groupinfo, '&nbsp;');
            Element.addClassName(groupinfo, 'z-hide');
            Element.removeClassName(group, 'z-hide');
        } else {
            Element.update(groupinfo, infotext);
            Element.addClassName(group, 'z-hide');
            Element.removeClassName(groupinfo, 'z-hide');
        }
    } else {
        $A(document.getElementsByClassName('z-groupinfo')).each(function(groupinfo){
            Element.update(groupinfo, '&nbsp;');
            Element.addClassName(groupinfo, 'z-hide');
        });
        $A(document.getElementsByClassName('groupcontent')).each(function(groupcontent){
            Element.removeClassName(groupcontent, 'z-hide');
        });
    }
}