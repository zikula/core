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

namespace Zikula\SettingsModule\Listener;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Translation\Bundle\Service\StorageManager;
use Translation\Bundle\Service\StorageService;
use Translation\SymfonyStorage\FileStorage;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class TranslationUiListener implements EventSubscriberInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var StorageManager
     */
    private $storageManager;

    public function __construct(
        PermissionApiInterface $permissionApi,
        LocaleApiInterface $localeApi,
        StorageManager $storageManager
    ) {
        $this->permissionApi = $permissionApi;
        $this->localeApi = $localeApi;
        $this->storageManager = $storageManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
            // remove after php-translation/symfony-bundle does not rely on '</body>' anymore
            // the problem here is that our theme engine adds the closing body tag quite late
            // thus, we simply add it and remove it again
            KernelEvents::RESPONSE => [
                ['addDummyClosingBody', 5],
                ['removeDummyClosingBody', -1]
            ]
        ];
    }

    /**
     * @throws AccessDeniedException Thrown if the user doesn't have admin access
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->get('_route', '');
        if ('translation_edit_in_place_update' !== $routeName && 'translation_' !== mb_substr($routeName, 0, 12)) {
            return;
        }

        // allow translations UI functionality only with required permissions
        if (!$this->permissionApi->hasPermission('ZikulaSettingsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // update locale configuration parameters if needed
        $this->localeApi->getSupportedLocales(true);

        // inject correct value for output format
        // remove when https://github.com/php-translation/symfony-storage/issues/48 is solved
        try {
            $configName = $request->attributes->get('configName', '');
            /** @var StorageService $storage */
            $storage = $this->storageManager->getStorage($configName);
            $reflection = new ReflectionClass(StorageService::class);
            $localStoragesProperty = $reflection->getProperty('localStorages');
            $localStoragesProperty->setAccessible(true);
            $localStorages = $localStoragesProperty->getValue($storage);
            foreach ($localStorages as $localStorage) {
                if (!($localStorage instanceof FileStorage)) {
                    continue;
                }
                $reflection = new ReflectionClass(FileStorage::class);
                $optionsProperty = $reflection->getProperty('options');
                $optionsProperty->setAccessible(true);
                $options = $optionsProperty->getValue($localStorage);
                $options['default_output_format'] = 'yaml';
                $optionsProperty->setValue($localStorage, $options);
                break;
            }
        } catch (ReflectionException $exception) {
            // nothing
        }
    }

    public function addDummyClosingBody(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->isRequestRelevantForEditInPlace($event->getRequest())) {
            return;
        }

        $content = $event->getResponse()->getContent();
        $content .= '</body>'; // required by EditInPlaceResponseListener
        $event->getResponse()->setContent($content);
    }

    public function removeDummyClosingBody(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->isRequestRelevantForEditInPlace($event->getRequest())) {
            return;
        }

        $content = $event->getResponse()->getContent();
        $content = str_replace('</body>', '', $content);
        $event->getResponse()->setContent($content);
    }

    private function isRequestRelevantForEditInPlace(Request $request): bool
    {
        if ($request->isXmlHttpRequest()) {
            return false;
        }
        $format = $request->getRequestFormat();
        if ('html' !== $format) {
            return false;
        }
        if (!$request->hasSession() || !($session = $request->getSession())) {
            return false;
        }
        if (!$session->has(EditInPlaceActivator::KEY)) {
            return false;
        }

        return true;
    }
}
