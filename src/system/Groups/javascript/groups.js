// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

var adding = Array();

 /**
 * Inits the ajax stuff: show ajax buttons, remove non ajax buttons etc.
 *
 *@params none;
 *@return none;
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
    $('appendajax').removeClassName('z-hide');

    // set observers on all existing groups images
    $$('button.z-imagebutton').each(
    function(singlebutton)
    {
        var groupid = singlebutton.id.split('_')[1];
        switch(singlebutton.id.split('_')[0])
        {
            case "groupeditsave":
                $('groupeditsave_'   + groupid).observe('click', function() { groupmodify(groupid); });
                break;
            case "groupeditdelete":
                $('groupeditdelete_' + groupid).observe('click', function() { groupdelete(groupid); });
                break;
            case "groupeditcancel":
                $('groupeditcancel_' + groupid).observe('click', function() { groupmodifycancel(groupid); });
                break;
        }
    });
}

/**
 * Append a new group at the end of the list
 *
 *@params none;
 *@return none;
 */
function groupappend()
{
    if (appending == false) {
        appending = true;

        new Zikula.Ajax.Request(
            "ajax.php?module=Groups&func=creategroup",
            {
                onComplete: groupappend_response
            });
    }
}

/**
 * Ajax response function for appending a new group: adds a new li,
 * updates fields and makes them visible. More important: renames all ids
 *
 *@params req response from ajax call;
 *@return none;
 */
function groupappend_response(req)
{
    appending = false;

    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    // copy new group li from permission_1.
    var newgroup = $('group_'+firstgroup).cloneNode(true);

    // update the ids. We use the getElementsByTagName function from
    // protoype for this. The 6 tags here cover everything in a single li
    // that has a unique id
    newgroup.id   = 'group_' + data.gid;
    $A(newgroup.getElementsByTagName('a')).each(function(node)       { node.id = node.id.split('_')[0] + '_' + data.gid; });
    $A(newgroup.getElementsByTagName('div')).each(function(node)     { node.id = node.id.split('_')[0] + '_' + data.gid; });
    $A(newgroup.getElementsByTagName('span')).each(function(node)    { node.id = node.id.split('_')[0] + '_' + data.gid; });
    $A(newgroup.getElementsByTagName('input')).each(function(node)   { node.id = node.id.split('_')[0] + '_' + data.gid; node.value = ''; });
    $A(newgroup.getElementsByTagName('select')).each(function(node)  { node.id = node.id.split('_')[0] + '_' + data.gid; });
    $A(newgroup.getElementsByTagName('button')).each(function(node)  { node.id = node.id.split('_')[0] + '_' + data.gid; });
    $A(newgroup.getElementsByTagName('textarea')).each(function(node){ node.id = node.id.split('_')[0] + '_' + data.gid; });

    // append new group to the group list
    $('grouplist').appendChild(newgroup);

    // set initial values in input, hidden and select
    $('name_'            + data.gid).value = data.name;
    $('description_'     + data.gid).value = data.description;
    $('editgroupnbumax_' + data.gid).value = data.nbumax;
    $('members_'         + data.gid).href  = data.membersurl;

    Zikula.setselectoption('state_' + data.gid, data.statelbl);
    Zikula.setselectoption('gtype_' + data.gid, data.gtypelbl);

    // hide cancel icon for new groups
    // $('groupeditcancel_' + json.gid).addClassName('z-hide');
    // update delete icon to show cancel icon
    // $('groupeditdelete_' + json.gid).update(canceliconhtml);

    // update some innerHTML
    $('groupnbuser_'      + data.gid).update(data.nbuser);
    $('groupnbumax_'      + data.gid).update(data.nbumax);
    $('groupgid_'         + data.gid).update(data.gid);
    $('groupname_'        + data.gid).update(data.name);
    $('groupgtype_'       + data.gid).update(data.gtypelbl);
    $('groupdescription_' + data.gid).update(data.description + '&nbsp;');
    $('groupstate_'       + data.gid).update(data.statelbl);
    //$('members_'          + json.gid).update(json.membersurl);

    // add events
    $('modifyajax_'      + data.gid).observe('click', function() { groupmodifyinit(data.gid); });
    $('groupeditsave_'   + data.gid).observe('click', function() { groupmodify(data.gid); });
    $('groupeditdelete_' + data.gid).observe('click', function() { groupdelete(data.gid); });
    $('groupeditcancel_' + data.gid).observe('click', function() { groupmodifycancel(data.gid); });

    // remove class to make edit button visible
    $('modifyajax_' + data.gid).removeClassName('z-hide');
    $('modifyajax_' + data.gid).observe('click', function() { groupmodifyinit(data.gid); });

    // turn on edit mode
    enableeditfields(data.gid);

    // we are ready now, make it visible
    $('group_' + data.gid).removeClassName('z-hide');
    new Effect.Highlight('group_' + data.gid, { startcolor: '#ffff99', endcolor: '#ffffff' });

    // set flag: we are adding a new group
    adding[data.gid] = 1;
}

/**
 * Start edit of permissions: hide/show the neceaasty fields
 *
 *@params permid the permission id;
 *@return none;
 */
function groupmodifyinit(groupid)
{
    if (getmodifystatus(groupid) == 0) {
        Zikula.setselectoption('gtype_' + groupid, $F('gtype_' + groupid));
        Zikula.setselectoption('state_' + groupid, $F('state_' + groupid));

        if ((groupid == defaultgroup) || (groupid == admingroup)) {
            $('groupeditdelete_' + groupid).addClassName('z-hide');
        } else {
            $('groupeditdelete_' + groupid).removeClassName('z-hide');
        }
        enableeditfields(groupid);
    }
}

/**
 * Show/hide all fields needed for modifying a permission
 *
 *@params permid the permission id;
 *@return none;
 */
function enableeditfields(groupid)
{
    $('groupname_'               + groupid).addClassName('z-hide');
    $('groupgtype_'              + groupid).addClassName('z-hide');
    $('groupdescription_'        + groupid).addClassName('z-hide');
    $('groupstate_'              + groupid).addClassName('z-hide');
    $('groupnbumax_'             + groupid).addClassName('z-hide');
    $('groupaction_'             + groupid).addClassName('z-hide');
    $('editgroupname_'        + groupid).removeClassName('z-hide');
    $('editgroupgtype_'       + groupid).removeClassName('z-hide');
    $('editgroupdescription_' + groupid).removeClassName('z-hide');
    $('editgroupstate_'       + groupid).removeClassName('z-hide');
    $('editgroupnbumax_'      + groupid).removeClassName('z-hide');
    $('editgroupaction_'      + groupid).removeClassName('z-hide');
}

/**
 * Show/hide all fields needed for not modifying a permission
 *
 *@params permid the permission id;
 *@return none;
 */
function disableeditfields(groupid)
{
    $('editgroupname_'        + groupid).addClassName('z-hide');
    $('editgroupgtype_'       + groupid).addClassName('z-hide');
    $('editgroupdescription_' + groupid).addClassName('z-hide');
    $('editgroupstate_'       + groupid).addClassName('z-hide');
    $('editgroupnbumax_'      + groupid).addClassName('z-hide');
    $('editgroupaction_'      + groupid).addClassName('z-hide');
    $('groupname_'         + groupid).removeClassName('z-hide');
    $('groupgtype_'        + groupid).removeClassName('z-hide');
    $('groupdescription_'  + groupid).removeClassName('z-hide');
    $('groupstate_'        + groupid).removeClassName('z-hide');
    $('groupnbumax_'       + groupid).removeClassName('z-hide');
    $('groupaction_'       + groupid).removeClassName('z-hide');
}

/**
 * Cancel permission modification
 *
 *@params none;
 *@return none;
 */
function groupmodifycancel(groupid)
{
    if (adding[groupid] == 1) {
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
 */
function groupmodify(groupid)
{
    disableeditfields(groupid);
    if (getmodifystatus(groupid) == 0) {
        setmodifystatus(groupid, 1);
        showinfo(groupid, updatinggroup);

        // store via ajax
       var pars = {
           gid: groupid,
           name: $F('name_' + groupid),
           gtype: $F('gtype_' + groupid),
           description: $F('description_' + groupid),
           state: $F('state_' + groupid),
           nbumax: $F('nbumax_' + groupid)
       };

        new Zikula.Ajax.Request(
            "ajax.php?module=Groups&func=updategroup",
            {
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
 */
function groupmodify_response(req)
{
    if (!req.isSuccess()) {
        showinfo();
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    // check for groups internal error
    if (data.error == 1) {
        showinfo();
        $('groupinfo_' + data.gid).addClassName('z-hide');
        $('groupcontent_' + data.gid).removeClassName('z-hide');

        /*
        // add events
        $('modifyajax_'      + json.gid).observe('click', function() { groupmodifyinit(json.gid); });
        $('groupeditsave_'   + json.gid).observe('click', function() { groupmodify(json.gid); });
        $('groupeditdelete_' + json.gid).observe('click', function() { groupdelete(json.gid); });
        $('groupeditcancel_' + json.gid).observe('click', function() { groupmodifycancel(json.gid); });
        enableeditfields(json.gid);
        */

        Zikula.showajaxerror(data.message);
        setmodifystatus(data.gid, 0);
        groupmodifyinit(data.gid);
        return;
    }

    $('gtype_' + data.gid).value = data.gtype;
    $('state_' + data.gid).value = data.state;

    $('groupgtype_' + data.gid).update(data.gtypelbl);
    $('groupname_' + data.gid).update(data.name);

    $('groupdescription_' + data.gid).update(data.description + '&nbsp;');
    $('groupstate_'       + data.gid).update(data.statelbl);
    $('groupnbuser_'      + data.gid).update(data.nbuser);
    $('groupnbumax_'      + data.gid).update(data.nbumax);

    adding = adding.without(data.gid);

    // show trascan icon for new permissions if necessary
    $('groupeditcancel_' + data.gid).removeClassName('z-hide');
    // update delete icon to show trashcan icon
    $('groupeditdelete_' + data.gid).update(deleteiconhtml);

    setmodifystatus(data.gid, 0);
    showinfo(data.gid);
}

/**
 * Delete a permission
 *
 *@params permid the permission id;
 *@return none;
 */
function groupdelete(groupid)
{
    if (confirm(confirmDeleteGroup) && getmodifystatus(groupid) == 0) {
        showinfo(groupid, deletinggroup);
        setmodifystatus(groupid, 1);

        // delete via ajax
        var pars = {
            gid: groupid
        };

        new Zikula.Ajax.Request(
            "ajax.php?module=Groups&func=deletegroup",
            {
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
 */
function groupdelete_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    setmodifystatus(data.gid, 0);
    $('group_' + data.gid).remove();
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
 */
function showinfo(groupid, infotext)
{
    if (groupid) {
        var groupinfo = $('groupinfo_' + groupid);
        var group = $('groupcontent_' + groupid);
        if (!groupinfo.hasClassName('z-hide')) {
            groupinfo.update('&nbsp;');
            groupinfo.addClassName('z-hide');
            group.removeClassName('z-hide');
        } else {
            groupinfo.update(infotext);
            group.addClassName('z-hide');
            groupinfo.removeClassName('z-hide');
        }
    } else {
        $A(document.getElementsByClassName('z-groupinfo')).each(function(groupinfo) {
            $(groupinfo).update('&nbsp;');
            $(groupinfo).addClassName('z-hide');
        });
        $A(document.getElementsByClassName('groupcontent')).each(function(groupcontent) {
            $(groupcontent).removeClassName('z-hide');
        });
    }
}

function groupremoveuser(event) {
    event.preventDefault();
    var link = event.findElement('a'),
        rel = link.readAttribute('rel').split(':'),
        pars = {
            gid: rel[0],
            uid: rel[1]
        }
    new Zikula.Ajax.Request('ajax.php?module=Groups&func=removeuser', {
            parameters: pars,
            onComplete: groupremoveuser_response
    });
}

function groupremoveuser_response (req) {
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var uid = req.getData().uid,
        link = $('user-'+uid);
    if (link) {
        var tr = link.up('tr');
        Effect.SwitchOff(tr,{
            afterFinish: function() {tr.remove();}
        });
    }
}

$(document).observe('dom:loaded', function() {
    var links = $$('a.group-membership-removeuser');
    if (links) {
        links.invoke('observe','click', groupremoveuser);
    }
});