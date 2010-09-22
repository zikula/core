// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

/**
 * Show information about all available components and instances
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function showinstanceinformation() {
    window.open (Zikula.Config.entrypoint + "?module=Permissions&type=admin&func=viewinstanceinfo",
                 "Instance_Information","toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=no,copyhistory=no,width=600,height=300");
}

/**
 * Inits the ajax stuff: show ajax buttons, remove non ajax buttons etc.
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function permissioninit()
{
    appending = false;

    // craigh specialsuperusability extension :-)
    deleteiconhtml = $('permeditdelete_' + adminpermission).innerHTML;
    canceliconhtml = $('permeditcancel_' + adminpermission).innerHTML;
     
    /* highlight admin permission */
    if (adminpermission != -1) {
        if ($('permission_' + adminpermission)) {
            $('permission_' + adminpermission).addClassName('adminpermission');
        } else {
            alert('admin permission not found!');
            adminpermission = -1;
        }
    }

    $A(document.getElementsByClassName('z-sortable', 'permissionlist')).each(
        function(node) 
        { 
            var thispermid = node.id.split('_')[1];
            if (lockadmin == 1 && thispermid == adminpermission) {
                // adminpermission found, locking required
                $('permission_' + thispermid).title = permissionlocked;
                $('permdrag_' + thispermid).update('(' + thispermid + ')');
                $('permission_' + thispermid).addClassName('permlocked');
                $('permission_' + thispermid).removeClassName('z-sortable');
            } else {
                // not adminpermission
                $('permission_' + thispermid).addClassName('normalpermission');
                $('permission_' + thispermid).addClassName('z-itemsort');
                $('permdrag_' + thispermid).update('(' + thispermid + ')');
                $('modifyajax_' + thispermid).removeClassName('z-hide');
                $('modifyajax_' + thispermid).observe('click', function() { permmodifyinit(thispermid); });
            }
            // both admin and not adminpermissions
            $('insert_' + thispermid).addClassName('z-hide');
            $('modify_' + thispermid).addClassName('z-hide');
            $('delete_' + thispermid).addClassName('z-hide');
            $('testpermajax_' + thispermid).removeClassName('z-hide');

            $('testpermajax_' + thispermid).observe('click', function() { permtestinit(thispermid); });
        } );

    // set observers on all existing groups images
    $$('button.z-imagebutton').each(
    function(singlebutton) 
    {
        var permid = singlebutton.id.split('_')[1];
        switch(singlebutton.id.split('_')[0])
        {
            case 'permeditsave':
                $('permeditsave_'   + permid).observe('click', function() { permmodify(permid); });
                break;
            case 'permeditdelete':
                $('permeditdelete_' + permid).observe('click', function() { permdelete(permid); });
                break;
            case 'permeditcancel':
                $('permeditcancel_' + permid).observe('click', function() { permmodifycancel(permid); });
                break;
        }
    });

    $('appendajax').removeClassName('z-hide'); 
    if ($('permgroupfilterform')) {
        $('permgroupfilterform').action = 'javascript:void(0);'; 
        $('permgroupfiltersubmit').remove('z-hide'); 
        $('permgroupfiltersubmitajax').removeClassName('z-hide'); 
    }
    $('testpermsubmit').remove();
    $('testpermsubmitajax').removeClassName('z-hide');
    $('testpermform').action = 'javascript:void(0);'; 

    $('permissiondraganddrophint').removeClassName('z-hide');

    Sortable.create('permissionlist',
                    { 
                      only: 'z-sortable',
                      onUpdate: sortorderchanged
                    });
    
}

/**
 * Append a new permission at the end of the list
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function permappend()
{
    if (appending == false) {
        appending = true;
        var pars = "module=Permissions&func=createpermission&authid=" + $F('permissionsauthid');
        var myAjax = new Ajax.Request(
            "ajax.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: permappend_response
            });
    }
}

/**
 * Ajax response function for appending a new permission: adds a new li,
 * updates fields and makes them visible. More important: renames all ids
 *
 *@params req reponse from ajax call;
 *@return none;
 *@author Frank Schummertz
 */
function permappend_response(req)
{
    appending = false;
    if (req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('permissionsauthid').value = json.authid;
    
    // copy new permission li from permission_1.
    var newperm = $('permission_1').cloneNode(true);

    // update the ids. We use the getElementsByTagName function from
    // protoype for this. The 6 tags here cover everything in a single li
    // that has a unique id
    newperm.id   = 'permission_' + json.pid; 
    $A(newperm.getElementsByTagName('div')).each(function(node){ node.id = node.id.split('_')[0] + '_' + json.pid; });
    $A(newperm.getElementsByTagName('span')).each(function(node){ node.id = node.id.split('_')[0] + '_' + json.pid; });
    $A(newperm.getElementsByTagName('input')).each(function(node){ node.id = node.id.split('_')[0] + '_' + json.pid; node.value = ''; });
    $A(newperm.getElementsByTagName('select')).each(function(node){ node.id = node.id.split('_')[0] + '_' + json.pid; });
    $A(newperm.getElementsByTagName('button')).each(function(node){ node.id = node.id.split('_')[0] + '_' + json.pid; });
    $A(newperm.getElementsByTagName('textarea')).each(function(node){ node.id = node.id.split('_')[0] + '_' + json.pid; });

    // append new perm to the permission list
    $('permissionlist').appendChild(newperm);

    // remove adminpermission & permlocked classes
    $('permission_' + json.pid).removeClassName('adminpermission');  
    $('permission_' + json.pid).removeClassName('permlocked');

    // set initial values in input, hidden and select
    $('groupid_' + json.pid).value = json.gid;
    $('levelid_' + json.pid).value = json.level;
    $('sequence_' + json.pid).value = json.sequence;
    $('component_' + json.pid).value = json.component;
    $('instance_' + json.pid).value = json.instance;
    pnsetselectoption('group_' + json.pid, json.gid);
    pnsetselectoption('level_' + json.pid, json.level);

    // hide cancel icon for new permissions
    $('permeditcancel_' + json.pid).addClassName('z-hide');
    // update delete icon to show cancel icon 
    $('permeditdelete_' + json.pid).update(canceliconhtml);

    // update some innerHTML 
    $('permdrag_' + json.pid).update('(' + json.pid + ')');
    $('permcomp_' + json.pid).update(json.component);
    $('perminst_' + json.pid).update(json.instance);
    $('permgroup_' + json.pid).update(json.groupname);
    $('permlevel_' + json.pid).update(json.levelname);

    // Remove cloned handlers (otherwise we end up deleting/updating the admin rule!
    $('modifyajax_' + json.pid).stopObserving('click');
    $('testpermajax_' + json.pid).stopObserving('click');
    $('permeditsave_' + json.pid).stopObserving('click');
    $('permeditdelete_' + json.pid).stopObserving('click');
    $('permeditcancel_' + json.pid).stopObserving('click');

    // add events
    $('modifyajax_' + json.pid).observe('click', function() { permmodifyinit(json.pid); });
    $('testpermajax_' + json.pid).observe('click', function() { permtestinit(json.pid); });
    $('permeditsave_' + json.pid).observe('click',  function() { permmodify(json.pid); });
    $('permeditdelete_' + json.pid).observe('click',  function() { permdelete(json.pid); });
    $('permeditcancel_' + json.pid).observe('click',  function() { permmodifycancel(json.pid); });

    // add class to make it sortable 
    $('permission_' + json.pid).addClassName('z-sortable');
    $('permission_' + json.pid).addClassName('normalpermission');
    $('permission_' + json.pid).addClassName('z-itemsort');

    // remove class to make edit button visible
    $('modifyajax_' + json.pid).removeClassName('z-hide');  
    $('modifyajax_' + json.pid).observe('click', function() { permmodifyinit(json.pid); });
    
    // turn on edit mode
    enableeditfields(json.pid);
    
    // we are ready now, make it visible
    $('permission_' + json.pid).removeClassName('z-hide');
    new Effect.Highlight('permission_' + json.pid, { startcolor: '#ffff99', endcolor: '#ffffff' });
    
    // update the sortable
    Sortable.create("permissionlist",
                    { 
                      only: 'z-sortable',
                      constraint: false,
                      onUpdate: sortorderchanged
                    });
}

/**
 * Copies the component, instance and level to the permission test form
 *
 *@params permid thie permission id;
 *@return none;
 *@author Frank Schummertz
 */
function permtestinit(permid)
{
    $('test_user').value = '';
    $('test_component').value = $('permcomp_' + permid).innerHTML;
    $('test_instance').value  = $('perminst_' + permid).innerHTML;
    pnsetselectoption('test_level', $F('levelid_' + permid));
    $('permissiontestinfo').update('&nbsp;');
    $('testpermform').scrollTo();
}

/**
 * Stores the new sort order. This function gets called automatically
 * from the Sortable when a 'drop' action has been detected
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function sortorderchanged()
{
    // make the adminpermission sortable for a moment
    // this is necessary to get the correct result from Sortable.serialize because otherwise
    // the adminpermission is left out and gets sequence value of 0 which puts it on top of the
    // list
    $('permission_' + adminpermission).addClassName('z-sortable');    
    var pars = "module=Permissions&func=changeorder&authid=" + $F('permissionsauthid') + "&"
               + Sortable.serialize('permissionlist', { 'name': 'permorder' });
    // remove sortable class from adminpermission
    $('permission_' + adminpermission).removeClassName('z-sortable');    

    var myAjax = new Ajax.Request(
        "ajax.php", 
        {
            method: 'get', 
            parameters: pars, 
            onComplete: sortorderchanged_response
        });
}

/**
 * Ajax response function for updating new sort order: cleanup
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function sortorderchanged_response(req)
{
    if (req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }

    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('permissionsauthid').value = json.authid;
    
    pnrecolor('permissionlist', 'permlistheader');
}
   
/**
 * Start edit of permissions: hide/show the neceaasty fields
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function permmodifyinit(permid)
{
    if (getmodifystatus(permid) == 0) {
        pnsetselectoption('group_' + permid, $F('groupid_' + permid));
        pnsetselectoption('level_' + permid, $F('levelid_' + permid));

        enableeditfields(permid);
    }
}

/**
 * Cancel permission modification
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function permmodifycancel(permid)
{
    disableeditfields(permid);
}

/**
 * Store updated permission in the database
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function permmodify(permid)
{
    if (permid==adminpermission && lockadmin==1) {
        return;
    }
    disableeditfields(permid);
    if (getmodifystatus(permid) == 0) {
        setmodifystatus(permid, 1);
        showinfo(permid, updatingpermission);
        // store via ajax
        var pars = "module=Permissions&func=updatepermission&authid="
                   + $F('permissionsauthid')
                   + '&pid=' + permid
                   + '&gid=' + $F('group_' + permid)
                   + '&seq=' + $F('sequence_' + permid)
                   + '&comp=' + encodeURIComponent($F('component_' + permid))
                   + '&inst=' + encodeURIComponent($F('instance_' + permid))
                   + '&level=' + $F('level_' + permid)
        var myAjax = new Ajax.Request(
            "ajax.php", 
            {
                method: 'post', 
                parameters: pars, 
                onComplete: permmodify_response
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
function permmodify_response(req)
{
    if (req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        showinfo();
        return;
    }
    
    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('permissionsauthid').value = json.authid;
    
    $('groupid_' + json.pid).value = json.gid;
    $('levelid_' + json.pid).value = json.level;
    
    $('permgroup_' + json.pid).update(json.groupname);
    $('permcomp_' + json.pid).update(json.component);
    $('editpermcomp_' + json.pid, json.component);
    $('perminst_' + json.pid).update(json.instance);
    $('editperminst_' + json.pid, json.instance);
    $('permlevel_' + json.pid).update(json.levelname);

    // show trascan icon for new permissions if necessary
    $('permeditcancel_' + json.pid).removeClassName('z-hide');
    // update delete icon to show trashcan icon 
    $('permeditdelete_' + json.pid).update(deleteiconhtml);

    // update the observer for cancel, it might lea to delete if this rule
    // has been appended before
    $('permeditcancel_' + json.pid).observe('click', function() { permmodifycancel(json.pid); });

    setmodifystatus(json.pid, 0);
    showinfo(json.pid);
}

/**
 * Delete a permission
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function permdelete(permid)
{
    if (permid==adminpermission && lockadmin==1) {
        return;
    }
    if (confirm(confirmdeleteperm) && getmodifystatus(permid) == 0) {
        showinfo(permid, deletingpermission);
        setmodifystatus(permid, 1);
        // delete via ajax
        var pars = "module=Permissions&func=deletepermission&authid="
                   + $F('permissionsauthid')
                   + '&pid=' + permid;
        var myAjax = new Ajax.Request(
            "ajax.php", 
            {
                method: 'get', 
                parameters: pars, 
                onComplete: permdelete_response
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
function permdelete_response(req)
{
    if (req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('permissionsauthid').value = json.authid;

    $('permission_' + json.pid).remove();
}

/**
 * Show/hide all fields needed for modifying a permission
 *
 *@params permid the permission id;
 *@params newperm true if we are adding a new permission;
 *@return none;
 *@author Frank Schummertz
 */
function enableeditfields(permid)
{
    $('permgroup_' + permid).addClassName('z-hide');
    $('permcomp_' + permid).addClassName('z-hide');
    $('perminst_' + permid).addClassName('z-hide');
    $('permlevel_' + permid).addClassName('z-hide');
    $('permaction_' + permid).addClassName('z-hide');
    $('editpermgroup_' + permid).removeClassName('z-hide');
    $('editpermcomp_' + permid).removeClassName('z-hide');
    $('editperminst_' + permid).removeClassName('z-hide');
    $('editpermlevel_' + permid).removeClassName('z-hide');
    $('editpermaction_' + permid).removeClassName('z-hide');
}

/**
 * Show/hide all fields needed for not modifying a permission
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function disableeditfields(permid)
{
    $('editpermgroup_' + permid).addClassName('z-hide');
    $('editpermcomp_' + permid).addClassName('z-hide');
    $('editperminst_' + permid).addClassName('z-hide');
    $('editpermlevel_' + permid).addClassName('z-hide');
    $('editpermaction_' + permid).addClassName('z-hide');
    $('permgroup_' + permid).removeClassName('z-hide');
    $('permcomp_' + permid).removeClassName('z-hide');
    $('perminst_' + permid).removeClassName('z-hide');
    $('permlevel_' + permid).removeClassName('z-hide');
    $('permaction_' + permid).removeClassName('z-hide');
}

/**
 * Reads a hidden field that holds the modification status
 *
 *@params permid the permission id;
 *@return 1 if modification is in progress, otherwise 0;
 *@author Frank Schummertz
 */
function getmodifystatus(permid)
{
    return $F('modifystatus_' + permid);
}

/**
 * Set the hidden field the holds the modification status
 *
 *@params permid the permission id;
 *@return none;
 *@author Frank Schummertz
 */
function setmodifystatus(permid, newvalue)
{
    $('modifystatus_' + permid).value = newvalue;
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
function showinfo(permid, infotext)
{
    if (permid) {
        var perminfo = $('permissioninfo_' + permid);
        var perm = $('permissioncontent_' + permid);
        if (!perminfo.hasClassName('z-hide')) {
            perminfo.update('&nbsp;');
            perminfo.addClassName('z-hide');
            perm.removeClassName('z-hide');
        } else {
            perminfo.update(infotext);
            perm.addClassName('z-hide');
            perminfo.removeClassName('z-hide');
        }
    } else {
        $A(document.getElementsByClassName('permissioninfo')).each(function(perminfo) {
            $(perminfo).update('&nbsp;');
            $(perminfo).addClassName('z-hide');
        });    
        $A(document.getElementsByClassName('permissioncontent')).each(function(permcontent) {
            $(permcontent).removeClassName('z-hide');
        });    
    }
}    

/**
 * Filters the permission list as requested
 * 'All groups' will always been displayed
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function permgroupfilter()
{
    var filtergroupid = $F('permgrp');
    filtertype = filtergroupid.split('+')[0];
    filter = filtergroupid.split('+')[1];

    if (filtertype == 'g') {
        $('filterwarningcomponent').hide();
        if (filter == -1) {
            $('filterwarninggroup').hide();
        } else {
            $('filterwarninggroup').show();
        }
        $A(document.getElementsByClassName('z-sortable')).each(
            function(el) 
            { 
                permid = el.id.split('_')[1];
                if (filter == -1) {
                    // show all groups - reset view
                    $('permission_' + permid).show();
                } else {
                    groupid = $F('groupid_' + permid);
                    if (groupid != filter && groupid != -1) {
                        $('permission_' + permid).hide();
                    } else {
                        $('permission_' + permid).show();
                    }
                } 
            });
    } else if (filtertype == 'c') {
        $('filterwarninggroup').hide();
        if (filter == -1) {
            $('filterwarningcomponent').hide();
        } else {
            $('filterwarningcomponent').show();
        }
        $A(document.getElementsByClassName('z-sortable')).each(
            function(el) 
            { 
                // show all permissions with .* and filter
                permid = el.id.split('_')[1];
                if (filter == -1) {
                    // show all components - reset view
                    $('permission_' + permid).show();
                } else {
                    permcomp = $F('comp_' + permid);
                    if (permcomp.indexOf(filter) == 0 || permcomp == '.*') {
                        $('permission_' + permid).show();
                    } else {
                        $('permission_' + permid).hide();
                    }
                } 
            });
    }
    if (filter == -1) {
        $('filterwarning').hide();
    } else {
        $('filterwarning').show();
    }
}

/**
 * Test a permission for a user
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function performpermissiontest()
{
    $('permissiontestinfo').update(testingpermission);
    var pars = "module=Permissions&func=testpermission&"
               + Form.serialize('testpermform');
    Form.disable('testpermform');
    var myAjax = new Ajax.Request(
        "ajax.php", 
        {
            method: 'get', 
            parameters: pars, 
            onComplete: performpermissiontest_response
        }); 
    
}

/**
 * Ajax response function for the permission test: show the result
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function performpermissiontest_response(req)
{
    Form.enable('testpermform');
    $('permissiontestinfo').update('&nbsp;');
    if (req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    $('permissiontestinfo').update(json.testresult);
}
