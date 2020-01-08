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
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class HookController
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
    public function editAction(
        RequestStack $requestStack,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        HookDispatcherInterface $dispatcher,
        ExtensionRepositoryInterface $extensionRepository,
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

        $nonPersistedProviders = $collector->getProviders();
        $nonPersistedSubscribers = $collector->getSubscribers();

        // get areas of module and bundle titles also
        if ($isProvider) {
            $providerAreas = $collector->getProviderAreasByOwner($moduleName);
            $templateParameters['providerAreas'] = $providerAreas;

            $providerAreasToTitles = [];
            foreach ($providerAreas as $providerArea) {
                if (isset($nonPersistedProviders[$providerArea])) {
                    $providerAreasToTitles[$providerArea] = $nonPersistedProviders[$providerArea]->getTitle();
                }
            }
            $templateParameters['providerAreasToTitles'] = $providerAreasToTitles;
        }
        $templateParameters['subscriberAreas'] = [];
        $templateParameters['hooksubscribers'] = [];

        if ($isSubscriber) {
            $subscriberAreas = $collector->getSubscriberAreasByOwner($moduleName);
            $templateParameters['subscriberAreas'] = $subscriberAreas;

            $subscriberAreasToTitles = [];
            $subscriberAreasToCategories = [];
            $subscriberAreasAndCategories = [];
            foreach ($subscriberAreas as $subscriberArea) {
                $category = null;
                if (isset($nonPersistedSubscribers[$subscriberArea])) {
                    $subscriberAreasToTitles[$subscriberArea] = $nonPersistedSubscribers[$subscriberArea]->getTitle();
                    $category = $nonPersistedSubscribers[$subscriberArea]->getCategory();
                }
                $subscriberAreasToCategories[$subscriberArea] = $category;
                if (null !== $category) {
                    $subscriberAreasAndCategories[$category][] = $subscriberArea;
                }
            }
            $templateParameters['subscriberAreasToTitles'] = $subscriberAreasToTitles;
            $templateParameters['subscriberAreasToCategories'] = $subscriberAreasToCategories;
            $templateParameters['subscriberAreasAndCategories'] = $subscriberAreasAndCategories;
        }

        // get available subscribers that can attach to provider
        if ($isProvider && !empty($providerAreas)) {
            /** @var ExtensionEntity[] $hookSubscribers */
            $hookSubscribers = $this->getExtensionsCapableOf($collector, $extensionRepository, HookCollectorInterface::HOOK_SUBSCRIBER);
            $amountOfAvailableSubscriberAreas = 0;
            foreach ($hookSubscribers as $i => $hookSubscriber) {
                $hookSubscribers[$i] = $hookSubscriber->toArray();
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if ($moduleName === $hookSubscriber->getName()) {
                    unset($hookSubscribers[$i]);
                    continue;
                }
                // does the user have admin permissions on the subscriber module?
                if (!$permissionApi->hasPermission($hookSubscriber->getName() . '::', '::', ACCESS_ADMIN)) {
                    unset($hookSubscribers[$i]);
                    continue;
                }

                // get the areas of the subscriber
                $hooksubscriberAreas = $collector->getSubscriberAreasByOwner($hookSubscriber->getName());
                $hookSubscribers[$i]['areas'] = $hooksubscriberAreas;
                $amountOfAvailableSubscriberAreas += count($hooksubscriberAreas);

                $hooksubscriberAreasToTitles = []; // and get the titles
                $hooksubscriberAreasToCategories = []; // and get the categories
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    $category = null;
                    if (isset($nonPersistedSubscribers[$hooksubscriberArea])) {
                        $hooksubscriberAreasToTitles[$hooksubscriberArea] = $nonPersistedSubscribers[$hooksubscriberArea]->getTitle();
                        $category = $nonPersistedSubscribers[$hooksubscriberArea]->getCategory();
                    }
                    $hooksubscriberAreasToCategories[$hooksubscriberArea] = $category;
                }
                $hookSubscribers[$i]['areasToTitles'] = $hooksubscriberAreasToTitles;
                $hookSubscribers[$i]['areasToCategories'] = $hooksubscriberAreasToCategories;
            }
            $templateParameters['hooksubscribers'] = $hookSubscribers;
            $templateParameters['total_available_subscriber_areas'] = $amountOfAvailableSubscriberAreas;
        } else {
            $templateParameters['total_available_subscriber_areas'] = 0;
        }

        // get providers that are already attached to the subscriber
        // and providers that can attach to the subscriber
        if ($isSubscriber && !empty($subscriberAreas)) {
            // get current sorting
            $currentSortingTitles = [];
            $currentSorting = [];
            $amountOfAttachedProviderAreas = 0;
            foreach ($subscriberAreas as $hookSubscriber) {
                $sortsByArea = $dispatcher->getBindingsFor($hookSubscriber);
                foreach ($sortsByArea as $sba) {
                    $areaname = $sba['areaname'];
                    $category = $sba['category'];

                    if (!isset($currentSorting[$category])) {
                        $currentSorting[$category] = [];
                    }

                    if (!isset($currentSorting[$category][$hookSubscriber])) {
                        $currentSorting[$category][$hookSubscriber] = [];
                    }

                    $currentSorting[$category][$hookSubscriber][] = $areaname;
                    $amountOfAttachedProviderAreas++;

                    // get the bundle title
                    if (isset($nonPersistedProviders[$areaname])) {
                        $currentSortingTitles[$areaname] = $nonPersistedProviders[$areaname]->getTitle();
                    }
                }
            }
            $templateParameters['areasSorting'] = $currentSorting;
            $templateParameters['areasSortingTitles'] = $currentSortingTitles;
            $templateParameters['total_attached_provider_areas'] = $amountOfAttachedProviderAreas;

            // get available providers
            /** @var ExtensionEntity[] $hookProviders */
            $hookProviders = $this->getExtensionsCapableOf($collector, $extensionRepository, HookCollectorInterface::HOOK_PROVIDER);
            $amountOfAvailableProviderAreas = 0;
            foreach ($hookProviders as $i => $hookProvider) {
                $hookProviders[$i] = $hookProvider->toArray();
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if (!$isSubscriberSelfCapable && $moduleName === $hookProvider->getName()) {
                    unset($hookProviders[$i]);
                    continue;
                }

                // does the user have admin permissions on the provider module?
                if (!$permissionApi->hasPermission($hookProvider->getName() . '::', '::', ACCESS_ADMIN)) {
                    unset($hookProviders[$i]);
                    continue;
                }

                // get the areas of the provider
                $hookproviderAreas = $collector->getProviderAreasByOwner($hookProvider->getName());
                $hookProviders[$i]['areas'] = $hookproviderAreas;
                $amountOfAvailableProviderAreas += count($hookproviderAreas);

                $hookproviderAreasToTitles = []; // and get the titles
                $hookproviderAreasToCategories = []; // and get the categories
                $hookproviderAreasAndCategories = []; // and build array with category => areas
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $category = null;
                    if (isset($nonPersistedProviders[$hookproviderArea])) {
                        $hookproviderAreasToTitles[$hookproviderArea] = $nonPersistedProviders[$hookproviderArea]->getTitle();
                        $category = $nonPersistedProviders[$hookproviderArea]->getCategory();
                    }
                    $hookproviderAreasToCategories[$hookproviderArea] = $category;
                    $hookproviderAreasAndCategories[$category][] = $hookproviderArea;
                }
                $hookProviders[$i]['areasToTitles'] = $hookproviderAreasToTitles;
                $hookProviders[$i]['areasToCategories'] = $hookproviderAreasToCategories;
                $hookProviders[$i]['areasAndCategories'] = $hookproviderAreasAndCategories;
            }
            $templateParameters['hookproviders'] = $hookProviders;
            $templateParameters['total_available_provider_areas'] = $amountOfAvailableProviderAreas;
        } else {
            $templateParameters['hookproviders'] = [];
        }
        $templateParameters['hookDispatcher'] = $dispatcher;
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
    public function toggleSubscribeAreaStatusAction(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        HookDispatcherInterface $dispatcher
    ): JsonResponse {
        $this->setTranslator($translator);
        if (!$this->checkAjaxToken($request)) {
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
            throw new InvalidArgumentException($this->trans('Module "%s" is not a valid subscriber.', ['%s' => $subscriber->getOwner()]));
        }
        if (!$kernel->isBundle($subscriber->getOwner())) {
            throw new RuntimeException($this->trans('Subscriber module "%s" is not available.', ['%s' => $subscriber->getOwner()]));
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
            throw new InvalidArgumentException($this->trans('Module "%s" is not a valid provider.', ['%s' => $provider->getOwner()]));
        }
        if (!$kernel->isBundle($provider->getOwner())) {
            throw new RuntimeException($this->trans('Provider module "%s" is not available.', ['%s' => $provider->getOwner()]));
        }
        if (!$permissionApi->hasPermission($provider->getOwner() . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check if binding between areas exists
        $binding = $dispatcher->getBindingBetweenAreas($subscriberArea, $providerArea);
        if (!$binding) {
            $dispatcher->bindSubscriber($subscriberArea, $providerArea);
        } else {
            $dispatcher->unbindSubscriber($subscriberArea, $providerArea);
        }

        // ajax response
        $response = [
            'result' => true,
            'action' => $binding ? 'unbind' : 'bind',
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
    public function changeProviderAreaOrderAction(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        HookDispatcherInterface $hookDispatcher
    ): JsonResponse {
        $this->setTranslator($translator);
        if (!$this->checkAjaxToken($request)) {
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
            throw new InvalidArgumentException($this->trans('Module "%s" is not a valid subscriber.', ['%s' => $subscriber->getOwner()]));
        }
        if (!$kernel->isBundle($subscriber->getOwner())) {
            throw new RuntimeException($this->trans('Subscriber module "%s" is not available.', ['%s' => $subscriber->getOwner()]));
        }
        if (!$permissionApi->hasPermission($subscriber->getOwner() . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providers' areas from POST
        $providerarea = $request->request->get('providerarea', '');
        if (!is_array($providerarea) || count($providerarea) < 1) {
            throw new InvalidArgumentException($this->trans('Providers\' areas order is not an array.'));
        }

        // set sorting
        $hookDispatcher->setBindOrder($subscriberarea, $providerarea);

        return $this->json(['result' => true]);
    }

    /**
     * Check the CSRF token.
     */
    private function checkAjaxToken(Request $request): bool
    {
        if (!$request->isXmlHttpRequest()) {
            return false;
        }

        if (!$request->hasSession()) {
            return false;
        }

        $sessionName = $this->getParameter('zikula.session.name');
        $sessionId = $request->cookies->get($sessionName);

        if ($sessionId !== $request->getSession()->getId()) {
            return false;
        }

        if (!$this->isCsrfTokenValid('hook-ui', $request->request->get('token'))) {
            return false;
        }

        return true;
    }

    private function getExtensionsCapableOf(
        HookCollectorInterface $collector,
        ExtensionRepositoryInterface $extensionRepository,
        string $type
    ): array {
        $owners = $collector->getOwnersCapableOf($type);
        $extensions = [];
        foreach ($owners as $owner) {
            $extensions[] = $extensionRepository->findOneBy(['name' => $owner]);
        }

        return $extensions;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
