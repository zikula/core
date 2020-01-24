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
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
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
     * @Template("@ZikulaSettingsModule/Settings/main.html.twig")
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
        ProfileModuleCollector $profileModuleCollector,
        MessageModuleCollector $messageModuleCollector
    ) {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // ensures that locales with regions are up to date
        $installedLanguageNames = $localeApi->getSupportedLocaleNames(null, $request->getLocale(), true);

        $profileModules = $profileModuleCollector->getKeys();
        $messageModules = $messageModuleCollector->getKeys();

        $variables = $variableApi->getAll(VariableApi::CONFIG);
        $variables['UseCompression'] = (bool)$variables['UseCompression'];
        $form = $this->createForm(MainSettingsType::class,
            $variables, [
                'languages' => $installedLanguageNames,
                'profileModules' => $profileModules,
                'messageModules' => $messageModules
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                foreach ($data as $name => $value) {
                    $variableApi->set(VariableApi::CONFIG, $name, $value);
                }
                $this->addFlash('status', 'Done! Configuration updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
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
     * @Template("@ZikulaSettingsModule/Settings/locale.html.twig")
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
        VariableApiInterface $variableApi
    ) {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // ensures that locales with regions are up to date
        $installedLanguageNames = $localeApi->getSupportedLocaleNames(null, $request->getLocale(), true);

        $form = $this->createForm(LocaleSettingsType::class,
            [
                'multilingual' => (bool)$variableApi->getSystemVar('multilingual'),
                'languageurl' => $variableApi->getSystemVar('languageurl'),
                'language_detect' => (bool)$variableApi->getSystemVar('language_detect'),
                'locale' => $variableApi->getSystemVar('locale'),
                'timezone' => $variableApi->getSystemVar('timezone'),
            ], [
                'languages' => $installedLanguageNames,
                'locale' => $request->getLocale()
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
                // resets config/dynamic/generated.yaml and config/services_custom.yaml
                $localeApi->getSupportedLocales(true);

                if ($request->hasSession() && ($session = $request->getSession())) {
                    $session->set('_locale', $data['locale']);
                }
                $this->addFlash('status', 'Done! Localization configuration updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
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
     * @Template("@ZikulaSettingsModule/Settings/phpinfo.html.twig")
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
        $phpinfo = str_replace(
            'module_Zend Optimizer',
            'module_Zend_Optimizer',
            preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo)
        );

        return [
            'phpinfo' => $phpinfo
        ];
    }

    /**
     * @Route("/toggleeditinplace")
     * @Theme("admin")
     *
     * Toggles the "Edit in place" translation functionality.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function toggleEditInPlaceAction(
        Request $request,
        EditInPlaceActivator $activator
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ($request->hasSession() && ($session = $request->getSession())) {
            if ($session->has(EditInPlaceActivator::KEY)) {
                $activator->deactivate();
                $this->addFlash('status', 'Done! Disabled edit in place translations.');
            } else {
                $activator->activate();
                $this->addFlash('status', 'Done! Enabled edit in place translations.');
            }
        } else {
            $this->addFlash('error', 'Could not change the setting due to missing session access.');
        }

        return $this->redirectToRoute('zikulasettingsmodule_settings_locale');
    }
}
