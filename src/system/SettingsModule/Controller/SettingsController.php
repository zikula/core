<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SettingsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class SettingsController
 * @Route("")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("")
     * @Theme("admin")
     * @Template
     *
     * Set locale settings for entire site.
     *
     * @return Response|RedirectResponse
     */
    public function mainAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $userModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(CapabilityApiInterface::USER);
        $profileModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(CapabilityApiInterface::PROFILE);
        $messageModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(CapabilityApiInterface::MESSAGE);

        $form = $this->createForm('Zikula\SettingsModule\Form\Type\MainSettingsType',
            $this->getSystemVars(),
            [
                'translator' => $this->get('translator.default'),
                'languages' => \ZLanguage::getInstalledLanguageNames(),
                'modules' => $this->formatModuleArrayForSelect($userModules),
                'profileModules' => $this->formatModuleArrayForSelect($profileModules),
                'messageModules' => $this->formatModuleArrayForSelect($messageModules)
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                $this->setSystemVars($data);
                $this->addFlash('status', $this->__('Done! Configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulasettingsmodule_settings_main');
        }

        return [
            'languages' => \ZLanguage::getInstalledLanguageNames(),
            'zlibEnabled' => extension_loaded('zlib'),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/locale", options={"i18n"=false})
     * @Theme("admin")
     * @Template
     *
     * Set locale settings for entire site.
     *
     * @return Response|RedirectResponse
     */
    public function localeAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\SettingsModule\Form\Type\LocaleSettingsType',
            [
                'multilingual' => (bool)$this->getSystemVar('multilingual'),
                'languageurl' => $this->getSystemVar('languageurl'),
                'language_detect' => (bool)$this->getSystemVar('language_detect'),
                'language_i18n' => $this->getSystemVar('language_i18n'),
                'timezone_offset' => $this->getSystemVar('timezone_offset'),
                'idnnames' => (bool)$this->getSystemVar('idnnames'),
            ],
            [
                'translator' => $this->get('translator.default'),
                'languages' => \ZLanguage::getInstalledLanguageNames(),
                'timezones' => \DateUtil::getTimezones()
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                if (false == $data['multilingual']) {
                    $data['language_detect'] = false;
                    $this->get('zikula_extensions_module.api.variable')->del(VariableApi::CONFIG, 'language');
                }
                $this->setSystemVars($data);
                // update the custom_parameters.yml file
                $yamlManager = new YamlDumper($this->get('kernel')->getRootDir() .'/config');
                $yamlManager->setParameter('locale', $data['language_i18n']);

                $this->get('zikularoutesmodule.multilingual_routing_helper')->reloadMultilingualRoutingSettings(); // resets config/dynamic/generated.yml
                $this->addFlash('status', $this->__('Done! Localization configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulasettingsmodule_settings_locale');
        }

        return [
            'server_timezone' => \DateUtil::getTimezoneAbbr(),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/phpinfo")
     * @Theme("admin")
     * @Template
     *
     * Displays the content of {@see phpinfo()}.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function phpinfoAction()
    {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
        $phpinfo = str_replace("module_Zend Optimizer", "module_Zend_Optimizer", preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo));

        return [
            'phpinfo' => $phpinfo
        ];
    }

    /**
     * Get a system variable.
     * @param $name
     * @param null $default
     * @return mixed
     */
    private function getSystemVar($name, $default = null)
    {
        // service caches values already
        return $this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, $name, $default);
    }

    /**
     * Get all the system vars.
     * @return array
     */
    private function getSystemVars()
    {
        return $this->get('zikula_extensions_module.api.variable')->getAll(VariableApi::CONFIG);
    }

    /**
     * Set system variables from array [<name> => <value>, <name> => <value>, ...]
     * @param array $data
     */
    private function setSystemVars(array $data)
    {
        foreach ($data as $name => $value) {
            $this->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, $name, $value);
        }
    }

    /**
     * Prepare an array of module names and displaynames with choices_as_values
     * @param ExtensionEntity[] $modules
     * @return array
     */
    private function formatModuleArrayForSelect(array $modules)
    {
        $return = [];
        foreach ($modules as $module) {
            $return[$module->getDisplayname()] = $module->getName();
        }
        ksort($return);

        return $return;
    }
}
