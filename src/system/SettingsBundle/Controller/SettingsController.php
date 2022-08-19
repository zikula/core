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

namespace Zikula\SettingsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsBundle\Api\VariableApi;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\SettingsBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\SettingsBundle\Form\Type\LocaleSettingsType;
use Zikula\SettingsBundle\Form\Type\MainSettingsType;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\UsersBundle\Collector\MessageBundleCollector;
use Zikula\UsersBundle\Collector\ProfileBundleCollector;

/**
 * @PermissionCheck("admin")
 */
#[Route('/settings')]
class SettingsController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaSettings/Settings/main.html.twig")
     *
     * Settings for entire site.
     */
    #[Route('', name: 'zikulasettingsbundle_settings_mainsettings')]
    public function mainSettings(
        Request $request,
        LocaleApiInterface $localeApi,
        VariableApiInterface $variableApi,
        ProfileBundleCollector $profileBundleCollector,
        MessageBundleCollector $messageBundleCollector
    ) {
        // ensures that locales with regions are up to date
        $installedLanguageNames = $localeApi->getSupportedLocaleNames(null, $request->getLocale(), true);

        $profileBundles = $profileBundleCollector->getKeys();
        $messageBundles = $messageBundleCollector->getKeys();

        $variables = $variableApi->getAll(VariableApi::CONFIG);
        $variables['UseCompression'] = (bool) $variables['UseCompression'];
        $form = $this->createForm(
            MainSettingsType::class,
            $variables,
            [
                'languages' => $installedLanguageNames,
                'profileBundles' => $profileBundles,
                'messageBundles' => $messageBundles
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

            return $this->redirectToRoute('zikulasettingsbundle_settings_mainsettings');
        }

        return [
            'languages' => $installedLanguageNames,
            'zlibEnabled' => extension_loaded('zlib'),
            'form' => $form->createView()
        ];
    }

    /**
     * @Theme("admin")
     * @Template("@ZikulaSettings/Settings/locale.html.twig")
     *
     * Locale settings for entire site.
     */
    #[Route('/locale', name: 'zikulasettingsbundle_settings_localesettings')]
    public function localeSettings(
        Request $request,
        LocaleApiInterface $localeApi,
        VariableApiInterface $variableApi
    ) {
        // ensures that locales with regions are up to date
        $installedLanguageNames = $localeApi->getSupportedLocaleNames(null, $request->getLocale(), true);

        $form = $this->createForm(
            LocaleSettingsType::class,
            [
                'multilingual' => (bool) $variableApi->getSystemVar('multilingual'),
                'languageurl' => $variableApi->getSystemVar('languageurl'),
                'language_detect' => (bool) $variableApi->getSystemVar('language_detect'),
                'locale' => $variableApi->getSystemVar('locale'),
                'timezone' => $variableApi->getSystemVar('timezone'),
            ],
            [
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
                $localeApi->getSupportedLocales(true);

                if ($request->hasSession() && ($session = $request->getSession())) {
                    $session->set('_locale', $data['locale']);
                }
                $this->addFlash('status', 'Done! Localization configuration updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulasettingsbundle_settings_localesettings');
        }

        return [
            'intl_installed' => extension_loaded('intl'),
            'form' => $form->createView()
        ];
    }

    /**
     * @Theme("admin")
     * @Template("@ZikulaSettings/Settings/phpinfo.html.twig")
     *
     * Displays the content of {@see phpinfo()}.
     */
    #[Route('/phpinfo', name: 'zikulasettingsbundle_settings_phpinfo', methods: ['GET'])]
    public function phpinfo(): array
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        $phpinfo = str_replace(
            'bundle_Zend Optimizer',
            'bundle_Zend_Optimizer',
            preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo)
        );

        return [
            'phpinfo' => $phpinfo
        ];
    }

    /**
     * @Theme("admin")
     *
     * Toggles the "Edit in place" translation functionality.
     */
    #[Route('/toggleeditinplace', name: 'zikulasettingsbundle_settings_toggleeditinplace')]
    public function toggleEditInPlace(Request $request, EditInPlaceActivator $activator): RedirectResponse
    {
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

        return $this->redirectToRoute('zikulasettingsbundle_settings_localesettings');
    }
}
