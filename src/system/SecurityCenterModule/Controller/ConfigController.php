<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Controller;

use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_VarParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\SecurityCenterModule\Constant;
use Zikula\SecurityCenterModule\Form\Type\ConfigType;
use Zikula\SecurityCenterModule\Helper\HtmlTagsHelper;
use Zikula\SecurityCenterModule\Helper\PurifierHelper;
use Zikula\SecurityCenterModule\ZikulaSecurityCenterModule;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Helper\AccessHelper;

/**
 * Class ConfigController
 *
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaSecurityCenterModule/Config/config.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function config(
        ZikulaSecurityCenterModule $securityCenterModule,
        Request $request,
        RouterInterface $router,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        AccessHelper $accessHelper,
        string $projectDir
    ) {
        $modVars = $variableApi->getAll(VariableApi::CONFIG);

        $sessionName = $this->getParameter('zikula.session.name');
        $modVars['sessionname'] = $sessionName;
        $modVars['idshtmlfields'] = implode(PHP_EOL, $modVars['idshtmlfields']);
        $modVars['idsjsonfields'] = implode(PHP_EOL, $modVars['idsjsonfields']);
        $modVars['idsexceptions'] = implode(PHP_EOL, $modVars['idsexceptions']);

        $form = $this->createForm(ConfigType::class, $modVars);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $updateCheck = $formData['updatecheck'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'updatecheck', $updateCheck);
                if (0 === $updateCheck) {
                    // if update checks are disabled, reset values to force new update check if re-enabled
                    $variableApi->set(VariableApi::CONFIG, 'updateversion', ZikulaKernel::VERSION);
                    $variableApi->set(VariableApi::CONFIG, 'updatelastchecked', 0);
                }
                $variableApi->set(VariableApi::CONFIG, 'updatefrequency', $formData['updatefrequency'] ?? 7);

                $variableApi->set(VariableApi::CONFIG, 'seclevel', $formData['seclevel'] ?? 'Medium');

                $secMedDays = $formData['secmeddays'] ?? 7;
                if ($secMedDays < 1 || $secMedDays > 365) {
                    $secMedDays = 7;
                }
                $variableApi->set(VariableApi::CONFIG, 'secmeddays', $secMedDays);

                $secInactiveMinutes = $formData['secinactivemins'] ?? 20;
                if ($secInactiveMinutes < 1 || $secInactiveMinutes > 1440) {
                    $secInactiveMinutes = 7;
                }
                $variableApi->set(VariableApi::CONFIG, 'secinactivemins', $secInactiveMinutes);

                $sessionStoreToFile = $formData['sessionstoretofile'] ?? 0;
                $sessionSavePath = $formData['sessionsavepath'] ?? '';

                // check session path config is writable (if method is being changed to session file storage)
                $causeLogout = false;
                $storeTypeCanBeWritten = true;
                if (1 === $sessionStoreToFile && !empty($sessionSavePath)) {
                    // fix path on windows systems
                    $sessionSavePath = str_replace('\\', '/', $sessionSavePath);
                    // sanitize the path
                    $sessionSavePath = trim(stripslashes($sessionSavePath));

                    // check if sessionsavepath is a dir and if it is writable
                    // if yes, we need to logout
                    $storeTypeCanBeWritten = is_dir($sessionSavePath) ? is_writable($sessionSavePath) : false;
                    $causeLogout = $storeTypeCanBeWritten;

                    if (false === $storeTypeCanBeWritten) {
                        // an error occured - we do not change the way of storing session data
                        $this->addFlash('error', 'Error! Session path not writeable!');
                        $sessionSavePath = '';
                    }
                }
                if (true === $storeTypeCanBeWritten) {
                    $variableApi->set(VariableApi::CONFIG, 'sessionstoretofile', $sessionStoreToFile);
                    $variableApi->set(VariableApi::CONFIG, 'sessionsavepath', $sessionSavePath);
                }

                if ((bool)$sessionStoreToFile !== (bool)$variableApi->getSystemVar('sessionstoretofile')) {
                    // logout if going from one storage to another one
                    $causeLogout = true;
                }

                $newSessionName = $formData['sessionname'] ?? $sessionName;
                if (mb_strlen($newSessionName) < 3) {
                    $newSessionName = $sessionName;
                }

                // cause logout if we changed session name
                if ($newSessionName !== $modVars['sessionname']) {
                    $causeLogout = true;
                }

                $configurator = new Configurator($projectDir);
                $configurator->loadPackages('zikula_security_center');
                $sessionConfig = $configurator->get('zikula_security_center', 'session');
                $sessionConfig['name'] = $newSessionName;
                $sessionConfig['handler_id'] = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'session.handler.native_file' : 'zikula_core.bridge.http_foundation.doctrine_session_handler';
                $sessionConfig['storage_id'] = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'zikula_core.bridge.http_foundation.zikula_session_storage_file' : 'zikula_core.bridge.http_foundation.zikula_session_storage_doctrine';
                $sessionConfig['save_path'] = empty($sessionSavePath) ? '%kernel.cache_dir%/sessions' : $sessionSavePath;
                $configurator->set('zikula_security_center', 'session', $sessionConfig);
                $configurator->write();

                $variableApi->set(VariableApi::CONFIG, 'sessionname', $newSessionName);
                $variableApi->set(VariableApi::CONFIG, 'sessionstoretofile', $sessionStoreToFile);

                $variableApi->set(VariableApi::CONFIG, 'outputfilter', $formData['outputfilter'] ?? 1);

                $useIds = $formData['useids'] ?? 0;
                $variableApi->set(VariableApi::CONFIG, 'useids', $useIds);

                // create tmp directory for PHPIDS
                if (1 === $useIds) {
                    $idsTmpDir = $this->getParameter('kernel.cache_dir') . '/idsTmp';
                    $fs = new Filesystem();
                    if (!$fs->exists($idsTmpDir)) {
                        $fs->mkdir($idsTmpDir);
                    }
                }

                $variableApi->set(VariableApi::CONFIG, 'idssoftblock', $formData['idssoftblock'] ?? 1);
                $variableApi->set(VariableApi::CONFIG, 'idsmail', $formData['idsmail'] ?? 0);
                $variableApi->set(VariableApi::CONFIG, 'idsfilter', $formData['idsfilter'] ?? 'xml');

                $idsRulePath = $formData['idsrulepath'] ?? 'Resources/config/phpids_zikula_default.xml';
                if (is_readable($securityCenterModule->getPath() . '/' . $idsRulePath)) {
                    $variableApi->set(VariableApi::CONFIG, 'idsrulepath', $idsRulePath);
                } else {
                    $this->addFlash('error', $this->trans('Error! PHPIDS rule file %filePath% does not exist or is not readable.', ['%filePath%' => $idsRulePath]));
                }

                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdone', $formData['idsimpactthresholdone'] ?? 1);
                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdtwo', $formData['idsimpactthresholdtwo'] ?? 10);
                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdthree', $formData['idsimpactthresholdthree'] ?? 25);
                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdfour', $formData['idsimpactthresholdfour'] ?? 75);

                $variableApi->set(VariableApi::CONFIG, 'idsimpactmode', $formData['idsimpactmode'] ?? 1);

                $idsHtmlFields = $formData['idshtmlfields'] ?? '';
                $idsHtmlFields = explode(PHP_EOL, $idsHtmlFields);
                $idsHtmlArray = [];
                foreach ($idsHtmlFields as $idsHtmlField) {
                    $idsHtmlField = trim($idsHtmlField);
                    if (!empty($idsHtmlField)) {
                        $idsHtmlArray[] = $idsHtmlField;
                    }
                }
                $variableApi->set(VariableApi::CONFIG, 'idshtmlfields', $idsHtmlArray);

                $idsJsonFields = $formData['idsjsonfields'] ?? '';
                $idsJsonFields = explode(PHP_EOL, $idsJsonFields);
                $idsJsonArray = [];
                foreach ($idsJsonFields as $idsJsonField) {
                    $idsJsonField = trim($idsJsonField);
                    if (!empty($idsJsonField)) {
                        $idsJsonArray[] = $idsJsonField;
                    }
                }
                $variableApi->set(VariableApi::CONFIG, 'idsjsonfields', $idsJsonArray);

                $idsExceptions = $formData['idsexceptions'] ?? '';
                $idsExceptions = explode(PHP_EOL, $idsExceptions);
                $idsExceptionsArray = [];
                foreach ($idsExceptions as $idsException) {
                    $idsException = trim($idsException);
                    if (!empty($idsException)) {
                        $idsExceptionsArray[] = $idsException;
                    }
                }
                $variableApi->set(VariableApi::CONFIG, 'idsexceptions', $idsExceptionsArray);

                $variableApi->set(VariableApi::CONFIG, 'idscachingtype', $formData['idscachingtype'] ?? 'none');
                $variableApi->set(VariableApi::CONFIG, 'idscachingexpiration', $formData['idscachingexpiration'] ?? 600);

                // clear cache
                $cacheClearer->clear('symfony');

                // the module configuration has been updated successfuly
                $this->addFlash('status', 'Done! Configuration updated.');

                // we need to auto logout the user if essential session settings have been changed
                if (true === $causeLogout) {
                    $accessHelper->logout();
                    $this->addFlash('status', 'Session handling variables have changed. You must log in again.');
                    $returnPage = urlencode($router->generate('zikulasecuritycentermodule_config_config'));

                    return $this->redirectToRoute('zikulausersmodule_access_login', ['returnUrl' => $returnPage]);
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulasecuritycentermodule_config_config');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/purifierconfig/{reset}")
     * @Theme("admin")
     * @Template("@ZikulaSecurityCenterModule/Config/purifierconfig.html.twig")
     *
     * HTMLPurifier configuration.
     *
     * @return array|RedirectResponse
     */
    public function purifierconfig(
        Request $request,
        PurifierHelper $purifierHelper,
        CacheClearer $cacheClearer,
        string $reset = null
    ) {
        if (Request::METHOD_POST === $request->getMethod()) {
            // Load HTMLPurifier Classes
            $purifier = $purifierHelper->getPurifier();

            // Update module variables.
            $config = $request->request->get('purifierConfig');
            $config = HTMLPurifier_Config::prepareArrayFromForm($config, false, true, true, $purifier->config->def);

            $allowed = HTMLPurifier_Config::getAllowedDirectivesForForm(true, $purifier->config->def);
            foreach ($allowed as [$namespace, $directive]) {
                $directiveKey = $namespace . '.' . $directive;
                $def = $purifier->config->def->info[$directiveKey];

                if (isset($config[$namespace])
                        && array_key_exists($directive, $config[$namespace])
                        && null === $config[$namespace][$directive]) {
                    unset($config[$namespace][$directive]);

                    if (count($config[$namespace]) <= 0) {
                        unset($config[$namespace]);
                    }
                }

                if (isset($config[$namespace][$directive])) {
                    if (is_int($def)) {
                        $directiveType = abs($def);
                    } else {
                        $directiveType = $def->type ?? 0;
                    }

                    switch ($directiveType) {
                        case HTMLPurifier_VarParser::LOOKUP:
                            $value = explode(PHP_EOL, $config[$namespace][$directive]);
                            $config[$namespace][$directive] = [];
                            foreach ($value as $val) {
                                $val = trim($val);
                                if (!empty($val)) {
                                    $config[$namespace][$directive][$val] = true;
                                }
                            }
                            if (empty($config[$namespace][$directive])) {
                                unset($config[$namespace][$directive]);
                            }
                            break;
                        case HTMLPurifier_VarParser::ALIST:
                            $value = explode(PHP_EOL, $config[$namespace][$directive]);
                            $config[$namespace][$directive] = [];
                            foreach ($value as $val) {
                                $val = trim($val);
                                if (!empty($val)) {
                                    $config[$namespace][$directive][] = $val;
                                }
                            }
                            if (empty($config[$namespace][$directive])) {
                                unset($config[$namespace][$directive]);
                            }
                            break;
                        case HTMLPurifier_VarParser::HASH:
                            $value = explode(PHP_EOL, $config[$namespace][$directive]);
                            $config[$namespace][$directive] = [];
                            foreach ($value as $val) {
                                [$i, $v] = explode(':', $val);
                                $i = trim($i);
                                $v = trim($v);
                                if (!empty($i) && !empty($v)) {
                                    $config[$namespace][$directive][$i] = $v;
                                }
                            }
                            if (empty($config[$namespace][$directive])) {
                                unset($config[$namespace][$directive]);
                            }
                            break;
                    }
                }

                if (isset($config[$namespace])
                        && array_key_exists($directive, $config[$namespace])
                        && null === $config[$namespace][$directive]) {
                    unset($config[$namespace][$directive]);

                    if (count($config[$namespace]) <= 0) {
                        unset($config[$namespace]);
                    }
                }
            }

            $this->setVar('htmlpurifierConfig', serialize($config));

            // clear all cache and compile directories
            $cacheClearer->clear('symfony');
            $cacheClearer->clear('legacy');

            // the module configuration has been updated successfuly
            $this->addFlash('status', 'Done! Saved HTMLPurifier configuration.');

            return $this->redirectToRoute('zikulasecuritycentermodule_config_purifierconfig');
        }

        // load the configuration page

        if (isset($reset) && 'default' === $reset) {
            $purifierConfig = $purifierHelper->getPurifierConfig(['forcedefault' => true]);
            $this->addFlash('status', 'Default values for HTML Purifier were successfully loaded. Please store them using the "Save" button at the bottom of this page');
        } else {
            $purifierConfig = $purifierHelper->getPurifierConfig(['forcedefault' => false]);
        }

        $purifier = new HTMLPurifier($purifierConfig);

        $config = $purifier->config;

        if (is_array($config) && isset($config[0])) {
            $config = $config[1];
        }

        $allowed = HTMLPurifier_Config::getAllowedDirectivesForForm(true, $config->def);

        // list of excluded directives, format is $namespace_$directive
        $excluded = ['Cache_SerializerPath'];

        // Editing for only these types is supported
        $editableTypes = [
            HTMLPurifier_VarParser::C_STRING,
            HTMLPurifier_VarParser::ISTRING,
            HTMLPurifier_VarParser::TEXT,
            HTMLPurifier_VarParser::ITEXT,
            HTMLPurifier_VarParser::C_INT,
            HTMLPurifier_VarParser::C_FLOAT,
            HTMLPurifier_VarParser::C_BOOL,
            HTMLPurifier_VarParser::LOOKUP,
            HTMLPurifier_VarParser::ALIST,
            HTMLPurifier_VarParser::HASH
        ];

        $purifierAllowed = [];
        foreach ($allowed as [$namespace, $directive]) {
            if (in_array($namespace . '_' . $directive, $excluded, true)) {
                continue;
            }

            $directiveRec = [];
            $directiveRec['key'] = $namespace . '.' . $directive;
            $def = $config->def->info[$directiveRec['key']];
            $directiveRec['value'] = $config->get($directiveRec['key']);
            if (is_int($def)) {
                $directiveRec['allowNull'] = ($def < 0);
                $directiveRec['type'] = abs($def);
            } else {
                $directiveRec['allowNull'] = (isset($def->allow_null) && $def->allow_null);
                $directiveRec['type'] = ($def->type ?? 0);
                if (isset($def->allowed)) {
                    $directiveRec['allowedValues'] = [];
                    foreach ($def->allowed as $val => $b) {
                        $directiveRec['allowedValues'][] = $val;
                    }
                }
            }
            if (is_array($directiveRec['value'])) {
                switch ($directiveRec['type']) {
                    case HTMLPurifier_VarParser::LOOKUP:
                        $value = [];
                        foreach ($directiveRec['value'] as $val => $b) {
                            $value[] = $val;
                        }
                        $directiveRec['value'] = implode(PHP_EOL, $value);
                        break;
                    case HTMLPurifier_VarParser::ALIST:
                        $directiveRec['value'] = implode(PHP_EOL, $directiveRec['value']);
                        break;
                    case HTMLPurifier_VarParser::HASH:
                        $directiveRec['value'] = json_encode($directiveRec['value']);
                        break;
                    default:
                        $directiveRec['value'] = '';
                }
            }

            $directiveRec['supported'] = in_array($directiveRec['type'], $editableTypes, true);

            $purifierAllowed[$namespace][$directive] = $directiveRec;
        }

        return [
            'purifier' => $purifier,
            'purifierTypes' => HTMLPurifier_VarParser::$types,
            'purifierAllowed' => $purifierAllowed
        ];
    }

    /**
     * @Route("/allowedhtml")
     * @Theme("admin")
     * @Template("@ZikulaSecurityCenterModule/Config/allowedhtml.html.twig")
     *
     * Display the allowed html form.
     *
     * @return array|RedirectResponse
     */
    public function allowedhtml(
        Request $request,
        RouterInterface $router,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        HtmlTagsHelper $htmlTagsHelper
    ) {
        $htmlTags = $htmlTagsHelper->getTagsWithLinks();

        if (Request::METHOD_POST === $request->getMethod()) {
            $htmlEntities = $request->request->getInt('htmlentities', 0);
            $variableApi->set(VariableApi::CONFIG, 'htmlentities', $htmlEntities);

            // update the allowed html settings
            $allowedHtml = [];
            foreach ($htmlTags as $htmlTag => $usageTag) {
                $tagVal = $request->request->getInt('htmlallow' . $htmlTag . 'tag', 0);
                if (1 !== $tagVal && 2 !== $tagVal) {
                    $tagVal = 0;
                }
                $allowedHtml[$htmlTag] = $tagVal;
            }

            $variableApi->set(VariableApi::CONFIG, 'AllowableHTML', $allowedHtml);

            // clear all cache and compile directories
            $cacheClearer->clear('symfony');
            $cacheClearer->clear('legacy');

            $this->addFlash('status', 'Done! Configuration updated.');

            return $this->redirectToRoute('zikulasecuritycentermodule_config_allowedhtml');
        }

        return [
            'htmlEntities' => $variableApi->getSystemVar('htmlentities'),
            'htmlPurifier' => 1 === $variableApi->getSystemVar('outputfilter'),
            'configUrl' => $router->generate('zikulasecuritycentermodule_config_config'),
            'htmlTags' => $htmlTags,
            'currentHtmlTags' => $variableApi->getSystemVar('AllowableHTML')
        ];
    }
}
