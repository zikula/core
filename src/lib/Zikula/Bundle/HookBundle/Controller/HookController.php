<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class HookController
 * @Route("/hooks")
 */
class HookController extends Controller
{
    use TranslatorTrait;

    /**
     * @Route("/{moduleName}", methods = {"GET"}, options={"zkNoBundlePrefix" = 1})
     * @Theme("admin")
     * @Template("ZikulaHookBundle:Hook:edit.html.twig")
     *
     * Display hooks user interface
     *
     * @param TranslatorInterface $translator
     * @param PermissionApiInterface $permissionApi
     * @param HookCollectorInterface $collector
     * @param HookDispatcherInterface $dispatcher
     * @param ExtensionRepositoryInterface $extensionRepository
     * @param string $moduleName
     *
     * @return array
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function editAction(
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        HookDispatcherInterface $dispatcher,
        ExtensionRepositoryInterface $extensionRepository,
        $moduleName
    ) {
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
            /** @var ExtensionEntity[] $hooksubscribers */
            $hooksubscribers = $this->getExtensionsCapableOf($collector, $extensionRepository, HookCollectorInterface::HOOK_SUBSCRIBER);
            $amountOfHookSubscribers = count($hooksubscribers);
            $amountOfAvailableSubscriberAreas = 0;
            for ($i = 0; $i < $amountOfHookSubscribers; $i++) {
                $hooksubscribers[$i] = $hooksubscribers[$i]->toArray();
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if ($hooksubscribers[$i]['name'] == $moduleName) {
                    unset($hooksubscribers[$i]);
                    continue;
                }
                // does the user have admin permissions on the subscriber module?
                if (!$permissionApi->hasPermission($hooksubscribers[$i]['name'] . "::", '::', ACCESS_ADMIN)) {
                    unset($hooksubscribers[$i]);
                    continue;
                }

                // get the areas of the subscriber
                $hooksubscriberAreas = $collector->getSubscriberAreasByOwner($hooksubscribers[$i]['name']);
                $hooksubscribers[$i]['areas'] = $hooksubscriberAreas;
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
                $hooksubscribers[$i]['areasToTitles'] = $hooksubscriberAreasToTitles;
                $hooksubscribers[$i]['areasToCategories'] = $hooksubscriberAreasToCategories;
            }
            $templateParameters['hooksubscribers'] = $hooksubscribers;
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
            $amountOfSubscriberAreas = count($subscriberAreas);
            for ($i = 0; $i < $amountOfSubscriberAreas; $i++) {
                $sortsByArea = $dispatcher->getBindingsFor($subscriberAreas[$i]);
                foreach ($sortsByArea as $sba) {
                    $areaname = $sba['areaname'];
                    $category = $sba['category'];

                    if (!isset($currentSorting[$category])) {
                        $currentSorting[$category] = [];
                    }

                    if (!isset($currentSorting[$category][$subscriberAreas[$i]])) {
                        $currentSorting[$category][$subscriberAreas[$i]] = [];
                    }

                    array_push($currentSorting[$category][$subscriberAreas[$i]], $areaname);
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
            /** @var ExtensionEntity[] $hookproviders */
            $hookproviders = $this->getExtensionsCapableOf($collector, $extensionRepository, HookCollectorInterface::HOOK_PROVIDER);
            $amountOfHookProviders = count($hookproviders);
            $amountOfAvailableProviderAreas = 0;
            for ($i = 0; $i < $amountOfHookProviders; $i++) {
                $hookproviders[$i] = $hookproviders[$i]->toArray();
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if ($hookproviders[$i]['name'] == $moduleName && !$isSubscriberSelfCapable) {
                    unset($hookproviders[$i]);
                    continue;
                }

                // does the user have admin permissions on the provider module?
                if (!$permissionApi->hasPermission($hookproviders[$i]['name'] . "::", '::', ACCESS_ADMIN)) {
                    unset($hookproviders[$i]);
                    continue;
                }

                // get the areas of the provider
                $hookproviderAreas = $collector->getProviderAreasByOwner($hookproviders[$i]['name']);
                $hookproviders[$i]['areas'] = $hookproviderAreas;
                $amountOfAvailableProviderAreas += count($hookproviderAreas);

                $hookproviderAreasToTitles = []; // and get the titles
                $hookproviderAreasToCategories = []; // and get the categories
                $hookproviderAreasAndCategories = []; // and build array with category => areas
                foreach ($hookproviderAreas as $hookproviderArea) {
                    if (isset($nonPersistedProviders[$hookproviderArea])) {
                        $hookproviderAreasToTitles[$hookproviderArea] = $nonPersistedProviders[$hookproviderArea]->getTitle();
                        $category = $nonPersistedProviders[$hookproviderArea]->getCategory();
                    }
                    $hookproviderAreasToCategories[$hookproviderArea] = $category;
                    $hookproviderAreasAndCategories[$category][] = $hookproviderArea;
                }
                $hookproviders[$i]['areasToTitles'] = $hookproviderAreasToTitles;
                $hookproviders[$i]['areasToCategories'] = $hookproviderAreasToCategories;
                $hookproviders[$i]['areasAndCategories'] = $hookproviderAreasAndCategories;
            }
            $templateParameters['hookproviders'] = $hookproviders;
            $templateParameters['total_available_provider_areas'] = $amountOfAvailableProviderAreas;
        } else {
            $templateParameters['hookproviders'] = [];
        }
        $templateParameters['hookDispatcher'] = $dispatcher;
        $request = $this->get('request_stack')->getCurrentRequest();
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
     * @param Request $request
     *  subscriberarea string area to be attached/detached
     *  providerarea   string area to attach/detach
     * @param PermissionApiInterface $permissionApi
     * @param HookCollectorInterface $collector
     * @param HookDispatcherInterface $dispatcher
     *
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException Thrown if either the subscriber, provider or subscriberArea parameters are empty
     * @throws \RuntimeException Thrown if either the subscriber or provider module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to either the subscriber or provider modules
     */
    public function toggleSubscribeAreaStatusAction(
        Request $request,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector,
        HookDispatcherInterface $dispatcher
    ) {
        $this->setTranslator($translator);
        if (!$this->checkAjaxToken($request)) {
            throw new AccessDeniedException();
        }

        // get subscriberarea from POST
        $subscriberArea = $request->request->get('subscriberarea', '');
        if (empty($subscriberArea)) {
            throw new \InvalidArgumentException($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $collector->getSubscriber($subscriberArea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid subscriber.', ['%s' => $subscriber->getOwner()]));
        }
        if (!$this->get('kernel')->isBundle($subscriber->getOwner())) {
            throw new \RuntimeException($this->__f('Subscriber module "%s" is not available.', ['%s' => $subscriber->getOwner()]));
        }
        if (!$permissionApi->hasPermission($subscriber->getOwner() . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providerarea from POST
        $providerArea = $request->request->get('providerarea', '');
        if (empty($providerArea)) {
            throw new \InvalidArgumentException($this->__('No provider area passed.'));
        }

        // get provider module based on area and do some checks
        $provider = $collector->getProvider($providerArea);
        if (empty($provider)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid provider.', ['%s' => $provider->getOwner()]));
        }
        if (!$this->get('kernel')->isBundle($provider->getOwner())) {
            throw new \RuntimeException($this->__f('Provider module "%s" is not available.', ['%s' => $provider->getOwner()]));
        }
        if (!$this->get('zikula_permissions_module.api.permission')->hasPermission($provider->getOwner() . '::', '::', ACCESS_ADMIN)) {
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
     * @param Request $request
     *  subscriber    string     name of the subscriber
     *  providerorder array      array of sorted provider ids
     * @param PermissionApiInterface $permissionApi
     * @param HookCollectorInterface $collector
     *
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException Thrown if the subscriber or subscriberarea parameters aren't valid
     * @throws \RuntimeException Thrown if the subscriber module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the subscriber module
     */
    public function changeProviderAreaOrderAction(
        Request $request,
        PermissionApiInterface $permissionApi,
        HookCollectorInterface $collector
    ) {
        $this->setTranslator($this->get('translator.default'));
        if (!$this->checkAjaxToken($request)) {
            throw new AccessDeniedException();
        }

        // get subscriberarea from POST
        $subscriberarea = $request->request->get('subscriberarea', '');
        if (empty($subscriberarea)) {
            throw new \InvalidArgumentException($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $collector->getSubscriber($subscriberarea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid subscriber.', ['%s' => $subscriber->getOwner()]));
        }
        if (!$this->get('kernel')->isBundle($subscriber->getOwner())) {
            throw new \RuntimeException($this->__f('Subscriber module "%s" is not available.', ['%s' => $subscriber->getOwner()]));
        }
        if (!$permissionApi->hasPermission($subscriber->getOwner() . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providers' areas from POST
        $providerarea = $request->request->get('providerarea', '');
        if (!(is_array($providerarea) && count($providerarea) > 0)) {
            throw new \InvalidArgumentException($this->__('Providers\' areas order is not an array.'));
        }

        // set sorting
        $this->get('hook_dispatcher')->setBindOrder($subscriberarea, $providerarea);

        return $this->json(['result' => true]);
    }

    /**
     * Check the CSRF token.
     *
     * @param Request $request
     *
     * @return boolean
     */
    private function checkAjaxToken(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return false;
        }

        $sessionName = $this->container->getParameter('zikula.session.name');
        $sessionId = $request->cookies->get($sessionName, null);

        if ($sessionId != $request->getSession()->getId()) {
            return false;
        }

        if (!$this->isCsrfTokenValid('hook-ui', $request->request->get('token'))) {
            return false;
        }
    }

    private function getExtensionsCapableOf(HookCollectorInterface $collector, ExtensionRepositoryInterface $extensionRepository, $type)
    {
        $owners = $collector->getOwnersCapableOf($type);
        $extensions = [];
        foreach ($owners as $owner) {
            $extensions[] = $extensionRepository->findOneBy(['name' => $owner]);
        }

        return $extensions;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
