{ajaxheader ui=true}
{pageaddvarblock}
<script type="text/javascript">
    var accordion;
    document.observe("dom:loaded", function() {
        accordion = new Zikula.UI.Accordion('adminhelp');
    });
</script>
{/pageaddvarblock}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="help" size="small"}
    <h3>{gt text="Help"}</h3>
</div>

<div id="adminhelp">
    <h4 id="view" class="z-acc-header">{gt text="Module categories list"}</h4>
    <div class="z-acc-content">
        <p>{gt text="Displays the list view of module categories included within the category tab menu. In the 'Actions' column on the right, you can choose to edit or delete a module category"}</p>
        <ul>
            <li>
                <a id="modify"></a>
                <strong>{gt text="Edit"}</strong>: {gt text="Lets you edit the name and description of an existing module category."}
            </li>
            <li>
                <a id="delete"></a>
                <strong>{gt text="Delete"}</strong>: {gt text="Lets you delete a module category from the tab menu. You will be prompted for confirmation before the category is deleted."}
            </li>
        </ul>
    </div>

    <h4 id="new" class="z-acc-header">{gt text="Create new module category"}</h4>
    <div class="z-acc-content">
        <p>{gt text="Lets you add a new category to the module categories tab list. You can edit the following:"}</p>
        <ul>
            <li>
                <strong>{gt text="Name"}</strong>: {gt text="Enter the name to be displayed within the categories tab menu."}
            </li>
            <li>
                <strong>{gt text="Description"}</strong>: {gt text="Enter an informative description to be displayed after the category name, when this category is displayed in the Administration panel."}
            </li>
        </ul>
    </div>

    <h4 id="modifyconfig" class="z-acc-header">{gt text="Settings"}</h4>
    <div class="z-acc-content">
        <p>{gt text="The 'Settings' page contains three sections: 'General settings', 'Display settings' and 'Modules categorisation'. Each is covered below."}</p>
    </div>

    <h4 id="generalsettings" class="z-acc-header">{gt text="General settings"}</h4>
    <div class="z-acc-content">
        <p>{gt text="There is currently only one option in the General settings section."}</p>
        <ul>
            <li><strong>{gt text="Ignore check for installer"}</strong>:
            {gt text="This option enables you to disable the Security analyser's check to see if the Installer is present in your site's root directory. Preferably, you should leave the check enabled, as a security breach could easily occur if you omit to remove the Installer's components after completing installation."}</li>
        </ul>
    </div>

    <h4 id="displaysettings" class="z-acc-header">{gt text="Display settings"}</h4>
    <div class="z-acc-content">
        <p>{gt text="There are six display settings."}</p>
        <ul>
            <li><strong>{gt text="Display icons"}:</strong> {gt text="When checked, this option causes graphic icons to be displayed for each module in the Administration panel."}</li>
            <li><strong>{gt text="Modules per page"}:</strong> {gt text="Enter a whole number for the number of modules to be displayed on each page of the Administration panel."}</li>
            <li><strong>{gt text="Modules per row"}:</strong> {gt text="Enter a whole number for the number of modules to be displayed on each line of the Administration panel."}</li>
            <li><strong>{gt text="Theme to use"}:</strong> {gt text="Open the dropdown list and choose the theme to be applied to the Administration panel."}</li>
            <li><strong>{gt text="Style sheet to use"}:</strong> {gt text="Open the dropdown list and choose the style sheet to be used to render the Administration panel."}</li>
            <li><strong>{gt text="Category initially selected"}:</strong> {gt text="Open the dropdown list and choose the modules category to be shown when the Administration panel first displays."}</li>
        </ul>
    </div>

    <h4 id="categoryconfiguration" class="z-acc-header">{gt text="Modules categorisation"}</h4>
    <div class="z-acc-content">
        <p>{gt text="In the Modules categorisation section, you can choose two things:"}</p>
        <ul>
            <li><strong>{gt text="Default category for newly-added modules"}:</strong> {gt text="Open the dropdown list and choose the module category in which to put all newly-installed and activated modules."}</li>
            <li><strong>{gt text="Modules categorisation"}:</strong> {gt text="You will see listed all the modules currently installed and activated. Open the dropdown list beside each module and choose the module category in which that module should be placed."}</li>
        </ul>
    </div>
</div>
<br />

<fieldset>
    <legend>{gt text="Messages you might see"}</legend>
    <h5 id="securitywarnings">{gt text="Security analyser warnings"}</h5>
    <p>{gt text='In the Administration panel, you will see security warnings displayed if the Security analyser detects potential security vulnerabilities in your site\'s installation. If you see no warning box then it means that no vulnerabilities have been found. Below are explanations for each vulnerability possibly identified.'}</p>

    <h5 id="admin_psakwarning">{gt text="You see the message"}: {gt text="Stop, please! The Swiss Army Knife tool ('psak.php' file) is in the site root, but must be removed before you can access the site admin panel. Moreover, for your information, this tool is now deprecated in favor of the Zikula recovery console ('zrc.php')."}</h5>
    <p>{gt text="The Zikula recovery console tool (a file called 'zrc.php') is a useful utility for resolving certain problems, but should not be left in any directory accessible from the Internet, because this leaves an easy opportunity for a security breach. If the Security analyser detects this vulnerability, you will have to remove 'zrc.php' before you can access the Administration panel."}</p>

    <h5 id="admin_installwarning">{gt text="You see the message"}: {gt text="Stop, please! The installer file 'install.php' and directory 'install' are in the site root, but must be removed before you can access the site admin panel."}</h5>
    <p>{gt text='After completing the installation process, it is very important to delete the Installer\'s file \'install.php\' and directory \'install\' from the site root directory, especially if it is on-line on the Internet, because otherwise there is an opportunity for a security breach. If the Security analyser detects this vulnerability, you will have to remove them before you can access the Administration panel.'}</p>

    <h5 id="admin_configphpwarning">{gt text="You see the message"}: {gt text="Configuration file 'config/config.php' is writeable, but should be read-only (chmod 400, 440 or at worst 444)."}</h5>
    <p>{gt text='When a site is originally installed, the Installer script creates a configuration file called \'config.php\' that is located in the sub-directory \'config\' within the site\'s root directory. It should not be left writeable, because this leaves an opportunity for a security breach. You should set the file\'s permissions to read-only (chmod 400, 440 or at worst 444).'}</p>

    <h5 id="admin_upgrade_php_warning">{gt text="You see the message"}: {gt text="Installer file 'upgrade.php' is in the site root, but should be removed."}</h5>
    <p>{gt text="The file 'upgrade.php' is used by the Installer script if a previously-existing site is upgraded to a more-recent version. Once the new site is working properly, you should delete this file, as it is no longer required and could make a security breach possible."}</p>

    <h5 id="admin_magic_quotes_warning">{gt text="You see the message"}: {gt text="PHP 'magic_quotes_gpc' setting is ON, but should be OFF."}</h5>
    <p>{gt text='In some hosting environments, the PHP setting \'magic_quotes_gpc\' may be set to ON. However, this creates an opportunity for security breaches. If the site is accessible from the Internet, you are strongly recommended to ensure that \'magic_quotes_gpc\' is set to OFF.'}</p>

    <h5 id="admin_register_globals_warning">{gt text="You see the message"}: {gt text="PHP 'register_globals' setting is ON, but should be OFF."}</h5>
    <p>{gt text='In some hosting environments, the PHP setting \'register_globals\' may be set to ON. However, this can create an opportunity for security breaches. If the site is accessible from the Internet, you are strongly recommended to ensure that \'register_globals\' is set to OFF.'}</p>

    <h5 id="admin_ztemp_htaccess_warning">{gt text="You see the message"}: {gt text="There is no '.htaccess' file in the temporary directory ('/ztemp' or other location), but one should be present."}</h5>
    <p>{gt text='If properly installed, a site will include an \'.htaccess\' file in the site\'s temporary directory (which is called \'/ztemp\', unless another location is used). However, some methods used for transferring the package\'s files to a web space may lead to this file not being transferred. In this case, you are strongly recommended to create one (and to ensure that \'.htaccess\' files exist in certain other directories, too).'}</p>

    <h5 id="admin_phpids_warning">{gt text="You see the message"}: {gt text="PHPIDS with the Security Center is not activated, but preferably should be."}</h5>
    <p>{gt text="PHPIDS is a feature of the Security center module that enhances the security of a site, and the Security analyser has detected that it is not enabled. You are recommended to ensure that it is enabled. You will also see this message if the Security center module is not installed or is not activated. For more information about this, please consult the Security center on-line help."}</p>

    <h5 id="admin_idssoftblock_warning">{gt text="You see the message"}: {gt text="PHPIDS is activated, but requests are NOT blocked."}</h5>
    <p>{gt text="PHPIDS is enabled and you have set the Block action to 'Warn only'. Thus requests are not blocked. This is useful only for debugging the PHPIDS ruleset in order to verify correct operation."}</p>

    <h5 id="admin_security_center_warning2">{gt text="You see the message"}: {gt text="Security center module is not installed, but preferably should be."}</h5>
    <p>{gt text="When a site is originally installed, the Security center is installed and activated by default. You will see the above message if the Security center has been removed. You are recommended to leave the Security center installed, as it enhances a site's security."}</p>

    <h5 id="admin_security_center_warning3">{gt text="You see the message"}: {gt text="Security center module is not activated, but preferably should be."}</h5>
    <p>{gt text="When a site is originally installed, the Security center is installed and activated by default. You will see the above message if the Security center has been deactivated. You are recommended to leave the Security center activated, as it enhances a site's security."}</p>

    <h5 id="admin_legacymodewarning">{gt text="You see the message"}: {gt text="Legacy module support is enabled, but preferably should be disabled."}</h5>
    <p>{gt text="You have the possibility of enabling support for legacy PostNuke modules. However, legacy modules can contain security holes that can compromise a site's security. You are strongly recommended to only use up-to-date versions of modules that are compliant with the project's official API (Application Programming Interface). In this case, legacy module support can be disabled (this is the default setting when a site is first installed)."}</p>
</fieldset>
{adminfooter}