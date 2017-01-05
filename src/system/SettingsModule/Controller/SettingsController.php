<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Controller;

use DateUtil;
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

        $installedLanguageNames = $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames();

        $capabilityApi = $this->get('zikula_extensions_module.api.capability');
        $userModules = $capabilityApi->getExtensionsCapableOf(CapabilityApiInterface::USER);
        $profileModules = $capabilityApi->getExtensionsCapableOf(CapabilityApiInterface::PROFILE);
        $messageModules = $capabilityApi->getExtensionsCapableOf(CapabilityApiInterface::MESSAGE);

        $form = $this->createForm('Zikula\SettingsModule\Form\Type\MainSettingsType',
            $this->getSystemVars(),
            [
                'translator' => $this->get('translator.default'),
                'languages' => $installedLanguageNames,
                'modules' => $this->formatModuleArrayForSelect($userModules),
                'profileModules' => $this->formatModuleArrayForSelect($profileModules),
                'messageModules' => $this->formatModuleArrayForSelect($messageModules)
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setSystemVars($form->getData());
                $this->addFlash('status', $this->__('Done! Configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulasettingsmodule_settings_main');
        }

        return [
            'languages' => $installedLanguageNames,
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
                'languages' => $this->container->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(),
                'timezones' => DateUtil::getTimezones()
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
            'intl_installed' => extension_loaded('intl'),
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
        $phpinfo = str_replace('module_Zend Optimizer', 'module_Zend_Optimizer', preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo));

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
        return $this->get('zikula_extensions_module.api.variable')->getSystemVar($name, $default);
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
