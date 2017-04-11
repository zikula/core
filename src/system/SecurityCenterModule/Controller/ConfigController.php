<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Controller;

use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_VarParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SecurityCenterModule\Constant;
use Zikula\SecurityCenterModule\Form\Type\ConfigType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template
     *
     * This is a standard function to modify the configuration parameters of the module.
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $modVars = $variableApi->getAll(VariableApi::CONFIG);

        $sessionName = $this->get('service_container')->getParameter('zikula.session.name');
        $modVars['sessionname'] = $sessionName;
        $modVars['idshtmlfields'] = implode(PHP_EOL, $modVars['idshtmlfields']);
        $modVars['idsjsonfields'] = implode(PHP_EOL, $modVars['idsjsonfields']);
        $modVars['idsexceptions'] = implode(PHP_EOL, $modVars['idsexceptions']);

        $form = $this->createForm(ConfigType::class, $modVars, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $updateCheck = isset($formData['updatecheck']) ? $formData['updatecheck'] : 1;
                $this->setSystemVar('updatecheck', $updateCheck);

                // if update checks are disabled, reset values to force new update check if re-enabled
                if ($updateCheck == 0) {
                    $this->setSystemVar('updateversion', ZikulaKernel::VERSION);
                    $this->setSystemVar('updatelastchecked', 0);
                }

                $updateFrequency = isset($formData['updatefrequency']) ? $formData['updatefrequency'] : 7;
                $this->setSystemVar('updatefrequency', $updateFrequency);

                $keyExpiry = isset($formData['keyexpiry']) ? $formData['keyexpiry'] : 0;
                if ($keyExpiry < 0 || $keyExpiry > 3600) {
                    $keyExpiry = 0;
                }
                $this->setSystemVar('keyexpiry', $keyExpiry);

                $sessionAuthKeyUA = isset($formData['sessionauthkeyua']) ? $formData['sessionauthkeyua'] : 0;
                $this->setSystemVar('sessionauthkeyua', $sessionAuthKeyUA);

                $secureDomain = isset($formData['secure_domain']) ? $formData['secure_domain'] : '';
                $this->setSystemVar('secure_domain', $secureDomain);

                $signCookies = isset($formData['signcookies']) ? $formData['signcookies'] : 1;
                $this->setSystemVar('signcookies', $signCookies);

                $signingKey = isset($formData['signingkey']) ? $formData['signingkey'] : '';
                $this->setSystemVar('signingkey', $signingKey);

                $securityLevel = isset($formData['seclevel']) ? $formData['seclevel'] : 'Medium';
                $this->setSystemVar('seclevel', $securityLevel);

                $secMedDays = isset($formData['secmeddays']) ? $formData['secmeddays'] : 7;
                if ($secMedDays < 1 || $secMedDays > 365) {
                    $secMedDays = 7;
                }
                $this->setSystemVar('secmeddays', $secMedDays);

                $secInactiveMinutes = isset($formData['secinactivemins']) ? $formData['secinactivemins'] : 20;
                if ($secInactiveMinutes < 1 || $secInactiveMinutes > 1440) {
                    $secInactiveMinutes = 7;
                }
                $this->setSystemVar('secinactivemins', $secInactiveMinutes);

                $sessionStoreToFile = isset($formData['sessionstoretofile']) ? $formData['sessionstoretofile'] : 0;
                $sessionSavePath = isset($formData['sessionsavepath']) ? $formData['sessionsavepath'] : '';

                // check session path config is writable (if method is being changed to session file storage)
                $causeLogout = false;
                $storeTypeCanBeWritten = true;
                if ($sessionStoreToFile == 1 && !empty($sessionSavePath)) {
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
                        $this->addFlash('error', $this->__('Error! Session path not writeable!'));
                        $sessionSavePath = '';
                    }
                }
                if (true === $storeTypeCanBeWritten) {
                    $this->setSystemVar('sessionstoretofile', $sessionStoreToFile);
                    $this->setSystemVar('sessionsavepath', $sessionSavePath);
                }

                if ((bool)$sessionStoreToFile != (bool)$this->get('zikula_extensions_module.api.variable')->getSystemVar('sessionstoretofile')) {
                    // logout if going from one storage to another one
                    $causeLogout = true;
                }

                $gcProbability = isset($formData['gc_probability']) ? $formData['gc_probability'] : 100;
                if ($gcProbability < 1 || $gcProbability > 10000) {
                    $gcProbability = 7;
                }
                $this->setSystemVar('gc_probability', $gcProbability);

                $sessionCsrfTokenOneTime = isset($formData['sessioncsrftokenonetime']) ? $formData['sessioncsrftokenonetime'] : 1;
                $this->setSystemVar('sessioncsrftokenonetime', $sessionCsrfTokenOneTime);

                $sessionRandRegenerate = isset($formData['sessionrandregenerate']) ? $formData['sessionrandregenerate'] : 1;
                $this->setSystemVar('sessionrandregenerate', $sessionRandRegenerate);

                $sessionRegenerate = isset($formData['sessionregenerate']) ? $formData['sessionregenerate'] : 1;
                $this->setSystemVar('sessionregenerate', $sessionRegenerate);

                $sessionRegenerateFrequency = isset($formData['sessionregeneratefreq']) ? $formData['sessionregeneratefreq'] : 10;
                if ($sessionRegenerateFrequency < 1 || $sessionRegenerateFrequency > 100) {
                    $sessionRegenerateFrequency = 10;
                }
                $this->setSystemVar('sessionregeneratefreq', $sessionRegenerateFrequency);

                $sessionIpCheck = isset($formData['sessionipcheck']) ? $formData['sessionipcheck'] : 0;
                $this->setSystemVar('sessionipcheck', $sessionIpCheck);

                $newSessionName = isset($formData['sessionname']) ? $formData['sessionname'] : $sessionName;
                if (strlen($newSessionName) < 3) {
                    $newSessionName = $sessionName;
                }

                // cause logout if we changed session name
                if ($newSessionName != $modVars['sessionname']) {
                    $causeLogout = true;
                }

                // set the session information in /src/app/config/dynamic/generated.yml
                $configDumper = $this->get('zikula.dynamic_config_dumper');
                $configDumper->setParameter('zikula.session.name', $newSessionName);
                $sessionHandlerId = $sessionStoreToFile == Constant::SESSION_STORAGE_FILE ? 'session.handler.native_file' : 'zikula_core.bridge.http_foundation.doctrine_session_handler';
                $configDumper->setParameter('zikula.session.handler_id', $sessionHandlerId);
                $sessionStorageId = $sessionStoreToFile == Constant::SESSION_STORAGE_FILE ? 'zikula_core.bridge.http_foundation.zikula_session_storage_file' : 'zikula_core.bridge.http_foundation.zikula_session_storage_doctrine';
                $configDumper->setParameter('zikula.session.storage_id', $sessionStorageId); // Symfony default is 'session.storage.native'
                $zikulaSessionSavePath = empty($sessionSavePath) ? '%kernel.cache_dir%/sessions' : $sessionSavePath;
                $configDumper->setParameter('zikula.session.save_path', $zikulaSessionSavePath);

                // set the session name in the current container
                $this->get('service_container')->setParameter('zikula.session.name', $newSessionName);
                $this->setSystemVar('sessionname', $newSessionName);
                $this->setSystemVar('sessionstoretofile', $sessionStoreToFile);

                $outputFilter = isset($formData['outputfilter']) ? $formData['outputfilter'] : 1;
                $this->setSystemVar('outputfilter', $outputFilter);

                $useIds = isset($formData['useids']) ? $formData['useids'] : 0;
                $this->setSystemVar('useids', $useIds);

                // create tmp directory for PHPIDS
                if ($useIds == 1) {
                    $idsTmpDir = $this->getParameter('kernel.cache_dir') . '/idsTmp';
                    $fs = new Filesystem();
                    if (!$fs->exists($idsTmpDir)) {
                        $fs->mkdir($idsTmpDir);
                    }
                }

                $idsSoftBlock = isset($formData['idssoftblock']) ? $formData['idssoftblock'] : 1;
                $this->setSystemVar('idssoftblock', $idsSoftBlock);

                $idsMail = isset($formData['idsmail']) ? $formData['idsmail'] : 0;
                $this->setSystemVar('idsmail', $idsMail);

                $idsFilter = isset($formData['idsfilter']) ? $formData['idsfilter'] : 'xml';
                $this->setSystemVar('idsfilter', $idsFilter);

                $validates = true;

                $idsRulePath = isset($formData['idsrulepath']) ? $formData['idsrulepath'] : 'system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml';
                if (is_readable($idsRulePath)) {
                    $this->setSystemVar('idsrulepath', $idsRulePath);
                } else {
                    $this->addFlash('error', $this->__f('Error! PHPIDS rule file %s does not exist or is not readable.', ['%s' => $idsRulePath]));
                    $validates = false;
                }

                $idsImpactThresholdOne = isset($formData['idsimpactthresholdone']) ? $formData['idsimpactthresholdone'] : 1;
                $this->setSystemVar('idsimpactthresholdone', $idsImpactThresholdOne);

                $idsImpactThresholdTwo = isset($formData['idsimpactthresholdtwo']) ? $formData['idsimpactthresholdtwo'] : 10;
                $this->setSystemVar('idsimpactthresholdtwo', $idsImpactThresholdTwo);

                $idsImpactThresholdThree = isset($formData['idsimpactthresholdthree']) ? $formData['idsimpactthresholdthree'] : 25;
                $this->setSystemVar('idsimpactthresholdthree', $idsImpactThresholdThree);

                $idsImpactThresholdFour = isset($formData['idsimpactthresholdfour']) ? $formData['idsimpactthresholdfour'] : 75;
                $this->setSystemVar('idsimpactthresholdfour', $idsImpactThresholdFour);

                $idsImpactMode = isset($formData['idsimpactmode']) ? $formData['idsimpactmode'] : 1;
                $this->setSystemVar('idsimpactmode', $idsImpactMode);

                $idsHtmlFields = isset($formData['idshtmlfields']) ? $formData['idshtmlfields'] : '';
                $idsHtmlFields = explode(PHP_EOL, $idsHtmlFields);
                $idsHtmlArray = [];
                foreach ($idsHtmlFields as $idsHtmlField) {
                    $idsHtmlField = trim($idsHtmlField);
                    if (!empty($idsHtmlField)) {
                        $idsHtmlArray[] = $idsHtmlField;
                    }
                }
                $this->setSystemVar('idshtmlfields', $idsHtmlArray);

                $idsJsonFields = isset($formData['idsjsonfields']) ? $formData['idsjsonfields'] : '';
                $idsJsonFields = explode(PHP_EOL, $idsJsonFields);
                $idsJsonArray = [];
                foreach ($idsJsonFields as $idsJsonField) {
                    $idsJsonField = trim($idsJsonField);
                    if (!empty($idsJsonField)) {
                        $idsJsonArray[] = $idsJsonField;
                    }
                }
                $this->setSystemVar('idsjsonfields', $idsJsonArray);

                $idsExceptions = isset($formData['idsexceptions']) ? $formData['idsexceptions'] : '';
                $idsExceptions = explode(PHP_EOL, $idsExceptions);
                $idsExceptionsArray = [];
                foreach ($idsExceptions as $idsException) {
                    $idsException = trim($idsException);
                    if (!empty($idsException)) {
                        $idsExceptionsArray[] = $idsException;
                    }
                }
                $this->setSystemVar('idsexceptions', $idsExceptionsArray);

                // clear all cache and compile directories
                $this->get('zikula.cache_clearer')->clear('symfony');
                $this->get('zikula.cache_clearer')->clear('legacy');

                // the module configuration has been updated successfuly
                if ($validates) {
                    $this->addFlash('status', $this->__('Done! Module configuration updated.'));
                }

                // we need to auto logout the user if essential session settings have been changed
                if (true === $causeLogout) {
                    $this->get('zikula_users_module.helper.access_helper')->logout();
                    $this->addFlash('status', $this->__('Session handling variables have changed. You must log in again.'));
                    $returnPage = urlencode($this->get('router')->generate('zikulasecuritycentermodule_config_config'));

                    return $this->redirectToRoute('zikulausersmodule_access_login', ['returnUrl' => $returnPage]);
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
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
     * @Template
     *
     * HTMLPurifier configuration.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function purifierconfigAction(Request $request, $reset = null)
    {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $purifierHelper = $this->get('zikula_security_center_module.helper.purifier_helper');

        if ($request->getMethod() == 'POST') {
            // Load HTMLPurifier Classes
            $purifier = $purifierHelper->getPurifier();

            // Update module variables.
            $config = $request->request->get('purifierConfig', null);
            $config = HTMLPurifier_Config::prepareArrayFromForm($config, false, true, true, $purifier->config->def);

            $allowed = HTMLPurifier_Config::getAllowedDirectivesForForm(true, $purifier->config->def);
            foreach ($allowed as $allowedDirective) {
                list($namespace, $directive) = $allowedDirective;

                $directiveKey = $namespace . '.' . $directive;
                $def = $purifier->config->def->info[$directiveKey];

                if (isset($config[$namespace])
                        && array_key_exists($directive, $config[$namespace])
                        && is_null($config[$namespace][$directive])) {
                    unset($config[$namespace][$directive]);

                    if (count($config[$namespace]) <= 0) {
                        unset($config[$namespace]);
                    }
                }

                if (isset($config[$namespace]) && isset($config[$namespace][$directive])) {
                    if (is_int($def)) {
                        $directiveType = abs($def);
                    } else {
                        $directiveType = (isset($def->type) ? $def->type : 0);
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
                                list($i, $v) = explode(':', $val);
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
                        && is_null($config[$namespace][$directive])) {
                    unset($config[$namespace][$directive]);

                    if (count($config[$namespace]) <= 0) {
                        unset($config[$namespace]);
                    }
                }
            }

            $this->setVar('htmlpurifierConfig', serialize($config));

            // clear all cache and compile directories
            $this->get('cache_clearer')->clear('symfony');
            $this->get('cache_clearer')->clear('legacy');

            // the module configuration has been updated successfuly
            $this->addFlash('status', $this->__('Done! Saved HTMLPurifier configuration.'));

            return $this->redirectToRoute('zikulasecuritycentermodule_config_purifierconfig');
        }

        // load the configuration page

        if (isset($reset) && $reset == 'default') {
            $purifierConfig = $purifierHelper->getPurifierConfig(['forcedefault' => true]);
            $this->addFlash('status', $this->__('Default values for HTML Purifier were successfully loaded. Please store them using the "Save" button at the bottom of this page'));
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
            HTMLPurifier_VarParser::STRING,
            HTMLPurifier_VarParser::ISTRING,
            HTMLPurifier_VarParser::TEXT,
            HTMLPurifier_VarParser::ITEXT,
            HTMLPurifier_VarParser::INT,
            HTMLPurifier_VarParser::FLOAT,
            HTMLPurifier_VarParser::BOOL,
            HTMLPurifier_VarParser::LOOKUP,
            HTMLPurifier_VarParser::ALIST,
            HTMLPurifier_VarParser::HASH
        ];

        $purifierAllowed = [];
        foreach ($allowed as $allowedDirective) {
            list($namespace, $directive) = $allowedDirective;

            if (in_array($namespace . '_' . $directive, $excluded)) {
                continue;
            }

            if ($namespace == 'Filter') {
                if (
                // Do not allow Filter.Custom for now. Causing errors.
                // TODO research why Filter.Custom is causing exceptions and correct.
                        ($directive == 'Custom')
                        // Do not allow Filter.ExtractStyleBlock* for now. Causing errors.
                        // TODO Filter.ExtractStyleBlock* requires CSSTidy
                        || (stripos($directive, 'ExtractStyleBlock') !== false)
                ) {
                    continue;
                }
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
                $directiveRec['type'] = (isset($def->type) ? $def->type : 0);
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
//                        $value = '';
//                        foreach ($directiveRec['value'] as $i => $v) {
//                            $value .= "{$i}:{$v}" . PHP_EOL;
//                        }
                        $directiveRec['value'] = json_encode($directiveRec['value']);
                        break;
                    default:
                        $directiveRec['value'] = '';
                }
            }

            $directiveRec['supported'] = in_array($directiveRec['type'], $editableTypes);

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
     * @Template
     *
     * Display the allowed html form.
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function allowedhtmlAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');

        $htmlTags = $this->getHtmlTags();

        if ($request->getMethod() == 'POST') {
            $htmlEntities = $request->request->getDigits('htmlentities', 0);
            $this->setSystemVar('htmlentities', $htmlEntities);

            // update the allowed html settings
            $allowedHtml = [];
            foreach ($htmlTags as $htmlTag => $usageTag) {
                $tagVal = (int)$request->request->getDigits('htmlallow' . $htmlTag . 'tag', 0);
                if ($tagVal != 1 && $tagVal != 2) {
                    $tagVal = 0;
                }
                $allowedHtml[$htmlTag] = $tagVal;
            }

            $this->setSystemVar('AllowableHTML', $allowedHtml);

            // clear all cache and compile directories
            $this->get('cache_clearer')->clear('symfony');
            $this->get('cache_clearer')->clear('legacy');

            $this->addFlash('status', $this->__('Done! Module configuration updated.'));

            return $this->redirectToRoute('zikulasecuritycentermodule_config_allowedhtml');
        }

        return [
            'htmlEntities' => $variableApi->getSystemVar('htmlentities'),
            'htmlPurifier' => (bool)($variableApi->getSystemVar('outputfilter') == 1),
            'configUrl' => $this->get('router')->generate('zikulasecuritycentermodule_config_config'),
            'htmlTags' => $htmlTags,
            'currentHtmlTags' => $variableApi->getSystemVar('AllowableHTML')
        ];
    }

    /**
     * Helper function to set a system var.
     *
     * @param string $variableName The variable name
     * @param mixed  $value        The new value
     */
    private function setSystemVar($variableName, $value)
    {
        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $variableApi->set(VariableApi::CONFIG, $variableName, $value);
    }

    /**
     * Utility function to return the list of available tags.
     *
     * @return array
     */
    private function getHtmlTags()
    {
        // Possible allowed HTML tags
        return [
            '!--' => 'https://www.w3schools.com/tags/tag_comment.asp',
            'a' => 'https://www.w3schools.com/tags/tag_a.asp',
            'abbr' => 'https://www.w3schools.com/tags/tag_abbr.asp',
            'acronym' => 'https://www.w3schools.com/tags/tag_acronym.asp',
            'address' => 'https://www.w3schools.com/tags/tag_address.asp',
            'applet' => 'https://www.w3schools.com/tags/tag_applet.asp',
            'area' => 'https://www.w3schools.com/tags/tag_area.asp',
            'article' => 'https://www.w3schools.com/tags/tag_article.asp',
            'aside' => 'https://www.w3schools.com/tags/tag_aside.asp',
            'audio' => 'https://www.w3schools.com/tags/tag_audio.asp',
            'b' => 'https://www.w3schools.com/tags/tag_b.asp',
            'base' => 'https://www.w3schools.com/tags/tag_base.asp',
            'basefont' => 'https://www.w3schools.com/tags/tag_basefont.asp',
            'bdo' => 'https://www.w3schools.com/tags/tag_bdo.asp',
            'big' => 'https://www.w3schools.com/tags/tag_font_style.asp',
            'blockquote' => 'https://www.w3schools.com/tags/tag_blockquote.asp',
            'br' => 'https://www.w3schools.com/tags/tag_br.asp',
            'button' => 'https://www.w3schools.com/tags/tag_button.asp',
            'canvas' => 'https://www.w3schools.com/tags/tag_canvas.asp',
            'caption' => 'https://www.w3schools.com/tags/tag_caption.asp',
            'center' => 'https://www.w3schools.com/tags/tag_center.asp',
            'cite' => 'https://www.w3schools.com/tags/tag_cite.asp',
            'code' => 'https://www.w3schools.com/tags/tag_code.asp',
            'col' => 'https://www.w3schools.com/tags/tag_col.asp',
            'colgroup' => 'https://www.w3schools.com/tags/tag_colgroup.asp',
            'command' => 'https://www.w3schools.com/tags/tag_command.asp',
            'datalist' => 'https://www.w3schools.com/tags/tag_datalist.asp',
            'dd' => 'https://www.w3schools.com/tags/tag_dd.asp',
            'del' => 'https://www.w3schools.com/tags/tag_del.asp',
            'details' => 'https://www.w3schools.com/tags/tag_details.asp',
            'dfn' => 'https://www.w3schools.com/tags/tag_dfn.asp',
            'dir' => 'https://www.w3schools.com/tags/tag_dir.asp',
            'div' => 'https://www.w3schools.com/tags/tag_div.asp',
            'dl' => 'https://www.w3schools.com/tags/tag_dl.asp',
            'dt' => 'https://www.w3schools.com/tags/tag_dt.asp',
            'em' => 'https://www.w3schools.com/tags/tag_em.asp',
            'embed' => 'https://www.w3schools.com/tags/tag_embed.asp',
            'fieldset' => 'https://www.w3schools.com/tags/tag_fieldset.asp',
            'figcaption' => 'https://www.w3schools.com/tags/tag_figcaption.asp',
            'figure' => 'https://www.w3schools.com/tags/tag_figure.asp',
            'font' => 'https://www.w3schools.com/tags/tag_font.asp',
            'footer' => 'https://www.w3schools.com/tags/tag_footer.asp',
            'form' => 'https://www.w3schools.com/tags/tag_form.asp',
            'h1' => 'https://www.w3schools.com/tags/tag_hn.asp',
            'h2' => 'https://www.w3schools.com/tags/tag_hn.asp',
            'h3' => 'https://www.w3schools.com/tags/tag_hn.asp',
            'h4' => 'https://www.w3schools.com/tags/tag_hn.asp',
            'h5' => 'https://www.w3schools.com/tags/tag_hn.asp',
            'h6' => 'https://www.w3schools.com/tags/tag_hn.asp',
            'header' => 'https://www.w3schools.com/tags/tag_header.asp',
            'hgroup' => 'https://www.w3schools.com/tags/tag_hgroup.asp',
            'hr' => 'https://www.w3schools.com/tags/tag_hr.asp',
            'i' => 'https://www.w3schools.com/tags/tag_i.asp',
            'iframe' => 'https://www.w3schools.com/tags/tag_iframe.asp',
            'img' => 'https://www.w3schools.com/tags/tag_img.asp',
            'input' => 'https://www.w3schools.com/tags/tag_input.asp',
            'ins' => 'https://www.w3schools.com/tags/tag_ins.asp',
            'keygen' => 'https://www.w3schools.com/tags/tag_keygen.asp',
            'kbd' => 'https://www.w3schools.com/tags/tag_kbd.asp',
            'label' => 'https://www.w3schools.com/tags/tag_label.asp',
            'legend' => 'https://www.w3schools.com/tags/tag_legend.asp',
            'li' => 'https://www.w3schools.com/tags/tag_li.asp',
            'map' => 'https://www.w3schools.com/tags/tag_map.asp',
            'mark' => 'https://www.w3schools.com/tags/tag_mark.asp',
            'menu' => 'https://www.w3schools.com/tags/tag_menu.asp',
            'marquee' => '',
            'meter' => 'https://www.w3schools.com/tags/tag_meter.asp',
            'nav' => 'https://www.w3schools.com/tags/tag_nav.asp',
            'nobr' => '',
            'object' => 'https://www.w3schools.com/tags/tag_object.asp',
            'ol' => 'https://www.w3schools.com/tags/tag_ol.asp',
            'optgroup' => 'https://www.w3schools.com/tags/tag_optgroup.asp',
            'option' => 'https://www.w3schools.com/tags/tag_option.asp',
            'output' => 'https://www.w3schools.com/tags/tag_output.asp',
            'p' => 'https://www.w3schools.com/tags/tag_p.asp',
            'param' => 'https://www.w3schools.com/tags/tag_param.asp',
            'pre' => 'https://www.w3schools.com/tags/tag_pre.asp',
            'progress' => 'https://www.w3schools.com/tags/tag_progress.asp',
            'q' => 'https://www.w3schools.com/tags/tag_q.asp',
            'rp' => 'https://www.w3schools.com/tags/tag_rp.asp',
            'rt' => 'https://www.w3schools.com/tags/tag_rt.asp',
            'ruby' => 'https://www.w3schools.com/tags/tag_ruby.asp',
            's' => 'https://www.w3schools.com/tags/tag_strike.asp',
            'samp' => 'https://www.w3schools.com/tags/tag_samp.asp',
            'script' => 'https://www.w3schools.com/tags/tag_script.asp',
            'section' => 'https://www.w3schools.com/tags/tag_section.asp',
            'select' => 'https://www.w3schools.com/tags/tag_select.asp',
            'small' => 'https://www.w3schools.com/tags/tag_small.asp',
            'source' => 'https://www.w3schools.com/tags/tag_source.asp',
            'span' => 'https://www.w3schools.com/tags/tag_span.asp',
            'strike' => 'https://www.w3schools.com/tags/tag_strike.asp',
            'strong' => 'https://www.w3schools.com/tags/tag_strong.asp',
            'sub' => 'https://www.w3schools.com/tags/tag_sup.asp',
            'summary' => 'https://www.w3schools.com/tags/tag_summary.asp',
            'sup' => 'https://www.w3schools.com/tags/tag_sup.asp',
            'table' => 'https://www.w3schools.com/tags/tag_table.asp',
            'tbody' => 'https://www.w3schools.com/tags/tag_tbody.asp',
            'td' => 'https://www.w3schools.com/tags/tag_td.asp',
            'textarea' => 'https://www.w3schools.com/tags/tag_textarea.asp',
            'tfoot' => 'https://www.w3schools.com/tags/tag_tfoot.asp',
            'th' => 'https://www.w3schools.com/tags/tag_th.asp',
            'thead' => 'https://www.w3schools.com/tags/tag_thead.asp',
            'time' => 'https://www.w3schools.com/tags/tag_time.asp',
            'tr' => 'https://www.w3schools.com/tags/tag_tr.asp',
            'tt' => 'https://www.w3schools.com/tags/tag_font_style.asp',
            'u' => 'https://www.w3schools.com/tags/tag_u.asp',
            'ul' => 'https://www.w3schools.com/tags/tag_ul.asp',
            'var' => 'https://www.w3schools.com/tags/tag_var.asp',
            'video' => 'https://www.w3schools.com/tags/tag_video.asp',
            'wbr' => 'https://www.w3schools.com/tags/tag_wbr.asp'
        ];
    }
}
