<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Util as ExtensionsUtil;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class HookController
 * @Route("/hooks")
 */
class HookController extends Controller
{
    /**
     * @Route("/{moduleName}", options={"zkNoBundlePrefix" = 1})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * Display hooks user interface
     *
     * @param string $moduleName
     * @return array
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function editAction($moduleName)
    {
        $templateParameters = [];
        // get module's name and assign it to template
        $templateParameters['currentmodule'] = $moduleName;

        // check if user has admin permission on this module
        if (!$this->get('zikula_permissions_module.api.permission')->hasPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // create an instance of the module's version
        // we will use it to get the bundles
        // @todo @deprecated in Core-2.0 use `$bundle->getMetaData()` and assume instance of MetaData
        $moduleVersionObj = ExtensionsUtil::getVersionMeta($moduleName);
        if ($moduleVersionObj instanceof MetaData) {
            // Core-1.5 Spec module
            $moduleVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($moduleVersionObj);
        }

        // find out the capabilities of the module
        $isProvider = $this->isCapable($moduleName, CapabilityApiInterface::HOOK_PROVIDER);
        $templateParameters['isProvider'] = $isProvider;

        $isSubscriber = $this->isCapable($moduleName, CapabilityApiInterface::HOOK_SUBSCRIBER);
        $templateParameters['isSubscriber'] = $isSubscriber;

        $isSubscriberSelfCapable = $this->isCapable($moduleName, CapabilityApiInterface::HOOK_SUBSCRIBE_OWN);
        $templateParameters['isSubscriberSelfCapable'] = $isSubscriberSelfCapable;
        $templateParameters['providerAreas'] = [];

        $nonPersistedProviders = $this->get('zikula_hook_bundle.collector.hook_collector')->getProviders();
        $nonPersistedSubscribers = $this->get('zikula_hook_bundle.collector.hook_collector')->getSubscribers();

        // get areas of module and bundle titles also
        if ($isProvider) {
            $providerAreas = $this->get('hook_dispatcher')->getProviderAreasByOwner($moduleName);
            $templateParameters['providerAreas'] = $providerAreas;

            $providerAreasToTitles = [];
            foreach ($providerAreas as $providerArea) {
                if (isset($nonPersistedProviders[$providerArea])) {
                    $providerAreasToTitles[$providerArea] = $nonPersistedProviders[$providerArea]->getTitle();
                } else {
                    // @deprecated
                    $providerAreasToTitles[$providerArea] = $this->get('translator.default')->__(/** @Ignore */
                        $moduleVersionObj->getHookProviderBundle($providerArea)->getTitle());
                }
            }
            $templateParameters['providerAreasToTitles'] = $providerAreasToTitles;
        }
        $templateParameters['subscriberAreas'] = [];
        $templateParameters['hooksubscribers'] = [];

        if ($isSubscriber) {
            $subscriberAreas = $this->get('hook_dispatcher')->getSubscriberAreasByOwner($moduleName);
            $templateParameters['subscriberAreas'] = $subscriberAreas;

            $subscriberAreasToTitles = [];
            $subscriberAreasToCategories = [];
            $subscriberAreasAndCategories = [];
            foreach ($subscriberAreas as $subscriberArea) {
                if (isset($nonPersistedSubscribers[$subscriberArea])) {
                    $subscriberAreasToTitles[$subscriberArea] = $nonPersistedSubscribers[$subscriberArea]->getTitle();
                    $category = $nonPersistedSubscribers[$subscriberArea]->getCategory();
                } else {
                    // @deprecated
                    $subscriberAreasToTitles[$subscriberArea] = $this->get('translator.default')->__(/** @Ignore */
                        $moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getTitle());
                    $category = $this->get('translator.default')->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getCategory());
                }
                $subscriberAreasToCategories[$subscriberArea] = $category;
                $subscriberAreasAndCategories[$category][] = $subscriberArea;
            }
            $templateParameters['subscriberAreasToTitles'] = $subscriberAreasToTitles;
            $templateParameters['subscriberAreasToCategories'] = $subscriberAreasToCategories;
            $templateParameters['subscriberAreasAndCategories'] = $subscriberAreasAndCategories;
        }

        // get available subscribers that can attach to provider
        if ($isProvider && !empty($providerAreas)) {
            /** @var ExtensionEntity[] $hooksubscribers */
            $hooksubscribers = $this->getExtensionsCapableOf(CapabilityApiInterface::HOOK_SUBSCRIBER);
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
                if (!$this->get('zikula_permissions_module.api.permission')->hasPermission($hooksubscribers[$i]['name'] . "::", '::', ACCESS_ADMIN)) {
                    unset($hooksubscribers[$i]);
                    continue;
                }

                // create an instance of the subscriber's version
                // @todo @deprecated in Core-2.0 use `$bundle->getMetaData()` and assume instance of MetaData
                $hooksubscriberVersionObj = ExtensionsUtil::getVersionMeta($hooksubscribers[$i]['name']);
                if ($hooksubscriberVersionObj instanceof MetaData) {
                    // Core-1.5 Spec module
                    $hooksubscriberVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($hooksubscriberVersionObj);
                }

                // get the areas of the subscriber
                $hooksubscriberAreas = $this->get('hook_dispatcher')->getSubscriberAreasByOwner($hooksubscribers[$i]['name']);
                $hooksubscribers[$i]['areas'] = $hooksubscriberAreas;
                $amountOfAvailableSubscriberAreas += count($hooksubscriberAreas);

                $hooksubscriberAreasToTitles = []; // and get the titles
                $hooksubscriberAreasToCategories = []; // and get the categories
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    if (isset($nonPersistedSubscribers[$hooksubscriberArea])) {
                        $hooksubscriberAreasToTitles[$hooksubscriberArea] = $nonPersistedSubscribers[$hooksubscriberArea]->getTitle();
                        $category = $nonPersistedSubscribers[$hooksubscriberArea]->getCategory();
                    } else {
                        // @deprecated
                        $hooksubscriberAreasToTitles[$hooksubscriberArea] = $this->get('translator.default')->__(/** @Ignore */
                            $hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getTitle());
                        $category = $this->get('translator.default')->__(/** @Ignore */
                            $hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getCategory());
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
                $sortsByArea = $this->get('hook_dispatcher')->getBindingsFor($subscriberAreas[$i]);
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

                    // get hook provider from it's area
                    $sbaProviderModule = $this->get('hook_dispatcher')->getOwnerByArea($areaname);

                    // create an instance of the provider's version
                    // @todo @deprecated in Core-2.0 use `$bundle->getMetaData()` and assume instance of MetaData
                    $sbaProviderModuleVersionObj = ExtensionsUtil::getVersionMeta($sbaProviderModule);
                    if ($sbaProviderModuleVersionObj instanceof MetaData) {
                        // Core-1.5 Spec module
                        $sbaProviderModuleVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($sbaProviderModuleVersionObj);
                    }

                    // get the bundle title
                    if (isset($nonPersistedProviders[$areaname])) {
                        $currentSortingTitles[$areaname] = $nonPersistedProviders[$areaname]->getTitle();
                    } else {
                        // @deprecated
                        $currentSortingTitles[$areaname] = $this->get('translator.default')->__(/** @Ignore */
                            $sbaProviderModuleVersionObj->getHookProviderBundle($areaname)->getTitle());
                    }
                }
            }
            $templateParameters['areasSorting'] = $currentSorting;
            $templateParameters['areasSortingTitles'] = $currentSortingTitles;
            $templateParameters['total_attached_provider_areas'] = $amountOfAttachedProviderAreas;

            // get available providers
            /** @var ExtensionEntity[] $hookproviders */
            $hookproviders = $this->getExtensionsCapableOf(CapabilityApiInterface::HOOK_PROVIDER);
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
                if (!$this->get('zikula_permissions_module.api.permission')->hasPermission($hookproviders[$i]['name']."::", '::', ACCESS_ADMIN)) {
                    unset($hookproviders[$i]);
                    continue;
                }

                // create an instance of the provider's version
                // @todo @deprecated in Core-2.0 use `$bundle->getMetaData()` and assume instance of MetaData
                $hookproviderVersionObj = ExtensionsUtil::getVersionMeta($hookproviders[$i]['name']);
                if ($hookproviderVersionObj instanceof MetaData) {
                    // Core-1.5 Spec module
                    $hookproviderVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($hookproviderVersionObj);
                }

                // get the areas of the provider
                $hookproviderAreas = $this->get('hook_dispatcher')->getProviderAreasByOwner($hookproviders[$i]['name']);
                $hookproviders[$i]['areas'] = $hookproviderAreas;
                $amountOfAvailableProviderAreas += count($hookproviderAreas);

                $hookproviderAreasToTitles = []; // and get the titles
                $hookproviderAreasToCategories = []; // and get the categories
                $hookproviderAreasAndCategories = []; // and build array with category => areas
                foreach ($hookproviderAreas as $hookproviderArea) {
                    if (isset($nonPersistedProviders[$hookproviderArea])) {
                        $hookproviderAreasToTitles[$hookproviderArea] = $nonPersistedProviders[$hookproviderArea]->getTitle();
                        $category = $nonPersistedProviders[$hookproviderArea]->getCategory();
                    } else {
                        // @deprecated
                        $providerBundle = $hookproviderVersionObj->getHookProviderBundle($hookproviderArea);
                        $hookproviderAreasToTitles[$hookproviderArea] = $this->get('translator.default')->__(/** @Ignore */$providerBundle->getTitle());
                        $category = $this->get('translator.default')->__(/** @Ignore */$providerBundle->getCategory());
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
        $templateParameters['hookDispatcher'] = $this->get('hook_dispatcher');
        $request = $this->get('request_stack')->getCurrentRequest();
        $request->attributes->set('_zkModule', $moduleName);
        $request->attributes->set('_zkType', 'admin');
        $request->attributes->set('_zkFunc', 'Hooks');

        return $templateParameters;
    }

    /**
     * @Route("/togglestatus", options={"expose"=true})
     * @Method("POST")
     *
     * Attach/detach a subscriber area to a provider area
     *
     * @param Request $request
     *
     *  subscriberarea string area to be attached/detached
     *  providerarea   string area to attach/detach
     *
     * @return AjaxResponse
     *
     * @throws \InvalidArgumentException Thrown if either the subscriber, provider or subscriberArea parameters are empty
     * @throws \RuntimeException Thrown if either the subscriber or provider module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to either the subscriber or provider modules
     */
    public function toggleSubscribeAreaStatusAction(Request $request)
    {
        $this->checkAjaxToken();

        // get subscriberarea from POST
        $subscriberArea = $request->request->get('subscriberarea', '');
        if (empty($subscriberArea)) {
            throw new \InvalidArgumentException($this->get('translator.default')->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $this->get('hook_dispatcher')->getOwnerByArea($subscriberArea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->get('translator.default')->__f('Module "%s" is not a valid subscriber.', ['%s' => $subscriber]));
        }
        // @todo @deprecated in Core-2.0 use `$this->get('kernel')->isBundle($subscriber)`
        if (!\ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->get('translator.default')->__f('Subscriber module "%s" is not available.', ['%s' => $subscriber]));
        }
        if (!$this->get('zikula_permissions_module.api.permission')->hasPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providerarea from POST
        $providerArea = $request->request->get('providerarea', '');
        if (empty($providerArea)) {
            throw new \InvalidArgumentException($this->get('translator.default')->__('No provider area passed.'));
        }

        // get provider module based on area and do some checks
        $provider = $this->get('hook_dispatcher')->getOwnerByArea($providerArea);
        if (empty($provider)) {
            throw new \InvalidArgumentException($this->get('translator.default')->__f('Module "%s" is not a valid provider.', ['%s' => $provider]));
        }
        // @todo @deprecated in Core-2.0 use `$this->get('kernel')->isBundle($provider)`
        if (!\ModUtil::available($provider)) {
            throw new \RuntimeException($this->get('translator.default')->__f('Provider module "%s" is not available.', ['%s' => $provider]));
        }
        if (!$this->get('zikula_permissions_module.api.permission')->hasPermission($provider.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check if binding between areas exists
        $binding = $this->get('hook_dispatcher')->getBindingBetweenAreas($subscriberArea, $providerArea);
        if (!$binding) {
            $this->get('hook_dispatcher')->bindSubscriber($subscriberArea, $providerArea);
        } else {
            $this->get('hook_dispatcher')->unbindSubscriber($subscriberArea, $providerArea);
        }

        // ajax response
        $response = [
            'result' => true,
            'action' => $binding ? 'unbind' : 'bind',
            'subscriberarea' => $subscriberArea,
            'subscriberarea_id' => md5($subscriberArea),
            'providerarea' => $providerArea,
            'providerarea_id' => md5($providerArea),
            'isSubscriberSelfCapable' => $this->isCapable($subscriber, CapabilityApiInterface::HOOK_SUBSCRIBE_OWN)
        ];

        return new AjaxResponse($response);
    }

    /**
     * @Route("/changeorder", options={"expose"=true})
     * @Method("POST")
     *
     * changeproviderareaorder
     * This function changes the order of the providers' areas that are attached to a subscriber
     *
     * @param Request $request
     *
     *  subscriber    string     name of the subscriber
     *  providerorder array      array of sorted provider ids
     *
     * @return AjaxResponse
     *
     * @throws \InvalidArgumentException Thrown if the subscriber or subscriberarea parameters aren't valid
     * @throws \RuntimeException Thrown if the subscriber module isn't available
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the subscriber module
     */
    public function changeProviderAreaOrderAction(Request $request)
    {
        $this->checkAjaxToken();

        // get subscriberarea from POST
        $subscriberarea = $request->request->get('subscriberarea', '');
        if (empty($subscriberarea)) {
            throw new \InvalidArgumentException($this->get('translator.default')->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $this->get('hook_dispatcher')->getOwnerByArea($subscriberarea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->get('translator.default')->__f('Module "%s" is not a valid subscriber.', ['%s' => $subscriber]));
        }
        // @todo @deprecated in Core-2.0 use `$this->get('kernel')->isBundle($subscriber)`
        if (!\ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->get('translator.default')->__f('Subscriber module "%s" is not available.', ['%s' => $subscriber]));
        }
        if (!$this->get('zikula_permissions_module.api.permission')->hasPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providers' areas from POST
        $providerarea = $request->request->get('providerarea', '');
        if (!(is_array($providerarea) && count($providerarea) > 0)) {
            throw new \InvalidArgumentException($this->get('translator.default')->__('Providers\' areas order is not an array.'));
        }

        // set sorting
        $this->get('hook_dispatcher')->setBindOrder($subscriberarea, $providerarea);

        $ol_id = $request->request->get('ol_id', '');

        return new AjaxResponse(['result' => true, 'ol_id' => $ol_id]);
    }

    /**
     * Check the CSRF token.
     * Checks will fall back to $token check if automatic checking fails
     *
     * @param string $token Token, default null
     * @throws AccessDeniedException If the CSFR token fails
     * @throws \Exception if request is not an XmlHttpRequest
     * @return void
     */
    private function checkAjaxToken($token = null)
    {
        $currentRequest = $this->get('request_stack')->getCurrentRequest();
        if (!$currentRequest->isXmlHttpRequest()) {
            throw new \Exception();
        }
        // @todo how to SET the $_SERVER['HTTP_X_ZIKULA_AJAX_TOKEN'] ?
        $headerToken = ($currentRequest->server->has('HTTP_X_ZIKULA_AJAX_TOKEN')) ? $currentRequest->server->get('HTTP_X_ZIKULA_AJAX_TOKEN') : null;
        if ($headerToken == $currentRequest->getSession()->getId()) {
            return;
        }
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);
    }

    private function isCapable($moduleName, $type)
    {
        $nonPersisted = $this->get('zikula_hook_bundle.collector.hook_collector')->isCapable($moduleName, $type);
        $persisted =  $this->get('zikula_extensions_module.api.capability')->isCapable($moduleName, $type) ? true : false;

        return $nonPersisted || $persisted;
    }

    private function getExtensionsCapableOf($type)
    {
        $nonPersistedOwners = $this->get('zikula_hook_bundle.collector.hook_collector')->getOwnersCapableOf($type);
        $nonPersisted = [];
        foreach ($nonPersistedOwners as $nonPersistedOwner) {
            $nonPersisted[] = $this->get('zikula_extensions_module.extension_repository')->findOneBy(['name' => $nonPersistedOwner]);
        }
        $persisted = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf($type);

        return array_merge($nonPersisted, $persisted);
    }
}
