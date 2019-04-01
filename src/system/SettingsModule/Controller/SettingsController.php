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

namespace Zikula\SettingsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\RoutesModule\Helper\MultilingualRoutingHelper;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\SettingsModule\Form\Type\LocaleSettingsType;
use Zikula\SettingsModule\Form\Type\MainSettingsType;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Collector\MessageModuleCollector;
use Zikula\UsersModule\Collector\ProfileModuleCollector;

/**
 * Class SettingsController
 * @Route("")
 */
class SettingsController extends AbstractController
{
    /**
     * @Route("")
     * @Theme("admin")
     * @Template("ZikulaSettingsModule:Settings:main.html.twig")
     *
     * Settings for entire site.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     *
     * @return array|RedirectResponse
     */
    public function mainAction(
        Request $request,
        LocaleApiInterface $localeApi,
        VariableApiInterface $variableApi,
        ExtensionRepositoryInterface $extensionRepository,
        MessageModuleCollector $messageModuleCollector,
        ProfileModuleCollector $profileModuleCollector
    ) {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $installedLanguageNames = $localeApi->getSupportedLocaleNames(null, $request->getLocale());
        $profileModules = $profileModuleCollector->getKeys();
        $messageModules = $messageModuleCollector->getKeys();

        $form = $this->createForm(MainSettingsType::class,
            $variableApi->getAll(VariableApi::CONFIG), [
                'languages' => $installedLanguageNames,
                'profileModules' => $this->formatModuleArrayForSelect($extensionRepository, $profileModules),
                'messageModules' => $this->formatModuleArrayForSelect($extensionRepository, $messageModules)
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                foreach ($data as $name => $value) {
                    $variableApi->set(VariableApi::CONFIG, $name, $value);
                }
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
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/locale", options={"i18n"=false})
     * @Theme("admin")
     * @Template("ZikulaSettingsModule:Settings:locale.html.twig")
     *
     * Locale settings for entire site.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     *
     * @return array|RedirectResponse
     */
    public function localeAction(
        Request $request,
        LocaleApiInterface $localeApi,
        VariableApiInterface $variableApi,
        MultilingualRoutingHelper $multilingualRoutingHelper
    ) {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(LocaleSettingsType::class,
            [
                'multilingual' => (bool)$variableApi->getSystemVar('multilingual'),
                'languageurl' => $variableApi->getSystemVar('languageurl'),
                'language_detect' => (bool)$variableApi->getSystemVar('language_detect'),
                'language_i18n' => $variableApi->getSystemVar('language_i18n'),
                'timezone' => $variableApi->getSystemVar('timezone'),
                'idnnames' => (bool)$variableApi->getSystemVar('idnnames'),
            ], [
                'languages' => $localeApi->getSupportedLocaleNames(null, $request->getLocale())
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                if (false === $data['multilingual']) {
                    $data['language_detect'] = false;
                    $variableApi->del(VariableApi::CONFIG, 'language');
                }
                foreach ($data as $name => $value) {
                    $variableApi->set(VariableApi::CONFIG, $name, $value);
                }
                $variableApi->set(VariableApi::CONFIG, 'locale', $data['language_i18n']); // @todo which variable are we using?

                $multilingualRoutingHelper->reloadMultilingualRoutingSettings(); // resets config/dynamic/generated.yml & custom_parameters.yml
                if (null !== $request->getSession()) {
                    $request->getSession()->set('_locale', $data['language_i18n']);
                }
                $this->addFlash('status', $this->__('Done! Localization configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulasettingsmodule_settings_locale');
        }

        return [
            'intl_installed' => extension_loaded('intl'),
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/phpinfo")
     * @Theme("admin")
     * @Template("ZikulaSettingsModule:Settings:phpinfo.html.twig")
     *
     * Displays the content of {@see phpinfo()}.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function phpinfoAction(): array
    {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        $phpinfo = str_replace('module_Zend Optimizer', 'module_Zend_Optimizer', preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo));

        return [
            'phpinfo' => $phpinfo
        ];
    }

    /**
     * Prepare an array of module names and displaynames for dropdown usage.
     */
    private function formatModuleArrayForSelect(
        ExtensionRepositoryInterface $extensionRepository,
        array $modules = []
    ): array {
        $return = [];
        foreach ($modules as $module) {
            if (!($module instanceof ExtensionEntity)) {
                $module = $extensionRepository->get($module);
            }
            $return[$module->getDisplayname()] = $module->getName();
        }
        ksort($return);

        return $return;
    }
}
