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

use CacheUtil;
use DataUtil;
use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_VarParser;
use Zikula_Core;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SecurityCenterModule\Util as SecurityCenterUtil;
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

        $form = $this->createForm('Zikula\SecurityCenterModule\Form\Type\ConfigType',
            $modVars, [
                'translator' => $this->get('translator.default')
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // Update module variables.
                $updateCheck = isset($formData['updatecheck']) ? $formData['updatecheck'] : 1;
                $this->setSystemVar('updatecheck', $updateCheck);

                // if update checks are disabled, reset values to force new update check if re-enabled
                if ($updateCheck == 0) {
                    $this->setSystemVar('updateversion', Zikula_Core::VERSION_NUM);
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
                    $causeLogout = is_dir($sessionSavePath) ? is_writable($sessionSavePath) : false;
                    $storeTypeCanBeWritten = $causeLogout;

                    if ($causeLogout == false) {
                        // an error occured - we do not change the way of storing session data
                        $this->addFlash('error', $this->__('Error! Session path not writeable!'));
                    }
                }
                if ($storeTypeCanBeWritten == true) {
                    $this->setSystemVar('sessionstoretofile', $sessionStoreToFile);
                    $this->setSystemVar('sessionsavepath', $sessionSavePath);
                }

                if ((bool)$sessionStoreToFile != (bool)$this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'sessionstoretofile')) {
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

                // set the session name in custom_parameters.yml
                $configDumper = $this->get('zikula.dynamic_config_dumper');
                $configDumper->setParameter('zikula.session.name', $newSessionName);

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
                    $idsTmpDir = CacheUtil::getLocalDir() . '/idsTmp';
                    if (!file_exists($idsTmpDir)) {
                        CacheUtil::clearLocalDir('idsTmp');
                    }
                }

                $idsSoftBlock = isset($formData['idssoftblock']) ? $formData['idssoftblock'] : 1;
                $this->setSystemVar('idssoftblock', $idsSoftBlock);

                $idsMail = isset($formData['idsmail']) ? $formData['idsmail'] : 0;
                $this->setSystemVar('idsmail', $idsMail);

                $idsFilter = isset($formData['idsfilter']) ? $formData['idsfilter'] : 'xml';
                $this->setSystemVar('idsfilter', $idsFilter);


                $validates = true;

                $idsRulePath = isset($formData['idsrulepath']) ? $formData['idsrulepath'] : 'Resources/config/zikula_default.xml';
                $idsRulePath = DataUtil::formatForOS($idsRulePath);
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

                // we need to auto logout the user if they changed from DB to FILE
                if ($causeLogout == true) {
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

        if ($request->getMethod() == 'POST') {
            // Load HTMLPurifier Classes
            $purifier = SecurityCenterUtil::getpurifier();

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
            $purifierconfig = SecurityCenterUtil::getpurifierconfig(['forcedefault' => true]);
            $this->addFlash('status', $this->__('Default values for HTML Purifier were successfully loaded. Please store them using the "Save" button at the bottom of this page'));
        } else {
            $purifierconfig = SecurityCenterUtil::getpurifierconfig(['forcedefault' => false]);
        }

        $purifier = new HTMLPurifier($purifierconfig);

        $config = $purifier->config;

        if (is_array($config) && isset($config[0])) {
            $config = $config[1];
        }

        $allowed = HTMLPurifier_Config::getAllowedDirectivesForForm(true, $config->def);

        // list of excluded directives, format is $namespace_$directive
        $excluded = ['Cache_SerializerPath'];

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
            // Editing for only these types is supported
            $directiveRec['supported'] = (($directiveRec['type'] == HTMLPurifier_VarParser::STRING)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::ISTRING)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::TEXT)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::ITEXT)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::INT)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::FLOAT)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::BOOL)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::LOOKUP)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::ALIST)
                    || ($directiveRec['type'] == HTMLPurifier_VarParser::HASH));

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
            'htmlEntities' => $variableApi->get(VariableApi::CONFIG, 'htmlentities'),
            'htmlPurifier' => (bool)($variableApi->get(VariableApi::CONFIG, 'outputfilter') == 1),
            'configUrl' => $this->get('router')->generate('zikulasecuritycentermodule_config_config'),
            'htmlTags' => $htmlTags,
            'currentHtmlTags' => $variableApi->get(VariableApi::CONFIG, 'AllowableHTML')
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
            '!--' => 'http://www.w3schools.com/html5/tag_comment.asp',
            'a' => 'http://www.w3schools.com/html5/tag_a.asp',
            'abbr' => 'http://www.w3schools.com/html5/tag_abbr.asp',
            'acronym' => 'http://www.w3schools.com/html5/tag_acronym.asp',
            'address' => 'http://www.w3schools.com/html5/tag_address.asp',
            'applet' => 'http://www.w3schools.com/tags/tag_applet.asp',
            'area' => 'http://www.w3schools.com/html5/tag_area.asp',
            'article' => 'http://www.w3schools.com/html5/tag_article.asp',
            'aside' => 'http://www.w3schools.com/html5/tag_aside.asp',
            'audio' => 'http://www.w3schools.com/html5/tag_audio.asp',
            'b' => 'http://www.w3schools.com/html5/tag_b.asp',
            'base' => 'http://www.w3schools.com/html5/tag_base.asp',
            'basefont' => 'http://www.w3schools.com/tags/tag_basefont.asp',
            'bdo' => 'http://www.w3schools.com/html5/tag_bdo.asp',
            'big' => 'http://www.w3schools.com/tags/tag_font_style.asp',
            'blockquote' => 'http://www.w3schools.com/html5/tag_blockquote.asp',
            'br' => 'http://www.w3schools.com/html5/tag_br.asp',
            'button' => 'http://www.w3schools.com/html5/tag_button.asp',
            'canvas' => 'http://www.w3schools.com/html5/tag_canvas.asp',
            'caption' => 'http://www.w3schools.com/html5/tag_caption.asp',
            'center' => 'http://www.w3schools.com/tags/tag_center.asp',
            'cite' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'code' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'col' => 'http://www.w3schools.com/html5/tag_col.asp',
            'colgroup' => 'http://www.w3schools.com/html5/tag_colgroup.asp',
            'command' => 'http://www.w3schools.com/html5/tag_command.asp',
            'datalist' => 'http://www.w3schools.com/html5/tag_datalist.asp',
            'dd' => 'http://www.w3schools.com/html5/tag_dd.asp',
            'del' => 'http://www.w3schools.com/html5/tag_del.asp',
            'details' => 'http://www.w3schools.com/html5/tag_details.asp',
            'dfn' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'dir' => 'http://www.w3schools.com/tags/tag_dir.asp',
            'div' => 'http://www.w3schools.com/html5/tag_div.asp',
            'dl' => 'http://www.w3schools.com/html5/tag_dl.asp',
            'dt' => 'http://www.w3schools.com/html5/tag_dt.asp',
            'em' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'embed' => 'http://www.w3schools.com/html5/tag_embed.asp',
            'fieldset' => 'http://www.w3schools.com/html5/tag_fieldset.asp',
            'figcaption' => 'http://www.w3schools.com/html5/tag_figcaption.asp',
            'figure' => 'http://www.w3schools.com/html5/tag_figure.asp',
            'font' => 'http://www.w3schools.com/tags/tag_font.asp',
            'footer' => 'http://www.w3schools.com/html5/tag_footer.asp',
            'form' => 'http://www.w3schools.com/html5/tag_form.asp',
            'h1' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h2' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h3' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h4' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h5' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'h6' => 'http://www.w3schools.com/html5/tag_hn.asp',
            'header' => 'http://www.w3schools.com/html5/tag_header.asp',
            'hgroup' => 'http://www.w3schools.com/html5/tag_hgroup.asp',
            'hr' => 'http://www.w3schools.com/html5/tag_hr.asp',
            'i' => 'http://www.w3schools.com/html5/tag_i.asp',
            'iframe' => 'http://www.w3schools.com/html5/tag_iframe.asp',
            'img' => 'http://www.w3schools.com/html5/tag_img.asp',
            'input' => 'http://www.w3schools.com/html5/tag_input.asp',
            'ins' => 'http://www.w3schools.com/html5/tag_ins.asp',
            'keygen' => 'http://www.w3schools.com/html5/tag_keygen.asp',
            'kbd' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'label' => 'http://www.w3schools.com/html5/tag_label.asp',
            'legend' => 'http://www.w3schools.com/html5/tag_legend.asp',
            'li' => 'http://www.w3schools.com/html5/tag_li.asp',
            'map' => 'http://www.w3schools.com/html5/tag_map.asp',
            'mark' => 'http://www.w3schools.com/html5/tag_mark.asp',
            'menu' => 'http://www.w3schools.com/html5/tag_menu.asp',
            'marquee' => '',
            'meter' => 'http://www.w3schools.com/html5/tag_meter.asp',
            'nav' => 'http://www.w3schools.com/html5/tag_nav.asp',
            'nobr' => '',
            'object' => 'http://www.w3schools.com/html5/tag_object.asp',
            'ol' => 'http://www.w3schools.com/html5/tag_ol.asp',
            'optgroup' => 'http://www.w3schools.com/html5/tag_optgroup.asp',
            'option' => 'http://www.w3schools.com/html5/tag_option.asp',
            'output' => 'http://www.w3schools.com/html5/tag_output.asp',
            'p' => 'http://www.w3schools.com/html5/tag_p.asp',
            'param' => 'http://www.w3schools.com/html5/tag_param.asp',
            'pre' => 'http://www.w3schools.com/html5/tag_pre.asp',
            'progress' => 'http://www.w3schools.com/html5/tag_progress.asp',
            'q' => 'http://www.w3schools.com/html5/tag_q.asp',
            'rp' => 'http://www.w3schools.com/html5/tag_rp.asp',
            'rt' => 'http://www.w3schools.com/html5/tag_rt.asp',
            'ruby' => 'http://www.w3schools.com/html5/tag_ruby.asp',
            's' => 'http://www.w3schools.com/tags/tag_strike.asp',
            'samp' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'script' => 'http://www.w3schools.com/html5/tag_script.asp',
            'section' => 'http://www.w3schools.com/html5/tag_section.asp',
            'select' => 'http://www.w3schools.com/html5/tag_select.asp',
            'small' => 'http://www.w3schools.com/html5/tag_small.asp',
            'source' => 'http://www.w3schools.com/html5/tag_source.asp',
            'span' => 'http://www.w3schools.com/html5/tag_span.asp',
            'strike' => 'http://www.w3schools.com/tags/tag_strike.asp',
            'strong' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'sub' => 'http://www.w3schools.com/html5/tag_sup.asp',
            'summary' => 'http://www.w3schools.com/html5/tag_summary.asp',
            'sup' => 'http://www.w3schools.com/html5/tag_sup.asp',
            'table' => 'http://www.w3schools.com/html5/tag_table.asp',
            'tbody' => 'http://www.w3schools.com/html5/tag_tbody.asp',
            'td' => 'http://www.w3schools.com/html5/tag_td.asp',
            'textarea' => 'http://www.w3schools.com/html5/tag_textarea.asp',
            'tfoot' => 'http://www.w3schools.com/html5/tag_tfoot.asp',
            'th' => 'http://www.w3schools.com/html5/tag_th.asp',
            'thead' => 'http://www.w3schools.com/html5/tag_thead.asp',
            'time' => 'http://www.w3schools.com/html5/tag_time.asp',
            'tr' => 'http://www.w3schools.com/html5/tag_tr.asp',
            'tt' => 'http://www.w3schools.com/tags/tag_font_style.asp',
            'u' => 'http://www.w3schools.com/tags/tag_u.asp',
            'ul' => 'http://www.w3schools.com/html5/tag_ul.asp',
            'var' => 'http://www.w3schools.com/html5/tag_phrase_elements.asp',
            'video' => 'http://www.w3schools.com/html5/tag_video.asp',
            'wbr' => 'http://www.w3schools.com/html5/tag_wbr.asp'
        ];
    }
}
