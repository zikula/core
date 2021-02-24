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

namespace Zikula\Bundle\HookBundle\Controller;

use InvalidArgumentException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Event\HookPostChangeEvent;
use Zikula\Bundle\HookBundle\Helper\HookUiHelper;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @deprecated remove at Core 4.0.0
 * @Route("/hooks")
 */
class HookController extends AbstractController
{
    use TranslatorTrait;

    /**
     * @Route("/{moduleName}", methods = {"GET"}, options={"zkNoBundlePrefix" = 1})
     * @Theme("admin")
     * @Template("@ZikulaHook/Hook/edit.html.twig")
     *
     * Display hooks user interface
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function edit(
        RequestStack $requestStack,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        HookDispatcherInterface $hookDispatcher,
        HookUiHelper $hookUiHelper,
        string $moduleName
    ): array {
        $templateParameters = [];
        // get module's name and assign it to template
        $templateParameters['currentmodule'] = $moduleName;

        // check if user has admin permission on this module
        if (!$permissionApi->hasPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // find out the capabilities of the module
        $isProvider = $collector->isCapable($moduleName, HookCollectorInterface::HOOK_PROVIDER);
        $templateParameters['isProvider'] = $isProvider;

        $isSubscriber = $collector->isCapable($moduleName, HookCollectorInterface::HOOK_SUBSCRIBER);
        $templateParameters['isSubscriber'] = $isSubscriber;

        $isSubscriberSelfCapable = $collector->isCapable($moduleName, HookCollectorInterface::HOOK_SUBSCRIBE_OWN);
        $templateParameters['isSubscriberSelfCapable'] = $isSubscriberSelfCapable;
        $templateParameters['providerAreas'] = [];

        $providers = $collector->getProviders();
        $subscribers = $collector->getSubscribers();

        // get areas of module and bundle titles also
        if ($isProvider) {
            $providerAreas = $collector->getProviderAreasByOwner($moduleName);
            $templateParameters['providerAreas'] = $providerAreas;

            $providerAreasToTitles = [];
            foreach ($providerAreas as $providerArea) {
                if (isset($providers[$providerArea])) {
                    $providerAreasToTitles[$providerArea] = $providers[$providerArea]->getTitle();
                }
            }
            $templateParameters['providerAreasToTitles'] = $providerAreasToTitles;
        }
        $templateParameters['subscriberAreas'] = [];
        $templateParameters['hookSubscribers'] = [];

        if ($isSubscriber) {
            $subscriberAreas = $collector->getSubscriberAreasByOwner($moduleName);
            $templateParameters['subscriberAreas'] = $subscriberAreas;

            $areasInfo = $hookUiHelper->prepareSubscriberAreasForSubscriber($subscriberAreas, $subscribers);
            $templateParameters['subscriberAreasToTitles'] = $areasInfo['titles'];
            $templateParameters['subscriberAreasToCategories'] = $areasInfo['categories'];
            $templateParameters['subscriberAreasAndCategories'] = $areasInfo['categoryGroups'];
        }

        // get available subscribers that can attach to provider
        if ($isProvider && !empty($providerAreas)) {
            [$hookSubscribers, $amountOfAvailableSubscriberAreas] = $hookUiHelper->prepareAvailableSubscriberAreasForProvider($moduleName, $subscribers);
            $templateParameters['hookSubscribers'] = $hookSubscribers;
            $templateParameters['amountOfAvailableSubscriberAreas'] = $amountOfAvailableSubscriberAreas;
        } else {
            $templateParameters['amountOfAvailableSubscriberAreas'] = 0;
        }

        // get providers that are already attached to the subscriber
        // and providers that can attach to the subscriber
        if ($isSubscriber && !empty($subscriberAreas)) {
            // get current sorting
            [$currentSorting, $currentSortingTitles, $amountOfAttachedProviderAreas] = $hookUiHelper->prepareAttachedProvidersForSubscriber($subscriberAreas, $providers);
            $templateParameters['areasSorting'] = $currentSorting;
            $templateParameters['areasSortingTitles'] = $currentSortingTitles;
            $templateParameters['amountOfAttachedProviderAreas'] = $amountOfAttachedProviderAreas;

            [$hookProviders, $amountOfAvailableProviderAreas] = $hookUiHelper->prepareAvailableProviderAreasForSubscriber($moduleName, $providers, $isSubscriberSelfCapable);
            $templateParameters['hookProviders'] = $hookProviders;
            $templateParameters['amountOfAvailableProviderAreas'] = $amountOfAvailableProviderAreas;
        } else {
            $templateParameters['hookProviders'] = [];
        }
        $templateParameters['hookDispatcher'] = $hookDispatcher;
        $request = $requestStack->getCurrentRequest();
        $request->attributes->set('_zkModule', $moduleName);
        $request->attributes->set('_zkType', 'admin');
        $request->attributes->set('_zkFunc', 'Hooks');

        return $templateParameters;
    }

    /**
     * @Route("/togglestatus", methods = {"POST"}, options={"expose"=true})
     *
     * Attach/detach a subscriber area to a provider area
     *
     * @throws InvalidArgumentException Thrown if either the subscriber, provider or subscriberArea parameters are empty
     * @throws RuntimeException Thrown if either the subscriber or provider module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to either the subscriber or provider modules
     */
    public function toggleSubscribeAreaStatus(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        EventDispatcherInterface $eventDispatcher,
        HookDispatcherInterface $hookDispatcher
    ): JsonResponse {
        $this->setTranslator($translator);
        if (!$this->isCsrfTokenValid('hook-ui', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        // get subscriberarea from POST
        $subscriberArea = $request->request->get('subscriberarea', '');
        if (empty($subscriberArea)) {
            throw new InvalidArgumentException($this->trans('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $collector->getSubscriber($subscriberArea);
        if (null === $subscriber) {
            throw new InvalidArgumentException($this->trans('Module "%name%" is not a valid subscriber.', ['%name%' => $subscriber->getOwner()]));
        }
        if (!$kernel->isBundle($subscriber->getOwner())) {
            throw new RuntimeException($this->trans('Subscriber module "%name%" is not available.', ['%name%' => $subscriber->getOwner()]));
        }
        if (!$permissionApi->hasPermission($subscriber->getOwner() . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providerarea from POST
        $providerArea = $request->request->get('providerarea', '');
        if (empty($providerArea)) {
            throw new InvalidArgumentException($this->trans('No provider area passed.'));
        }

        // get provider module based on area and do some checks
        $provider = $collector->getProvider($providerArea);
        if (null === $provider) {
            throw new InvalidArgumentException($this->trans('Module "%name%" is not a valid provider.', ['%name%' => $provider->getOwner()]));
        }
        if (!$kernel->isBundle($provider->getOwner())) {
            throw new RuntimeException($this->trans('Provider module "%name%" is not available.', ['%name%' => $provider->getOwner()]));
        }
        if (!$permissionApi->hasPermission($provider->getOwner() . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check if binding between areas exists
        $binding = $hookDispatcher->getBindingBetweenAreas($subscriberArea, $providerArea);
        if (!$binding) {
            $hookDispatcher->bindSubscriber($subscriberArea, $providerArea);
        } else {
            $hookDispatcher->unbindSubscriber($subscriberArea, $providerArea);
        }

        $action = $binding ? 'unbind' : 'bind';
        $eventDispatcher->dispatch(new HookPostChangeEvent($subscriberArea, $providerArea, $action));

        // ajax response
        $response = [
            'result' => true,
            'action' => $action,
            'subscriberarea' => $subscriberArea,
            'subscriberarea_id' => md5($subscriberArea),
            'providerarea' => $providerArea,
            'providerarea_id' => md5($providerArea),
            'isSubscriberSelfCapable' => $collector->isCapable($subscriber->getOwner(), HookCollectorInterface::HOOK_SUBSCRIBE_OWN)
        ];

        return $this->json($response);
    }

    /**
     * @Route("/changeorder", methods = {"POST"}, options={"expose"=true})
     *
     * Changes the order of the providers' areas that are attached to a subscriber.
     *
     * @throws InvalidArgumentException Thrown if the subscriber or subscriberarea parameters aren't valid
     * @throws RuntimeException Thrown if the subscriber module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the subscriber module
     */
    public function changeProviderAreaOrder(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        HookDispatcherInterface $hookDispatcher
    ): JsonResponse {
        $this->setTranslator($translator);
        if (!$this->isCsrfTokenValid('hook-ui', $request->request->get('token'))) {
            throw new AccessDeniedException();
        }

        // get subscriberarea from POST
        $subscriberarea = $request->request->get('subscriberarea', '');
        if (empty($subscriberarea)) {
            throw new InvalidArgumentException($this->trans('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $collector->getSubscriber($subscriberarea);
        if (null === $subscriber) {
            throw new InvalidArgumentException($this->trans('Module "%name%" is not a valid subscriber.', ['%name%' => $subscriber->getOwner()]));
        }
        if (!$kernel->isBundle($subscriber->getOwner())) {
            throw new RuntimeException($this->trans('Subscriber module "%name%" is not available.', ['%name%' => $subscriber->getOwner()]));
        }
        if (!$permissionApi->hasPermission($subscriber->getOwner() . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providers' areas from POST
        $providerarea = $request->request->get('providerarea', '');
        if (!is_array($providerarea) || 1 > count($providerarea)) {
            throw new InvalidArgumentException($this->trans('Providers\' areas order is not an array.'));
        }

        // set sorting
        $hookDispatcher->setBindOrder($subscriberarea, $providerarea);

        return $this->json(['result' => true]);
    }
}
