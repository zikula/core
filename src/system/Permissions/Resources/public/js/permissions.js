// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * Inits the ajax stuff: show ajax buttons, remove non ajax buttons etc.
 *
 *@params none;
 *@return none;
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
 */
function permappend()
{
    if (appending == false) {
        appending = true;
        new Zikula.Ajax.Request(
            "index.php?module=Permissions&type=ajax&func=createpermission",
            {
                onComplete: permappend_response
            });
    }
}

/**
 * Ajax response function for appending a new permission: adds a new li,
 * updates fields and makes them visible. More important: renames all ids
 *
 *@params req response from ajax call;
 *@return none;
 */
function permappend_response(req)
{
    appending = false;
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }
    var data = req.getData();

    // copy new permission li from permission_1.
    var newperm = $('permission_1').cloneNode(true);

    // update the ids. We use the getElementsByTagName function from
    // protoype for this. The 6 tags here cover everything in a single li
    // that has a unique id
    newperm.id   = 'permission_' + data.pid;
    $A(newperm.getElementsByTagName('div')).each(function(node){ node.id = node.id.split('_')[0] + '_' + data.pid; });
    $A(newperm.getElementsByTagName('span')).each(function(node){ node.id = node.id.split('_')[0] + '_' + data.pid; });
    $A(newperm.getElementsByTagName('input')).each(function(node){ node.id = node.id.split('_')[0] + '_' + data.pid; node.value = ''; });
    $A(newperm.getElementsByTagName('select')).each(function(node){ node.id = node.id.split('_')[0] + '_' + data.pid; });
    $A(newperm.getElementsByTagName('button')).each(function(node){ node.id = node.id.split('_')[0] + '_' + data.pid; });
    $A(newperm.getElementsByTagName('textarea')).each(function(node){ node.id = node.id.split('_')[0] + '_' + data.pid; });

    // append new perm to the permission list
    $('permissionlist').appendChild(newperm);

    // remove adminpermission & permlocked classes
    $('permission_' + data.pid).removeClassName('adminpermission');
    $('permission_' + data.pid).removeClassName('permlocked');

    // set initial values in input, hidden and select
    $('groupid_' + data.pid).value = data.gid;
    $('levelid_' + data.pid).value = data.level;
    $('sequence_' + data.pid).value = data.sequence;
    $('component_' + data.pid).value = data.component;
    $('instance_' + data.pid).value = data.instance;
    Zikula.setselectoption('group_' + data.pid, data.gid);
    Zikula.setselectoption('level_' + data.pid, data.level);

    // hide cancel icon for new permissions
    $('permeditcancel_' + data.pid).addClassName('z-hide');
    // update delete icon to show cancel icon
    $('permeditdelete_' + data.pid).update(canceliconhtml);

    // update some innerHTML
    $('permdrag_' + data.pid).update('(' + data.pid + ')');
    $('permcomp_' + data.pid).update(data.component);
    $('perminst_' + data.pid).update(data.instance);
    $('permgroup_' + data.pid).update(data.groupname);
    $('permlevel_' + data.pid).update(data.levelname);

    // Remove cloned handlers (otherwise we end up deleting/updating the admin rule!
    $('modifyajax_' + data.pid).stopObserving('click');
    $('testpermajax_' + data.pid).stopObserving('click');
    $('permeditsave_' + data.pid).stopObserving('click');
    $('permeditdelete_' + data.pid).stopObserving('click');
    $('permeditcancel_' + data.pid).stopObserving('click');

    // add events
    $('modifyajax_' + data.pid).observe('click', function() { permmodifyinit(data.pid); });
    $('testpermajax_' + data.pid).observe('click', function() { permtestinit(data.pid); });
    $('permeditsave_' + data.pid).observe('click',  function() { permmodify(data.pid); });
    $('permeditdelete_' + data.pid).observe('click',  function() { permdelete(data.pid); });
    $('permeditcancel_' + data.pid).observe('click',  function() { permmodifycancel(data.pid); });

    // add class to make it sortable
    $('permission_' + data.pid).addClassName('z-sortable');
    $('permission_' + data.pid).addClassName('normalpermission');
    $('permission_' + data.pid).addClassName('z-itemsort');

    // remove class to make edit button visible
    $('modifyajax_' + data.pid).removeClassName('z-hide');
    $('modifyajax_' + data.pid).observe('click', function() { permmodifyinit(data.pid); });

    // turn on edit mode
    enableeditfields(data.pid);

    // we are ready now, make it visible
    $('permission_' + data.pid).removeClassName('z-hide');
    new Effect.Highlight('permission_' + data.pid, { startcolor: '#ffff99', endcolor: '#ffffff' });

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
 */
function permtestinit(permid)
{
    $('test_user').value = '';
    $('test_component').value = $('permcomp_' + permid).innerHTML;
    $('test_instance').value  = $('perminst_' + permid).innerHTML;
    Zikula.setselectoption('test_level', $F('levelid_' + permid));
    $('permissiontestinfo').update('&nbsp;');
    $('testpermform').scrollTo();
}

/**
 * Stores the new sort order. This function gets called automatically
 * from the Sortable when a 'drop' action has been detected
 *
 *@params none;
 *@return none;
 */
function sortorderchanged()
{
    // make the adminpermission sortable for a moment
    // this is necessary to get the correct result from Sortable.serialize because otherwise
    // the adminpermission is left out and gets sequence value of 0 which puts it on top of the
    // list
    $('permission_' + adminpermission).addClassName('z-sortable');
    var pars = Sortable.serialize('permissionlist', { 'name': 'permorder' });
    // remove sortable class from adminpermission
    $('permission_' + adminpermission).removeClassName('z-sortable');

    new Zikula.Ajax.Request(
        "index.php?module=Permissions&type=ajax&func=changeorder",
        {
            parameters: pars,
            onComplete: sortorderchanged_response
        });
}

/**
 * Ajax response function for updating new sort order: cleanup
 *
 *@params none;
 *@return none;
 */
function sortorderchanged_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }
    Zikula.recolor('permissionlist', 'permlistheader');
}

/**
 * Start edit of permissions: hide/show the neceaasty fields
 *
 *@params permid the permission id;
 *@return none;
 */
function permmodifyinit(permid)
{
    if (getmodifystatus(permid) == 0) {
        Zikula.setselectoption('group_' + permid, $F('groupid_' + permid));
        Zikula.setselectoption('level_' + permid, $F('levelid_' + permid));

        enableeditfields(permid);
    }
}

/**
 * Cancel permission modification
 *
 *@params none;
 *@return none;
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
        var pars = {
               pid: permid,
               gid: $F('group_' + permid),
               seq: $F('sequence_' + permid),
               comp: $F('component_' + permid),
               inst: $F('instance_' + permid),
               level: $F('level_' + permid)
        }
        new Zikula.Ajax.Request(
            "index.php?module=Permissions&type=ajax&func=updatepermission",
            {
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
 */
function permmodify_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        showinfo();
        return;
    }
    var data = req.getData();

    $('groupid_' + data.pid).value = data.gid;
    $('levelid_' + data.pid).value = data.level;

    $('permgroup_' + data.pid).update(data.groupname);
    $('permcomp_' + data.pid).update(data.component);
    $('editpermcomp_' + data.pid, data.component);
    $('perminst_' + data.pid).update(data.instance);
    $('editperminst_' + data.pid, data.instance);
    $('permlevel_' + data.pid).update(data.levelname);

    // show trascan icon for new permissions if necessary
    $('permeditcancel_' + data.pid).removeClassName('z-hide');
    // update delete icon to show trashcan icon
    $('permeditdelete_' + data.pid).update(deleteiconhtml);

    // update the observer for cancel, it might lea to delete if this rule
    // has been appended before
    $('permeditcancel_' + data.pid).observe('click', function() { permmodifycancel(data.pid); });

    setmodifystatus(data.pid, 0);
    showinfo(data.pid);
}

/**
 * Delete a permission
 *
 *@params permid the permission id;
 *@return none;
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
        var pars = {pid: permid};
        new Zikula.Ajax.Request(
            "index.php?module=Permissions&type=ajax&func=deletepermission",
            {
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
 */
function permdelete_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }
    var data = req.getData();

    $('permission_' + data.pid).remove();
}

/**
 * Show/hide all fields needed for modifying a permission
 *
 *@params permid the permission id;
 *@params newperm true if we are adding a new permission;
 *@return none;
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
 */
function performpermissiontest()
{
    $('permissiontestinfo').update(testingpermission);
    var pars = Form.serialize('testpermform');
    Form.disable('testpermform');
    new Zikula.Ajax.Request(
        "index.php?module=Permissions&type=ajax&func=testpermission",
        {
            parameters: pars,
            onComplete: performpermissiontest_response
        }
    );
}

/**
 * Ajax response function for the permission test: show the result
 *
 *@params none;
 *@return none;
 */
function performpermissiontest_response(req)
{
    Form.enable('testpermform');
    $('permissiontestinfo').update('&nbsp;');
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }
    var data = req.getData();
    $('permissiontestinfo').update(data.testresult);
}
