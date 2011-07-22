GENERAL
=======
- The format of config.php has changed, please manually complete the values in
  the supplied config.php - note that base64 encoding is not supposed any more
  and everything must be in plain text (this is because base64 encoding is not
  actually an encryption).

DATABASE
========

Support for both column and database table prefixes has been dropped.  Existing 
modules should continue to work but module authors need to make some efforts to
migrate their modules away from using prefixes.

When you upgrade from 1.2 the core tables will be renamed without any prefix and
field names will be altered to remove the prefixes.

Module authors need to do the following:

  - tables.php should remove the use of DBUtil::getLimitedTablename() and just specify
the table name directly.

  - tables.php definitions should be updated to remove the column prefixes, e.g.

        $columns = array('id'        => 'z_id',
                         'parent_id' => 'z_parent_id',

    would become: 

        $columns = array('id'        => 'id',
                         'parent_id' => 'parent_id',

  - You will need will need to execute manual SQL to rename the columns and tables.  You can generally
    get the code you need from PHPMyAdmin by editing the table structure and then reading the SQL
    it generates.  Here is a sample taken from the Profile module:

        $connection = Doctrine_Manager::getInstance()->getConnection('default');
        $sqlStatements = array();
        // N.B. statements generated with PHPMyAdmin
        $sqlStatements[] = 'RENAME TABLE ' . DBUtil::getLimitedTablename('user_property') . " TO user_property";
        $sqlStatements[] = "ALTER TABLE `user_property` CHANGE `pn_prop_id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
           CHANGE `pn_prop_label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
           CHANGE `pn_prop_dtype` `dtype` INT( 11 ) NOT NULL DEFAULT '0',
           CHANGE `pn_prop_modname` `modname` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
           CHANGE `pn_prop_weight` `weight` INT( 11 ) NOT NULL DEFAULT '0',
           CHANGE `pn_prop_validation` `validation` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
           CHANGE `pn_prop_attribute_name` `attributename` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

         foreach ($sqlStatements as $sql) {
         $stmt = $connection->prepare($sql);
             try {
                 $stmt->execute();
             } catch (Exception $e) {
                 // trap and toss exceptions if you need to.
             }   
         }


LONGBLOB support
----------------

Support for LONGBLOB has been dropped.  You must choose another column type and 
alter it manually with SQL, e.g.

    ALTER TABLE workflows CHANGE debug debug LONGTEXT NULL DEFAULT NULL


TEMPLATES
=========
- Change all module templates plugin delimiters to { and }.  For any template
  plugins inside a <script></script> block or a <style></style> block please
  use {{ and }}.

- All plugins with pn have been renamed without the pn prefix.  The pndebug
  plugin has been renamed to zdebug. See all deprecated template plugins in
  lib/legacy/plugins. Please adjust your templates accordingly.

- To accommodate easy transition to the new templates Zikula runs a prefilter
  that will convert the old delimiters to the new, and also remove the pn prefix
  from any template plugin calls. This will also affect 3rd party module so
  3rd party modules must drop their pn prefix from the actual plugins.  As a
  workaround you can copy the plugin file and rename it and the function inside
  without the prefix.

- The {gt} plugin now no longer requires domain="", this will be detected automatically.

- You must refactor templates using the 'pndate_format' modifier to 'dateformat'.

- Output filtering is now done with safetext, and safehtml modifiers.

- In future versions of Zikula we will require more strict compliance with templating
  syntax.  {foo name=bar} is not acceptable, single or double quotes must be used.
  We recommend to use single quotes unless you need double quotes for a reason.
  The above example should be written as {foo name='bar'}
  Quote are not required for integers however, e.g. {sum count=1}

- All use of pnML() and {ml} are both completely deprecated and will not work
  any more because Zikula no longer supports define based language packs.
  Upgrade themes and modules to use Gettext.

- Rename templates from *.htm to *.tpl

- If you need to make browser hack please use the block made for the case, e.g.
  {browserhack condition="if lte IE 7"}foo{/browserback}
  This block also takes assign="var" so you can assign rather than display.

- Remove {addition_headers} plugin from any themes, this doesn't work anymore.

- Replace all occurrences of "javascript/style.css" with "style/core.css"

- The {pager} plugin  no longer requires the parameter shift

- Introduced persistent $metatags for general SEO purposes.

- Themes should be updated to use {$metatags.description} and {$metatags.keywords}

- Page title comes from {pagegetvar name="title"} and not {title}

- Replace any usage of the page variable 'rawtext' with 'header'. This affects 
  {pagesetvar}, {pageaddvar}, {pageregistervar}, {pagegetvar} in templates.

- Introduced persistent $coredata for miscellaneous templating data.

- Introduced persistent $modvars array with all module vars.

- Deprecated $pncore, use $coredata but not for the module vars as that behaviour
  is retained only when legacy mode is enabled.  Use $modvars.

- The following variables are reserved for Zikula_View and may not be assigned.
  - servicemanager
  - eventmanager
  - metatags
  - coredata
  - zikula_view
  - zikula_core
  - modvars

- {configgetvar} deprecated, use {$modvars.ZConfig.<name>}

- {blockshow} now requires parameter "position".

- Theme page variables have been altered slightly.  All metatags can get obtained from
  the {$metatags.foo} array present in all templates.  Metatags can be altered in the
  template with {setmetatag name='foo' value='bar'} as required.  From controllers
  just alter with `$this->serviceManager['zikula_view.metatags][$foo] = $bar;` no special
  API is required.

- The core icon set has been recoded to PNG format.  Refactor your templates to use .png
  the images.

- You can remove several lines in the admin templates. We don't need {admincategorymenu},
  the <div class="z-adminbox">...</div> header part as well as the "z-admincontainer".
  Just open the admin wrapper with {adminheader} and close it with {adminfooter} in the
  last line of your template.
  In most cases, files like "modname_admin_menu.tpl" are omitted because the header is 
  generated automatically now.

- The previous "z-adminpageicon" was restructured now:
  
  Instead of ...
  
  <div class="z-adminpageicon">{icon type="edit" size="large"}</div>
  <h2>{gt text='Edit'}</h2> 
  
  ... you should use the following markup:

  <div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit"}</h3>
  </div>
   
- The headings of your module admin templates should start from the third level.
  Before 1.3, the module display name was using H1, and the page title H2.
  Now the Theme title is the unique H1, the autogenerated module name is H2,
  and you should setup your content headings from H3 like:

  {adminheader}
  <div class="z-admin-content-pagetitle">
    {icon type="log" size="small"}
    <h3>{gt text="Admin List"}</h3>
  </div>

  <div>
    ... your admin list markup ...
  </div>

  <h4>{gt text="Additional Options"}</h4>
  ...
  {adminfooter}


THEMES
======
- While it's not compulsory, it is more efficient to update the block configuration
  templates to the relative path of block templates.

  e.g. in config/master.ini change

    [blockpositions]
    left = leftblock.tpl

  to

    [blockpositions]
    left = blocks/leftblock.tpl

MODULES
=======
- Module folder structure has changed, please create lib/$modname inside the module
  folder.  e.g. in module MyModule, add lib/MyModule.

    The old ones are still backward compatible but since file scanning is used
    your modules will be more efficient if you change to this format.

    - Rename pndocs to docs
    - Rename pnincludes to lib/, or lib/vendor if specifying 3rd party libs
    - Rename pnstyle to style
    - Rename pnjavascript to javascript
    - Rename pntemplates to templates

- Rename pntables.php to tables.php.  Change the function inside to $modulename_tables()

- Module folders now MUST start with a capital letter.

- Rename pnversion.php to lib/MyModule/Version.php
  Edit the contents like so:

    class MyModule_Version extends Zikula_AbstractVersion
    {
        public function getMetaData()
        {
            $meta = array();
            $meta['displayname']    = $this->__('MyModule example');
            $meta['description']    = $this->__("Example MyModule description.");
            //! module name that appears in URL
            $meta['url']            = $this->__('mymodule');
            $meta['version']        = '1.5.3';
            $meta['capabilities']   = array('profile' => array('version' => '1.0'));
            $meta['securityschema'] = array('MyModule::' => '::');
            return $meta;
        }
    }

  NOTE: Version numbers must be in the form 'a.b.c' e.g '1.0.0'

  Notice the new capabilities key.  This is an indexed array of arrays.
  array('profile' => array('version' => '1.0', 'anotherkey' => 'anothervalue')
        'message' => array('version' => '1.0', 'anotherkey' => 'anothervalue'));

  The following APIs can be used
    ModUtil::getModulesCapableOf()
    ModUtil::isCapable()
    ModUtil::getCapabilitiesOf()
    {html_select_modules capability='...'}

(Note in the following examples, $type must always start with a capital letter
 and all remaining characters must be lower case).

- Move module controllers (pnuser.php, pnadmin.php etc) to lib/$modname/Controller/$type
  e.g.
    pnuser.php => lib/MyModule/Controller/User.php

  Refactor the controllers, encapulating all functions inside 
  class $modname_Controller_$type extends Zikula_AbstractController
  e.g.
    class MyModule_Controller_User extends Zikula_AbstractController

  Make all functions public which should be accessible from the browser.
  Internal methods which should not be accessible outside the class should be made
  protected or private.  If you subclass Zikula_AbstractController, inherited methods
  will not be accessible even if they are public.

- Move module APIs (pnuserapi.php, pnadminapi.php etc) to lib/$modname/Api/$type
  e.g.
    pnuserapi.php => lib/MyModule/Api/User.php

  Refactor the APIs, encapulating all functions inside 
  class $modname_Api_$type extends Zikula_AbstractApi
  e.g.
    class MyModule_Api_User extends Zikula_AbstractApi

  Make all functions public which should be accessible from ModUtil::apiFunc().
  Internal methods which should not be accessible outside the class should be made
  protected or private.

- Move module blocks (pnblocks/foo.php etc) to lib/$modname/$type
  e.g.
    blocks/foo.php => lib/MyModule/Block/Foo.php

  Refactor the Blocks, encapulating all functions inside 
  class $modname_block_$type extends Zikula_Controller_AbstractBlock
  e.g.
    class MyModule_Block_Foo extends Zikula_Controller_AbstractBlock

  Make all functions public which should be accessible from outside the class.
  Internal methods which should not be accessible outside the class should be made
  protected or private.

- For all Controllers, APIs and Blocks, change gettext function calls
  OO modulea now have access to convenience where the domain is calculated
  automatically.

    $this->__($msgid)
    $this->__f($msgid, $params)
    $this->_n($singular, $plural, $count)
    $this->_fn($sin, $plu, $n, $params)

  Remove any $dom = ZLanguage::getModuleDomain() calls except from version.php

- For all Controllers and Blocks, remove any pnRender::getInstance() calls entirely.
  $this->view is automatically available: $this->view->assign(), $this->view->fetch()
  etc.

- Rename and move pninit.php to lib/MyModule/Installer.php
  Encapulate all functions in class $modname extends Zikula_Installer
  e.g.
    class MyModule_Installer extends Zikula_Installer
  
  Rename init() to install().
  Rename delete() to uninstall().

  Make all function public except for internal ones which should not be accessible outside the class,
  in which case make the protected or private.  Generally speaking only
  install(), upgrade() and uninstall() should be public.

  If your module was not compliant with previous standards you must
  - Add $meta['oldnames'] = array(oldnames,....); // in Version.php
  - Migrate any modvars with

        $modvars = ModUtil::getVar($oldname);
        if ($modvars) {
            foreach ($modvars as $key => $value) {
                $this->setVar($key, $value);
            }
            ModUtil::delVar($oldname);
        }

- Interactive install/upgrade/uninstall
  If there are any interactive install methods, please add these to lib/$modname/Controller/Interactiveinstaller.php
  e.g.
    lib/MyModule/Controller/Interactiveinstaller.php
    contains class MyModule_Controller_Interactiveinstaller extends Zikula_InteractiveInstaller (notice the casing).

  Basically, if the interactive installers has method install() that will override the install() in
  the main Installer.php, if it has upgrade() it will override the main upgrade() and if it has
  uninstall() it will override the uninstall() method.  Note, the override happen only at
  the initial install, upgrade, uninstall process when the user clicks to install/upgrade/uninstall.
  At the laste step, of the interactive process the installer will invoke the Installer.php methods to
  do the actual final process.

  Subsequent steps can be named arbitarily in the interactive installer controller class.  For example you might have
  upgrade_step1()
  upgrade_step2()
  etc.

- If you need any bootstrapping, like making a library available create bootstrap.php, this is
  included when the Module is first 'loaded'.

- If you use categorisation please refactor to use the following classes:
    PNCategory => Categories_DBObject_Category
    PNCategoryArray => Categories_DBObject_CategoryArray
    PNCategoryRegistry => Categories_DBObject_Registry
    PNCategoryRegistryArray => Categories_DBObject_RegistryArray

    Remove all references to Loader::loadClassFromModule, Loader::loadClassFromModuleArray()

- If you have any FilterUtil filter, replace any occurrence of $this->pntable with $this->dbtable

- FilterUtil can work with Doctrine passing the Record name to the constructor. i.e:

    $query = Doctrine_Query::create()
         ->from('MyModule_Model_MyModel tbl');

    $filter = new FilterUtil('MyModule', 'MyModule_Model_MyModel', $filter_args);
    $fwhere = $filter->GetSQL();

    $query->where($where)
          ->addWhere($fwhere);

    If you want to notify to FilterUtil of any JOIN present on your Doctrine Query, you can pass
    the main table alias and the join information in the $args:

    $joinInfo[] = array('join_table'         =>  'MyModule_Model_AnotherModel',
                        'join_alias'         =>  'another',
                        'join_field'         =>  array('fieldName1', 'fieldName2'),
                        'object_field_name'  =>  array('fieldAlias1', 'fieldAlias2'));

    $filter_args = array(
                         'varname' => 'filter',
                         'alias'   => 'tbl',
                         'join'    => $joinInfo
                        );

    $query->select('another.fieldName1 fieldAlias1, another.fieldName2 fieldAlias2')
          ->leftJoin('MyModule_Model_AnotherModel another ON another.id = tbl.another_id')

    And be able to filter the JOIN also with filter=fieldAlias1:eq:value

- Remove any references to Loader::loadClass() - classes are loaded automatically.

- Replace any usage of the page variable 'rawtext' with 'header'. This affects 
  calls to the PageUtil functions.

- You may now use the following convenience methods from OO controllers and APIs.
    (see lib/Zikula/Base.php for details)

    $this->throwNotFound()
    $this->throwNotFoundIf()
    $this->throwNotFoundUnless()

    $this->throwForbidden()
    $this->throwForbiddenIf()
    $this->throwForbiddenUnless()

    $this->redirect()
    $this->redirectIf()
    $this->redirectUnless()

URL STANDARDS
=============

All URLs must explicitly include module, type, and func in the GET request.
Please update all templates that generate URLs via ModUtil::url() or in templates {modurl ..}
so that full URLs are generated.  Assuming that type will default to 'user' and func will
default to 'main' are now no longer valid.

Custom API functions for `encodeurl()` should not remove the func parameter, unless a
custom `decodeurl()` function subsequently restores it. The execution of a custom 
`decodeurl()` function should always result in a URL that explicitly includes the
module name, type, and func components. If the URL encoded by a custom `encodeurl()`
function is to be decoded partially or fully by standard core functions, then only 
URLs having a type equal to 'user' should be encoded, and the func parameter should 
not be removed, even if it is equal to 'main'.

CSRF PROTECTION
===============
Templates should now use

    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />

And to check in the controller, use

    $this->checkCsrfToken();


HOOKS
=====
Hooks in Zikula 1.3.0 are not compatible with Zikula legacy hooks system.  In general,
modules written for Zikula 1.3.0 will not trigger the old hook system at all, nor will
the old hook system work with legacy mode off.  Please refer to the HOOKS documentation
regarding hooks.

DBOBJECT
========
- Rename classes to $modname_DBObject_$type and move to lib/$modname/DBObject/$type.php
  e.g
    MyModule_DBObject_Payments and move to lib/MyModule/DBObject/Payments.php
    MyModule_DBObject_PaymentsArray and move to lib/MyModule/DBObject/PaymentsArray.php

- Change the constructor of your DBObjects (was PNObject)
  from ClassName() to __construct() and to invoke parent constructor
  change $this->PNObject() to parent::__construct().

- Do not use Loader::loadClassFromModule to get DBObject class names any more.
  Simply build the class name or hard code it.

DBUTIL
======
- DBUtil::executeSQL used to return a ADODB object but now returns a PHP PDO
  object.  This means any code that previously iterated on the ADODB object
  will now break.  Please use of DBUtil::marshallObjects() after any manual
  SELECT through DBUtil::executeSQL() e.g.:

    [php]
    $res = DBUtil::executeSql ($sql);
    $objectArray = DBUtil::marshallObjects ($res, $ca, ...);

  Alternatively you can use the PDO return object.  PDO is built into PHP so
  accessing the PDO object is considered API complaint.  PDO documentation is
  available at http://php.net/PDO


MISCELLANEOUS
=============
- Theme module APIs theme_userapi_clear_compiled(), theme_userapi_clear_cache(),
  and pnrender_userapi_clear_compiled(), pnrender_userapi_clear_cache() are
  deprecated.  Please use Zikula_View::clear_compiled(), Zikula_View::clear_cache() and
  Theme::clear_compiled(), Theme::clear_cache().

- Now you can add a requirement check for your blocks which will display a
  message if it's necessary into the admin panel. eg: the language block will be
  visible only if the multilanguage system is enabled, so for this block a
  requirement message was aded to inform the admin that this block will not be
  visible until he enables the multilanguage system.

- You may now customise the core with Event Handler, these can be loaded in
  config/EventHandlers.  The classes should be the same as the filename and
  extend from CustomEventHandler.

- In modules you can autoload event handlers by calling
  EventUtil::attachCustomHandlers($path) which should be a folder with
  just event handlers, or if you have static method handler just load them
  directly with EventUtil::getManager()->attach($name, $callable); [see ** below]
  This method could be used to load event handler dynamically from a ConfigVar()
  containing array('name' => $name, 'callable' => $callable);

  ** Note that a callable is in the following format:-
       Foo::bar() = array('Foo', 'bar')
       $foo->bar = array($foo, 'bar')
       myfunction() = 'myfunction'

- The name of the classes are Modulename_$type (case sensitive).
  The $func argument would be the public methods contained therein.
  Also see the EventHandlers folder which shows how a method can be
  added to the controller via a notify() events of name
  'controller.method_not_found' and 'controllerapi.method_not_found'
  for APIs.

- OO modules will initialise an autoloader for the module automatically so
  a call to a class Example_DBObject_Users would load
  module/Example/lib/Example/DBObject/Users.php - the class contained should be
  Example_DBObject_Users.

  Please note that because of the use of ModUtil::func() and ModUtil::apiFunc()
  Controller and Controller Apis must be named according to the type in real
  camel case (ucwords).  E.g. type = adminform means the file *must* be names
  Adminform and NOT AdminForm.  The class name would be Modulename_Adminform.

- You may now optionally include bootstrap.php in your module root directory.
  This will be loaded during ModUtil::load/ModUtil::loadGeneric() automatically.

- You may additionally register autoloaders with
  ZLoader::addAutoloader($namespace, $path) where
  $namespace is the first part of the PEAR classname, and $path is
  the path to the containing folder.  Use bootstrap.php.

- If you have any front controllers, please note the bootstrapping process has
  been changed, see index.php for example.

- It is not acceptable to query the session for the user id.  You must use
  UserUtil::getVar('uid');

- To determine if the user is the anonymous user, please use UserUtil::isGuestUser()

API CHANGES
===========
There is a shell script in SVN tool/ to rename all these for you automatically
and accurately.

- pnMod* now all deprecated see ModUtil::*
- pnUser* deprecated, see UserUtil::*
- pnBlock deprecated, see BlockUtil::*
- pn* deprecated see System::*
- Legacy APIs for BC are stored in legacy/Compat.php and legacy/Api.php

- The prefixes are NOT gone for the class function based controllers like pnadmin etc.
  This is deliberate to encourage you to move to OO module controllers.


WORKFLOW CHANGES
================
- If you use WorkflowUtil, there are four changes for Zikula 1.3:
   - getActionsByStateArray:
       is not deprecated.
   - getActionsByState:
       now returns all the action data as array($action.id => $action),
       instead of array(id => id).
   - getActionTitlesByState:
       useful method to build the buttons for the current state,
       returning the allowed actions as array($action.id => $action.title).
   - getActionsForObject:
       now returns the result of getActionsByState.
   If you used values, replace them with the result keys, and take advantage
   of the action data now available.
- Workflow actions can define additional parameters in the XML like:
  <parameter className="z-bt-ok" titleText="Click me">button</parameter>
  and the case will be respected.


AJAX WORKFLOW CHANGES
=====================

The Zikula 1.3. ajax workflow has been changed from both the PHP and JavaScript
side.
On the JavaScript side:
- All requests should be performed using Zikula.Ajax.Request, this class is
  an extension of the prototype Ajax.Request and inherits all its methods, options
  and properties.
- For requests sent by Zikula.Ajax.Request has been added a new parameter -
  "authid", if you provide ID for element containing authid token - it will be
  automatically added to the request and then updated after receiving the response,
  it is the only recommended method for handling authid in ajax requests,
- The response returned by Zikula.Ajax.Request now has new methods for the data
  collection:
    - getAuthid - returns new authid token - usually there is no need to refer
      to this method manually, because authid should now be updated automatically
    - getMessage - returns the error or status message (or list of messages)
      registered in module controller by LogUtil
    - getData - returns the main data provided by the module controller
    - isSuccess - check if the request is successful or not
- The only recommended way to read the response is to use methods listed above,
  the response however still has all the methods and properties that has original
  Ajax.Response object
- In some cases ajax calls are made without Zikula.Ajax.Request (eg some predefined
  scripts, such as Ajax.InPlaceEditor etc) and returned response does is not extended
  with Zikula.Ajax.Response method. In such case use Zikula.Ajax.Response.extend
  method to manually extend response.

On the PHP side:
- There has been developed a whole set of classes that support responses sent to
  ajax request. Also error handling was changed.
- Module controller in case of success should always return as response one
  of the two types of objects: Zikula_Response_Ajax_Base or Zikula_Response_Ajax_Plain.
- Zikula_Response_Ajax_Base has 3 arguments:
      - $data - takes as an argument any value - a single variable or array,
        which then can be read on the JS side using the getData method
      - $message - optional param, which allows to pass message (or array of messages)
        to response; such messages will be next merged with possible messages
        from LotUtil
      - $options - optional param, which allows to add additional data to response
  In most cases, the module should return a reply of this type. In addition,
  this type of response is assumed to generate a new authid token.
- If it is necessary to send response that contains only plain text or html
  (for example, Ajax.Autocompleter from Scriptaculous requires such response)
  the module controller must return as response Zikula_Response_Ajax_Plain object.
  This class takes plain text as its $data argument. For this type of responses
  new authid token is not generated.
- Possible errors (not related to data validation) in the module controller
  must be handled via exceptions. You may first register error message using LogUtil,
  then throw an exception (eg Zikula_Exception_Forbidden for no presmission or
  Zikula_Exception_Fatal for bad authid token). You may also pass error message
  directly to exception.
- If the controller module must declare a failure because of data validation and/or
  also send some data to JS then the module should not throw an exception but instead
  return object of type Zikula_Response_Ajax_BadData. This class allows to pass
  arguments exactly the same as usual ajax responses.

Example (taken from the Permissions module):
Send a request from JS:
    // build parameters object
    var pars = {pid: permid};
    // call request class
    new Zikula.Ajax.Request(
        "ajax.php?module=permissions&func=deletepermission",
        {
            method: 'get',
            parameters: pars,
            authid 'permissionsauthid', // value of "permissionsauthid" will be
                                           added to request as authid and with
                                           response arrive it will be updated
            onComplete: permdelete_response
        }
    );

Process the request in the module controller:
    // test permissions and throw an exception on failure (in a Zikula_Base instance)
    $this->throwForbiddenUnless(SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN), LogUtil::getErrorMsgPermission());

    // test permissions and throw an exception on failure (outside a Zikula_Base instance)
    if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
        throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
    }

    // test authid and throw an exception on failure (in a Zikula_Base instance)
    $this->throwForbiddenUnless(SecurityUtil::confirmAuthKey(), LogUtil::getErrorMsgAuthid());

    // test authid and throw an exception on failure (outside a Zikula_Base instance)
    if (!SecurityUtil::confirmAuthKey()) {
        throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgAuthid());
    }

    // when controller needs to return failure due to data validation:
    return new Zikula_Response_Ajax_BadData($this->__('Invalid input')); // Second param $data is optional

    // throw an exception from some other reason
    throw new Zikula_Exception_Fatal($this->__f('Error! Could not delete permission rule with ID %s.', $pid));

    // return response
    return new Zikula_Response_Ajax(array('pid' => $pid));

Read the response in JS
    // check if request was successful
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }
    // get data returned by module
    // if you passed eg array('pid'=>123), then you will have data.pid = 123
    var date = req.getData();

    // when ajax call was made without Zikula.Ajax.Request you have to
    // manually extend response
    transport = Zikula.Ajax.Response.extend(transport);
    // no you have access to new methods:
    transport.getData();

If you need to communicate with some javascript that is not part of the Zikula
JS framework, we provide two responses of use

    // return a plain string
    return new Zikula_Response_Ajax_Plain($string);

    // return some data that must be serialized (will be serialized by the class).
    return new Zikula_Response_Ajax_Json($mixed);


PAGEADDVAR CHANGES
==================
This API has been updated with several conveniences.  The changes are
fully backwards compatible: you will notice that pageutil includes the new
javascript references even when specifying the old ones.

Prototype and Scriptaculous have been combined into a single compressed file for
convenience.  Only validation.js and unittest.js have not been combined.

Simply including prototype will include the combined version.  There is no need
to specify ajax, prototype and scriptaculous separately any more,
simply just specify 'prototype'.

To add Livepipe, simply specify 'livepipe'.  All Livepipe files have been
compressed into one.

To add jQuery, simply specify 'jquery'.  This will set up jQuery.noConflict()
automatically.

Since Zikula 1.3 it's recommended to load core scripts using defined shortcuts.
This way all dependencies will be resolved (also required stylesheets will be
loaded). Below is list of supported shortcuts:
- prototype,
- livepipe,
- zikula,
- zikula.ui,
- zikula.imageviewer,
- zikula.itemlist,
- zikula.tree,
- validation,
- jquery


ZIKULA_VIEW / ZIKULA_THEME CLASSES
==================================
Dozens of getter and setters have been added to try and encapsulate things more and
one day, allow a more easy migration away from Smarty 2.  Please desist from
direct access to properties and use the getter/setters.

Zikula_View (and thus Zikula_Theme, Zikula_Form_View etc) all now make use of
Zikula_TranslatableInterface which means that translation methods are always
available and pre-configured to the correct domain.

Inside a template plugin simply use $view->__() etc.

FORMS
=====
There have been some very important and powerful changes to the forms framework.

New features/fixes
------------------
It is now possible to have more than one instance of a form at once.  Nonce
checking has also been improved (automatically).

All forms now are assigned their own ID.  This is available to the form template
with `{$__formid}` and in the Zikula_Form_View with the getter $view->getFormId()
It may be necessary to update any javascripts to observe forms with the new
form ID.

Form Handler
------------
Firstly the Zikula_Form_Handler interface is now enforced, so handlers must extend
Zikula_Form_Handler.

Zikula_Form_Handler class has been modified with some powerful additions.
  - Zikula_Form_View::execute() now configures handlers
    - Injects the Zikula_Form_View into the handler's view property.
    - Configures the handler with the domain of the Zikula_Form_View.
    - Invoked setup() hook in the handler.
    - The handler now executes preInitialize() and postInitialize() around the
      initialize() method.
    - Form Handlers now implement Zikula_TranslatableInterface so you may just use
      $this->__() etc.  The methods are configured with the handler domain.
  
Plugins
-------
Firstly the Zikula_Form_Plugin interface is now enforced, so plugins must extend
Zikula_Form_Plugin.

Zikula_Form_Plugin class has been modified with some powerful additions.
    - Zikula_Form_View::registerPlugin() configures the plugins after instanciation.
    - Injects the Zikula_Form_View into the plugin's view property.
    - Configures the plugin with the domain of the Zikula_Form_View.
    - Invoked setup() hook in the handler.
    - The hooks preInitialize() and postInitialize() are invoked around the
      initialize() method.
    - Form Handlers now implement Zikula_TranslatableInterface so you may just use
      $this->__() etc.  The methods are configured with the plugin's domain.
  
Please note that in cases, where plugins are being re-used, you will need to
configure them with their own domain hardcoded in the setup() 
`$this->domain = 'foo';` because by default they will take on the characteristics
of the View they were invoked by.

Plugins, Handlers and template plugins should all be separated now. Everything
is handled by autoloading.

Example layout:

lib/Foo/Form/Handler/Admin/Config.php      Foo_Form_Handler_Admin_Config
lib/Foo/Form/Handler/User/View.php         Foo_Form_Handler_User_Config
lib/Foo/Form/Plugin/Youtube.php            Foo_Form_Plugin_Youtube
templates/plugins/function.formyoutube.php The actual template plugin.

A 'zparameters' parameter was added as a direct way to assign the values of
the form plugins attributes. For instance:
$attributes = {class:z-bt-ok; confirmMessage:Are you sure?}
{formbutton commandName='delete' __text='Delete' zparameters=$attributes}

The {linkbutton} now supports an image (through the {img} plugin), using the
new parameters 'imgset' and 'imgsrc'. imgset is default to 'icons/extrasmall'.
Examples:
{linkbutton commandName='edit' __text='Edit' imgsrc='edit.gif'}
or through the core CSS:
{linkbutton commandName='edit' __text='Edit' class='z-icon-es-edit'}
{button commandName='cancel' __text='Cancel' class='z-bt-cancel'}

API COMPLIANCE
==============
The following list of things are considered non Zikula API compliant.  If you
rely on them, there is no guarantee they will remain working even from one
bugfix version to the next.

  - Accessing class properties from Smarty, Zikula_View, Zikula_View_* classes
is completely forbidden although still possible since Smarty exposes many.  We have
added getters and setter and new methods in Zikula_View to modify settings.
  - The same rules apply to the forms framework.  Please access everything via
the provided getters and setters.
  - Reliance on $GLOBALS['ZConfig'] and $GLOBALS['ZRuntime'].
  - Reliance on Zikula_Adapter_AdodbStatement being returned from DBUtil::executeSQL().

SECURITY
========
You are REQUIRED to validate and sanitize input variables.  DO NOT assume that just because
you have retrieved them from FormUtil::getPassedValues() that they are valid or safe.
FormUtil::getPassedValues() can now filter and sanitize with native PHP filter_* or you
may do this manually.
