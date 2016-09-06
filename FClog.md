The purpose of this file is to define how to disable the legacy Zikula_Core in order to test
your code against the new Core-2.0 standards. By Commenting these lines as indicated

index.php
 - comment the following lines
     - //$core->init(Zikula_Core::STAGE_ALL, $request);
     - //$core->getDispatcher()->dispatch('frontcontroller.predispatch', new \Zikula\Core\Event\GenericEvent());

lib/bootstrap.php
 - comment the following lines
     - //require __DIR__.'/core.php';
 - after commenting those lines, you can add all or none of these lines in order to re-enable some legacy core function
   add them immedately after the `require` function commented above.
     - ServiceUtil::setContainer($kernel->getContainer());
     - EventUtil::setManager($kernel->getContainer()->get('event_dispatcher'));
     - ModUtil::initCoreVars();

src/lib/Zikula/Bundle/CoreBundle/EventListener/ExceptionListener.php
 - comment out line 68
    - //$this->handleLegacyExceptionEvent($event);

src/system/ThemeModule/Engine/Filter.php
 - comment out the following lines
   - // $header .= trim(implode("\n", \PageUtil::getVar('header', [])) . "\n"); // @todo legacy - remove at Core-2.0
   - // $footer .= trim(implode("\n", \PageUtil::getVar('footer', [])) . "\n"); // @todo legacy - remove at Core-2.0

src/system/ThemeModule/Helper/BundleSyncHelper.php
 - comment out line 114
   - // \ZLanguage::bindThemeDomain($bundle->getName());

src/lib/i18n/ZLanguage.php
 - NOTE: `setup()` typehint has been changed from `Zikula_Request_Http` to `Symfony\Component\HttpFoundation\Request`
    - this _should_ be BC because the first is a child of the second.

src/lib/legacy/Zikula/View.php
 - NOTE: `zikula_core` is no longer assigned as a template variable for Smarty in `src/lib/legacy/Zikula/View.php`

src/system/AdminModule/Controller/AdminInterfaceController.php line 306
and src/system/ExtensionsModule/Controller/ExtensionsInterfaceController.php line 48
 - there is no BC method for determining the adminIconPath/AdminImagePath

src/system/ThemeModule/EventListener/DefaultPageVarSetterListener.php
 - There is no FC method for determining the language direction line 61
 - There is no FC method for determining the DB charset line 77
 
