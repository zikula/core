// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

/**
 * Show information about all available components and instances
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function showinstanceinformation() {
    window.open (document.location.entrypoint + "?module=Permissions&type=admin&func=viewinstanceinfo",
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
    if(adminpermission != -1) {
        if($('permission_' + adminpermission)) {
            Element.addClassName('permission_' + adminpermission, 'adminpermission');
        } else {
            alert('admin permission not found!');
            adminpermission = -1;
        }
    }

    $A(document.getElementsByClassName('z-sortable', 'permissionlist')).each(
        function(node) 
        { 
            var thispermid = node.id.split('_')[1];
            if(lockadmin == 1 && thispermid == adminpermission) {
                // adminpermission found, locking required
                $('permission_' + thispermid).title = permissionlocked;
                Element.update('permdrag_' + thispermid, '(' + thispermid + ')');
                Element.addClassName('permission_' + thispermid, 'permlocked');
                Element.removeClassName('permission_' + thispermid, 'z-sortable');
            } else {
                // not adminpermission
                Element.addClassName('permission_' + thispermid, 'normalpermission');
                Element.addClassName('permission_' + thispermid, 'z-itemsort');
                Element.update('permdrag_' + thispermid, '(' + thispermid + ')');
                Element.removeClassName('modifyajax_' + thispermid, 'z-hide');
                Event.observe('modifyajax_' + thispermid, 'click', function(){permmodifyinit(thispermid)}, false);
            }
            // both admin and not adminpermissions
            Element.addClassName('insert_' + thispermid, 'z-hide');
            Element.addClassName('modify_' + thispermid, 'z-hide');
            Element.addClassName('delete_' + thispermid, 'z-hide');
            Element.removeClassName('testpermajax_' + thispermid, 'z-hide');
            
            Event.observe('testpermajax_' + thispermid, 'click', function(){permtestinit(thispermid)}, false);
        } );

    // set observers on all existing groups images
    $$('button.z-imagebutton').each(
    function(singlebutton) 
    {
        var permid = singlebutton.id.split('_')[1];
        switch(singlebutton.id.split('_')[0])
        {
            case "permeditsave":
                Event.observe('permeditsave_'   + permid, 'click', function(){ permmodify(permid)},       false);
                break;
            case "permeditdelete":
                Event.observe('permeditdelete_' + permid, 'click', function(){ permdelete(permid)},       false);
                break;
            case "permeditcancel":
                Event.observe('permeditcancel_' + permid, 'click', function(){ permmodifycancel(permid)}, false);
                break;
        }
    });

    Element.removeClassName('appendajax', 'z-hide'); 
    if($('permgroupfilterform')) {
        $('permgroupfilterform').action = 'javascript:void(0);'; 
        Element.remove('permgroupfiltersubmit', 'z-hide'); 
        Element.removeClassName('permgroupfiltersubmitajax', 'z-hide'); 
    }
    Element.remove('testpermsubmit');
    Element.removeClassName('testpermsubmitajax', 'z-hide');
    $('testpermform').action = 'javascript:void(0);'; 

    Element.removeClassName('permissiondraganddrophint', 'z-hide');

    Sortable.create("permissionlist",
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
    if(appending == false) {
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
    if(req.status != 200 ) { 
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
    Element.removeClassName('permission_' + json.pid, 'adminpermission');  
    Element.removeClassName('permission_' + json.pid, 'permlocked');
    
    // set initial values in input, hidden and select
    $('groupid_' + json.pid).value = json.gid;
    $('levelid_' + json.pid).value = json.level;
    $('sequence_' + json.pid).value = json.sequence;
    $('component_' + json.pid).value = json.component;
    $('instance_' + json.pid).value = json.instance;
    pnsetselectoption('group_' + json.pid, json.gid);
    pnsetselectoption('level_' + json.pid, json.level);

    // hide cancel icon for new permissions
    Element.addClassName('permeditcancel_' + json.pid, 'z-hide');
    // update delete icon to show cancel icon 
    Element.update('permeditdelete_' + json.pid, canceliconhtml);

    // update some innerHTML 
    Element.update('permdrag_' + json.pid, '(' + json.pid + ')');
    Element.update('permcomp_' + json.pid, json.component);
    Element.update('perminst_' + json.pid, json.instance);
    Element.update('permgroup_' + json.pid, json.groupname);
    Element.update('permlevel_' + json.pid, json.levelname);
       
	// Remove cloned handlers (otherwise we end up deleting/updating the admin rule!
    Event.stopObserving('modifyajax_' + json.pid, 'click');
    Event.stopObserving('testpermajax_' + json.pid, 'click');
    Event.stopObserving('permeditsave_' + json.pid, 'click');
    Event.stopObserving('permeditdelete_' + json.pid, 'click');
    Event.stopObserving('permeditcancel_' + json.pid, 'click');
    
	// add events
    Event.observe('modifyajax_' + json.pid, 'click', function(){permmodifyinit(json.pid)}, false);
    Event.observe('testpermajax_' + json.pid, 'click', function(){permtestinit(json.pid)}, false);
    Event.observe('permeditsave_' + json.pid, 'click',  function(){permmodify(json.pid)}, false);
    Event.observe('permeditdelete_' + json.pid, 'click',  function(){permdelete(json.pid)}, false);
    Event.observe('permeditcancel_' + json.pid, 'click',  function(){permmodifycancel(json.pid)}, false);

    // add class to make it sortable 
    Element.addClassName('permission_' + json.pid, 'z-sortable');
    Element.addClassName('permission_' + json.pid, 'normalpermission');
    Element.addClassName('permission_' + json.pid, 'z-itemsort');
    
    // remove class to make edit button visible
    Element.removeClassName('modifyajax_' + json.pid, 'z-hide');  
    Event.observe('modifyajax_' + json.pid, 'click', function(){permmodifyinit(json.pid)}, false);
    
    // turn on edit mode
    enableeditfields(json.pid);
    
    // we are ready now, make it visible
    Element.removeClassName('permission_' + json.pid, 'z-hide');
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
    Element.update('permissiontestinfo', '&nbsp;');
    Element.scrollTo('testpermform');
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
    if(req.status != 200 ) { 
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
    if(getmodifystatus(permid) == 0) {
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
    if(getmodifystatus(permid) == 0) {
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
    if(req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        showinfo();
        return;
    }
    
    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('permissionsauthid').value = json.authid;
    
    $('groupid_' + json.pid).value = json.gid;
    $('levelid_' + json.pid).value = json.level;
    
    Element.update('permgroup_' + json.pid, json.groupname);
    Element.update('permcomp_' + json.pid, json.component);
    $('editpermcomp_' + json.pid, json.component);
    Element.update('perminst_' + json.pid, json.instance);
    $('editperminst_' + json.pid, json.instance);
    Element.update('permlevel_' + json.pid, json.levelname);

    // show trascan icon for new permissions if necessary
    Element.removeClassName('permeditcancel_' + json.pid, 'z-hide');
    // update delete icon to show trashcan icon 
    Element.update('permeditdelete_' + json.pid, deleteiconhtml);

    // update the observer for cancel, it might lea to delete if this rule
    // has been appended before
    Event.observe('permeditcancel_' + json.pid, 'click',  function(){permmodifycancel(json.pid)}, false);
    
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
    if(confirm(confirmdeleteperm) && getmodifystatus(permid) == 0) {
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
    if(req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);

    pnupdateauthids(json.authid);
    $('permissionsauthid').value = json.authid;

    Element.remove('permission_' + json.pid);
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
    Element.addClassName('permgroup_' + permid, 'z-hide');
    Element.addClassName('permcomp_' + permid, 'z-hide');
    Element.addClassName('perminst_' + permid, 'z-hide');
    Element.addClassName('permlevel_' + permid, 'z-hide');
    Element.addClassName('permaction_' + permid, 'z-hide');
    Element.removeClassName('editpermgroup_' + permid, 'z-hide');
    Element.removeClassName('editpermcomp_' + permid, 'z-hide');
    Element.removeClassName('editperminst_' + permid, 'z-hide');
    Element.removeClassName('editpermlevel_' + permid, 'z-hide');
    Element.removeClassName('editpermaction_' + permid, 'z-hide');
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
    Element.addClassName('editpermgroup_' + permid, 'z-hide');
    Element.addClassName('editpermcomp_' + permid, 'z-hide');
    Element.addClassName('editperminst_' + permid, 'z-hide');
    Element.addClassName('editpermlevel_' + permid, 'z-hide');
    Element.addClassName('editpermaction_' + permid, 'z-hide');
    Element.removeClassName('permgroup_' + permid, 'z-hide');
    Element.removeClassName('permcomp_' + permid, 'z-hide');
    Element.removeClassName('perminst_' + permid, 'z-hide');
    Element.removeClassName('permlevel_' + permid, 'z-hide');
    Element.removeClassName('permaction_' + permid, 'z-hide');
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
    if(permid) {
        var perminfo = 'permissioninfo_' + permid;
        var perm = 'permissioncontent_' + permid;
        if(!Element.hasClassName(perminfo, 'z-hide')) {
            Element.update(perminfo, '&nbsp;');
            Element.addClassName(perminfo, 'z-hide');
            Element.removeClassName(perm, 'z-hide');
        } else {
            Element.update(perminfo, infotext);
            Element.addClassName(perm, 'z-hide');
            Element.removeClassName(perminfo, 'z-hide');
        }
    } else {
        $A(document.getElementsByClassName('permissioninfo')).each(function(perminfo){
            Element.update(perminfo, '&nbsp;');
            Element.addClassName(perminfo, 'z-hide');
        });    
        $A(document.getElementsByClassName('permissioncontent')).each(function(permcontent){
            Element.removeClassName(permcontent, 'z-hide');
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

    if(filtertype == 'g') {
        $('filterwarningcomponent').hide();
        if(filter == -1) {
            $('filterwarninggroup').hide();
        } else {
            $('filterwarninggroup').show();
        }
        $A(document.getElementsByClassName('z-sortable')).each(
            function(el) 
            { 
                permid = el.id.split('_')[1];
                if(filter == -1) {
                    // show all groups - reset view
                    Element.show('permission_' + permid);
                } else {
                    groupid = $F('groupid_' + permid);
                    if(groupid != filter && groupid != -1) {
                        Element.hide('permission_' + permid);
                    } else {
                        Element.show('permission_' + permid);
                    }
                } 
            });
    } else if (filtertype == 'c') {
        $('filterwarninggroup').hide();
        if(filter == -1) {
            $('filterwarningcomponent').hide();
        } else {
            $('filterwarningcomponent').show();
        }
        $A(document.getElementsByClassName('z-sortable')).each(
            function(el) 
            { 
                // show all permissions with .* and filter
                permid = el.id.split('_')[1];
                if(filter == -1) {
                    // show all components - reset view
                    Element.show('permission_' + permid);
                } else {
                    permcomp = $F('comp_' + permid);
                    if(permcomp.indexOf(filter) == 0 || permcomp == '.*') {
                        Element.show('permission_' + permid);
                    } else {
                        Element.hide('permission_' + permid);
                    }
                } 
            });
    }
    if(filter == -1) {
        Element.hide('filterwarning');
    } else {
        Element.show('filterwarning');
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
    Element.update('permissiontestinfo', testingpermission);
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
    Element.update('permissiontestinfo', '&nbsp;');
    if(req.status != 200 ) { 
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('permissiontestinfo', json.testresult);
}
