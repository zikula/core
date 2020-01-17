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

namespace Zikula\SettingsModule\Container;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
    }

    public function getLinks(string $type = LinkContainerInterface::TYPE_ADMIN): array
    {
        if (LinkContainerInterface::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        return [];
    }

    /**
     * Get the admin links for this extension.
     */
    private function getAdmin(): array
    {
        $links = [];

        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return $links;
        }

        $i10nLinks = [];
        $i10nLinks[] = [
            'url' => $this->router->generate('zikulasettingsmodule_settings_locale'),
            'text' => $this->translator->trans('Localisation settings'),
            'icon' => 'spell-check'
        ];
        if (true === (bool)$this->variableApi->getSystemVar('multilingual')) {
            if ('dev' === $this->kernel->getEnvironment()) {
                $request = $this->requestStack->getCurrentRequest();
                if ($request->hasSession() && ($session = $request->getSession())) {
                    if ($session->has(EditInPlaceActivator::KEY)) {
                        $i10nLinks[] = [
                            'url' => $this->router->generate('zikulasettingsmodule_settings_toggleeditinplace'),
                            'text' => $this->translator->trans('Disable edit in place'),
                            'icon' => 'ban'
                        ];
                    } else {
                        $i10nLinks[] = [
                            'url' => $this->router->generate('zikulasettingsmodule_settings_toggleeditinplace'),
                            'text' => $this->translator->trans('Enable edit in place'),
                            'title' => $this->translator->trans('Edit translations directly in the context of a page'),
                            'icon' => 'user-edit'
                        ];
                    }
                }
                $i10nLinks[] = [
                    'url' => $this->router->generate('translation_index'),
                    'text' => $this->translator->trans('Translation UI'),
                    'title' => $this->translator->trans('Web interface to add, edit and remove translations'),
                    'icon' => 'language'
                ];
            }
        }

        $links[] = [
            'url' => $this->router->generate('zikulasettingsmodule_settings_main'),
            'text' => $this->translator->trans('Main settings'),
            'icon' => 'wrench'
        ];
        $links[] = [
            'text' => $this->translator->trans('Localisation'),
            'icon' => 'globe',
            'links' => $i10nLinks
        ];
        $links[] = [
            'url' => $this->router->generate('zikulasettingsmodule_settings_phpinfo'),
            'text' => $this->translator->trans('PHP configuration'),
            'icon' => 'info-circle'
        ];

        return $links;
    }

    public function getBundleName(): string
    {
        return 'ZikulaSettingsModule';
    }
}
