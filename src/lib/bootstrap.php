<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
include 'lib/ZLoader.php';
ZLoader::register();

// to be removed before release
interface Zikula_Translatable extends Zikula_TranslatableInterface{}
interface Zikula_Collection_Interface extends Zikula_CollectionInterface{}
abstract class Zikula_Base extends Zikula_AbstractBase{}
abstract class Zikula_Controller extends Zikula_AbstractController{}
abstract class Zikula_Api extends Zikula_AbstractApi{}
abstract class Zikula_Controller_Block extends Zikula_Controller_AbstractBlock{}
abstract class Zikula_Controller_Ajax extends Zikula_Controller_AbstractAjax{}
abstract class Zikula_Helper extends Zikula_AbstractHelper{}
abstract class Zikula_InteractiveInstaller extends Zikula_Controller_AbstractInteractiveInstaller{}
abstract class Zikula_Installer extends Zikula_AbstractInstaller{}
abstract class Zikula_Controller_Plugin extends Zikula_Controller_AbstractPlugin{}
abstract class Zikula_Version extends Zikula_AbstractVersion{}
abstract class Zikula_EventHandler extends Zikula_AbstractEventHandler{}
abstract class Zikula_Plugin extends Zikula_AbstractPlugin{}
abstract class Zikula_ErrorHandler extends Zikula_AbstractErrorHandler{}
abstract class Zikula_Form_Plugin extends Zikula_Form_AbstractPlugin{}
abstract class Zikula_Form_Handler extends Zikula_Form_AbstractHandler{}
abstract class Zikula_Form_StyledPlugin extends Zikula_Form_AbstractStyledPlugin{}
abstract class Zikula_HookHandler extends Zikula_Hook_AbstractHandler{}
abstract class Zikula_Hook_ValidationProviders extends Zikula_Collection_HookValidationProviders {}

$core = new Zikula_Core();
$core->boot();

// Load system configuration
$event = new Zikula_Event('bootstrap.getconfig', $core);
$core->getEventManager()->notifyUntil($event);

$event = new Zikula_Event('bootstrap.custom', $core);
$core->getEventManager()->notify($event);
