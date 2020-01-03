<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\SecurityCenterModule\Constant;
use Zikula\SecurityCenterModule\Form\Type\ConfigType;
use Zikula\SecurityCenterModule\Helper\PurifierHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Helper\AccessHelper;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaSecurityCenterModule/Config/config.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function configAction(
        Request $request,
        RouterInterface $router,
        VariableApiInterface $variableApi,
        DynamicConfigDumper $configDumper,
        CacheClearer $cacheClearer,
        AccessHelper $accessHelper
    ) {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $modVars = $variableApi->getAll(VariableApi::CONFIG);

        $sessionName = $this->container->getParameter('zikula.session.name');
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

                // if update checks are disabled, reset values to force new update check if re-enabled
                if (0 === $updateCheck) {
                    $variableApi->set(VariableApi::CONFIG, 'updateversion', ZikulaKernel::VERSION);
                    $variableApi->set(VariableApi::CONFIG, 'updatelastchecked', 0);
                }

                $updateFrequency = $formData['updatefrequency'] ?? 7;
                $variableApi->set(VariableApi::CONFIG, 'updatefrequency', $updateFrequency);

                $keyExpiry = $formData['keyexpiry'] ?? 0;
                if ($keyExpiry < 0 || $keyExpiry > 3600) {
                    $keyExpiry = 0;
                }
                $variableApi->set(VariableApi::CONFIG, 'keyexpiry', $keyExpiry);

                $sessionAuthKeyUA = $formData['sessionauthkeyua'] ?? 0;
                $variableApi->set(VariableApi::CONFIG, 'sessionauthkeyua', $sessionAuthKeyUA);

                $secureDomain = $formData['secure_domain'] ?? '';
                $variableApi->set(VariableApi::CONFIG, 'secure_domain', $secureDomain);

                $signCookies = $formData['signcookies'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'signcookies', $signCookies);

                $signingKey = $formData['signingkey'] ?? '';
                $variableApi->set(VariableApi::CONFIG, 'signingkey', $signingKey);

                $securityLevel = $formData['seclevel'] ?? 'Medium';
                $variableApi->set(VariableApi::CONFIG, 'seclevel', $securityLevel);

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
                        $this->addFlash('error', $this->__('Error! Session path not writeable!'));
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

                $gcProbability = $formData['gc_probability'] ?? 100;
                if ($gcProbability < 1 || $gcProbability > 10000) {
                    $gcProbability = 7;
                }
                $variableApi->set(VariableApi::CONFIG, 'gc_probability', $gcProbability);

                $sessionCsrfTokenOneTime = $formData['sessioncsrftokenonetime'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'sessioncsrftokenonetime', $sessionCsrfTokenOneTime);

                $sessionRandRegenerate = $formData['sessionrandregenerate'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'sessionrandregenerate', $sessionRandRegenerate);

                $sessionRegenerate = $formData['sessionregenerate'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'sessionregenerate', $sessionRegenerate);

                $sessionRegenerateFrequency = $formData['sessionregeneratefreq'] ?? 10;
                if ($sessionRegenerateFrequency < 1 || $sessionRegenerateFrequency > 100) {
                    $sessionRegenerateFrequency = 10;
                }
                $variableApi->set(VariableApi::CONFIG, 'sessionregeneratefreq', $sessionRegenerateFrequency);

                $newSessionName = $formData['sessionname'] ?? $sessionName;
                if (mb_strlen($newSessionName) < 3) {
                    $newSessionName = $sessionName;
                }

                // cause logout if we changed session name
                if ($newSessionName !== $modVars['sessionname']) {
                    $causeLogout = true;
                }

                // set the session information in /src/app/config/dynamic/generated.yml
                $configDumper->setParameter('zikula.session.name', $newSessionName);
                $sessionHandlerId = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'session.handler.native_file' : 'zikula_core.bridge.http_foundation.doctrine_session_handler';
                $configDumper->setParameter('zikula.session.handler_id', $sessionHandlerId);
                $sessionStorageId = Constant::SESSION_STORAGE_FILE === $sessionStoreToFile ? 'zikula_core.bridge.http_foundation.zikula_session_storage_file' : 'zikula_core.bridge.http_foundation.zikula_session_storage_doctrine';
                $configDumper->setParameter('zikula.session.storage_id', $sessionStorageId); // Symfony default is 'session.storage.native'
                $zikulaSessionSavePath = empty($sessionSavePath) ? '%kernel.cache_dir%/sessions' : $sessionSavePath;
                $configDumper->setParameter('zikula.session.save_path', $zikulaSessionSavePath);

                $variableApi->set(VariableApi::CONFIG, 'sessionname', $newSessionName);
                $variableApi->set(VariableApi::CONFIG, 'sessionstoretofile', $sessionStoreToFile);

                $outputFilter = $formData['outputfilter'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'outputfilter', $outputFilter);

                $useIds = $formData['useids'] ?? 0;
                $variableApi->set(VariableApi::CONFIG, 'useids', $useIds);

                // create tmp directory for PHPIDS
                if (1 === $useIds) {
                    $idsTmpDir = $this->container->getParameter('kernel.cache_dir') . '/idsTmp';
                    $fs = new Filesystem();
                    if (!$fs->exists($idsTmpDir)) {
                        $fs->mkdir($idsTmpDir);
                    }
                }

                $idsSoftBlock = $formData['idssoftblock'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'idssoftblock', $idsSoftBlock);

                $idsMail = $formData['idsmail'] ?? 0;
                $variableApi->set(VariableApi::CONFIG, 'idsmail', $idsMail);

                $idsFilter = $formData['idsfilter'] ?? 'xml';
                $variableApi->set(VariableApi::CONFIG, 'idsfilter', $idsFilter);

                $validates = true;

                $idsRulePath = $formData['idsrulepath'] ?? 'system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml';
                if (is_readable($idsRulePath)) {
                    $variableApi->set(VariableApi::CONFIG, 'idsrulepath', $idsRulePath);
                } else {
                    $this->addFlash('error', $this->__f('Error! PHPIDS rule file %s does not exist or is not readable.', ['%s' => $idsRulePath]));
                    $validates = false;
                }

                $idsImpactThresholdOne = $formData['idsimpactthresholdone'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdone', $idsImpactThresholdOne);

                $idsImpactThresholdTwo = $formData['idsimpactthresholdtwo'] ?? 10;
                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdtwo', $idsImpactThresholdTwo);

                $idsImpactThresholdThree = $formData['idsimpactthresholdthree'] ?? 25;
                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdthree', $idsImpactThresholdThree);

                $idsImpactThresholdFour = $formData['idsimpactthresholdfour'] ?? 75;
                $variableApi->set(VariableApi::CONFIG, 'idsimpactthresholdfour', $idsImpactThresholdFour);

                $idsImpactMode = $formData['idsimpactmode'] ?? 1;
                $variableApi->set(VariableApi::CONFIG, 'idsimpactmode', $idsImpactMode);

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

                // clear all cache and compile directories
                $cacheClearer->clear('symfony');
                $cacheClearer->clear('legacy');

                // the module configuration has been updated successfuly
                if ($validates) {
                    $this->addFlash('status', $this->__('Done! Module configuration updated.'));
                }

                // we need to auto logout the user if essential session settings have been changed
                if (true === $causeLogout) {
                    $accessHelper->logout();
                    $this->addFlash('status', $this->__('Session handling variables have changed. You must log in again.'));
                    $returnPage = urlencode($router->generate('zikulasecuritycentermodule_config_config'));

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
     * @Template("@ZikulaSecurityCenterModule/Config/purifierconfig.html.twig")
     *
     * HTMLPurifier configuration.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function purifierconfigAction(
        Request $request,
        PurifierHelper $purifierHelper,
        CacheClearer $cacheClearer,
        string $reset = null
    ) {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ('POST' === $request->getMethod()) {
            // Load HTMLPurifier Classes
            $purifier = $purifierHelper->getPurifier();

            // Update module variables.
            $config = $request->request->get('purifierConfig');
            $config = HTMLPurifier_Config::prepareArrayFromForm($config, false, true, true, $purifier->config->def);

            $allowed = HTMLPurifier_Config::getAllowedDirectivesForForm(true, $purifier->config->def);
            foreach ($allowed as list($namespace, $directive)) {
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
            $this->addFlash('status', $this->__('Done! Saved HTMLPurifier configuration.'));

            return $this->redirectToRoute('zikulasecuritycentermodule_config_purifierconfig');
        }

        // load the configuration page

        if (isset($reset) && 'default' === $reset) {
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
        foreach ($allowed as list($namespace, $directive)) {
            if (in_array($namespace . '_' . $directive, $excluded, true)) {
                continue;
            }

            if ('Filter' === $namespace) {
                if (
                // Do not allow Filter.Custom for now. Causing errors.
                // TODO research why Filter.Custom is causing exceptions and correct.
                        ('Custom' === $directive)
                        // Do not allow Filter.ExtractStyleBlock* for now. Causing errors.
                        // TODO Filter.ExtractStyleBlock* requires CSSTidy
                        || (false !== mb_stripos($directive, 'ExtractStyleBlock'))
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
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function allowedhtmlAction(
        Request $request,
        RouterInterface $router,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer
    ) {
        if (!$this->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $htmlTags = $this->getHtmlTags();

        if ('POST' === $request->getMethod()) {
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

            $this->addFlash('status', $this->__('Done! Module configuration updated.'));

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

    /**
     * Utility function to return the list of available tags.
     */
    private function getHtmlTags(): array
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
            's' => 'https://www.w3schools.com/tags/tag_s.asp',
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
