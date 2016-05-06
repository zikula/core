<?php
/**
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Util as ExtensionsUtil;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
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
     * @return Response
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
        $moduleVersionObj = ExtensionsUtil::getVersionMeta($moduleName);
        if ($moduleVersionObj instanceof MetaData) {
            // Core-2.0 Spec module
            $moduleVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($moduleVersionObj);
        }

        // find out the capabilities of the module
        $isProvider = ($this->get('zikula_extensions_module.api.capability')->isCapable($moduleName, CapabilityApiInterface::HOOK_PROVIDER)) ? true : false;
        $templateParameters['isProvider'] = $isProvider;

        $isSubscriber = ($this->get('zikula_extensions_module.api.capability')->isCapable($moduleName, CapabilityApiInterface::HOOK_SUBSCRIBER)) ? true : false;
        $templateParameters['isSubscriber'] = $isSubscriber;

        $isSubscriberSelfCapable = ($this->get('zikula_extensions_module.api.capability')->isCapable($moduleName, CapabilityApiInterface::HOOK_SUBSCRIBE_OWN)) ? true : false;
        $templateParameters['isSubscriberSelfCapable'] = $isSubscriberSelfCapable;
        $templateParameters['providerAreas'] = [];

        // get areas of module and bundle titles also
        if ($isProvider) {
            $providerAreas = $this->get('hook_dispatcher')->getProviderAreasByOwner($moduleName);
            $templateParameters['providerAreas'] = $providerAreas;

            $providerAreasToTitles = [];
            foreach ($providerAreas as $providerArea) {
                $providerAreasToTitles[$providerArea] = $this->get('translator.default')->__(/** @Ignore */$moduleVersionObj->getHookProviderBundle($providerArea)->getTitle());
            }
            $templateParameters['providerAreasToTitles'] = $providerAreasToTitles;
        }
        $templateParameters['subscriberAreas'] = [];
        $templateParameters['hooksubscribers'] = [];

        if ($isSubscriber) {
            $subscriberAreas = $this->get('hook_dispatcher')->getSubscriberAreasByOwner($moduleName);
            $templateParameters['subscriberAreas'] = $subscriberAreas;

            $subscriberAreasToTitles = [];
            foreach ($subscriberAreas as $subscriberArea) {
                $subscriberAreasToTitles[$subscriberArea] = $this->get('translator.default')->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getTitle());
            }
            $templateParameters['subscriberAreasToTitles'] = $subscriberAreasToTitles;

            $subscriberAreasToCategories = [];
            foreach ($subscriberAreas as $subscriberArea) {
                $category = $this->get('translator.default')->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getCategory());
                $subscriberAreasToCategories[$subscriberArea] = $category;
            }
            $templateParameters['subscriberAreasToCategories'] = $subscriberAreasToCategories;

            $subscriberAreasAndCategories = [];
            foreach ($subscriberAreas as $subscriberArea) {
                $category = $this->get('translator.default')->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getCategory());
                $subscriberAreasAndCategories[$category][] = $subscriberArea;
            }
            $templateParameters['subscriberAreasAndCategories'] = $subscriberAreasAndCategories;
        }

        // get available subscribers that can attach to provider
        if ($isProvider && !empty($providerAreas)) {
            /** @var ExtensionEntity[] $hooksubscribers */
            $hooksubscribers = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(CapabilityApiInterface::HOOK_SUBSCRIBER);
            $total_hooksubscribers = count($hooksubscribers);
            $total_available_subscriber_areas = 0;
            for ($i = 0; $i < $total_hooksubscribers; $i++) {
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
                $hooksubscriberVersionObj = ExtensionsUtil::getVersionMeta($hooksubscribers[$i]['name']);
                if ($hooksubscriberVersionObj instanceof MetaData) {
                    // Core-2.0 Spec module
                    $hooksubscriberVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($hooksubscriberVersionObj);
                }

                // get the areas of the subscriber
                $hooksubscriberAreas = $this->get('hook_dispatcher')->getSubscriberAreasByOwner($hooksubscribers[$i]['name']);
                $hooksubscribers[$i]['areas'] = $hooksubscriberAreas;
                $total_available_subscriber_areas += count($hooksubscriberAreas);

                // and get the titles
                $hooksubscriberAreasToTitles = [];
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    $hooksubscriberAreasToTitles[$hooksubscriberArea] = $this->get('translator.default')->__(/** @Ignore */$hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getTitle());
                }
                $hooksubscribers[$i]['areasToTitles'] = $hooksubscriberAreasToTitles;

                // and get the categories
                $hooksubscriberAreasToCategories = [];
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    $category = $this->get('translator.default')->__(/** @Ignore */$hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getCategory());
                    $hooksubscriberAreasToCategories[$hooksubscriberArea] = $category;
                }
                $hooksubscribers[$i]['areasToCategories'] = $hooksubscriberAreasToCategories;
            }
            $templateParameters['hooksubscribers'] = $hooksubscribers;
            $templateParameters['total_available_subscriber_areas'] = $total_available_subscriber_areas;
        } else {
            $templateParameters['total_available_subscriber_areas'] = 0;
        }

        // get providers that are already attached to the subscriber
        // and providers that can attach to the subscriber
        if ($isSubscriber && !empty($subscriberAreas)) {
            // get current sorting
            $currentSortingTitles = [];
            $currentSorting = [];
            $total_attached_provider_areas = 0;
            for ($i = 0; $i < count($subscriberAreas); $i++) {
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
                    $total_attached_provider_areas++;

                    // get hook provider from it's area
                    $sbaProviderModule = $this->get('hook_dispatcher')->getOwnerByArea($areaname);

                    // create an instance of the provider's version
                    $sbaProviderModuleVersionObj = ExtensionsUtil::getVersionMeta($sbaProviderModule);
                    if ($sbaProviderModuleVersionObj instanceof MetaData) {
                        // Core-2.0 Spec module
                        $sbaProviderModuleVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($sbaProviderModuleVersionObj);
                    }

                    // get the bundle title
                    $currentSortingTitles[$areaname] = $this->get('translator.default')->__(/** @Ignore */$sbaProviderModuleVersionObj->getHookProviderBundle($areaname)->getTitle());
                }
            }
            $templateParameters['areasSorting'] = $currentSorting;
            $templateParameters['areasSortingTitles'] = $currentSortingTitles;
            $templateParameters['total_attached_provider_areas'] = $total_attached_provider_areas;

            // get available providers
            /** @var ExtensionEntity[] $hookproviders */
            $hookproviders = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf(CapabilityApiInterface::HOOK_PROVIDER);
            $total_hookproviders = count($hookproviders);
            $total_available_provider_areas = 0;
            for ($i = 0; $i < $total_hookproviders; $i++) {
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
                $hookproviderVersionObj = ExtensionsUtil::getVersionMeta($hookproviders[$i]['name']);
                if ($hookproviderVersionObj instanceof MetaData) {
                    // Core-2.0 Spec module
                    $hookproviderVersionObj = $this->get('zikula_hook_bundle.api.hook')->getHookContainerInstance($hookproviderVersionObj);
                }

                // get the areas of the provider
                $hookproviderAreas = $this->get('hook_dispatcher')->getProviderAreasByOwner($hookproviders[$i]['name']);
                $hookproviders[$i]['areas'] = $hookproviderAreas;
                $total_available_provider_areas += count($hookproviderAreas);

                // and get the titles
                $hookproviderAreasToTitles = [];
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $hookproviderAreasToTitles[$hookproviderArea] = $this->get('translator.default')->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getTitle());
                }
                $hookproviders[$i]['areasToTitles'] = $hookproviderAreasToTitles;

                // and get the categories
                $hookproviderAreasToCategories = [];
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $hookproviderAreasToCategories[$hookproviderArea] = $this->get('translator.default')->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getCategory());
                }
                $hookproviders[$i]['areasToCategories'] = $hookproviderAreasToCategories;

                // and build array with category => areas
                $hookproviderAreasAndCategories = [];
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $category = $this->get('translator.default')->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getCategory());
                    $hookproviderAreasAndCategories[$category][] = $hookproviderArea;
                }
                $hookproviders[$i]['areasAndCategories'] = $hookproviderAreasAndCategories;
            }
            $templateParameters['hookproviders'] = $hookproviders;
            $templateParameters['total_available_provider_areas'] = $total_available_provider_areas;
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
            throw new \InvalidArgumentException($this->get('translator.default')->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!\ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->get('translator.default')->__f('Subscriber module "%s" is not available.', $subscriber));
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
            throw new \InvalidArgumentException($this->get('translator.default')->__f('Module "%s" is not a valid provider.', $provider));
        }
        if (!\ModUtil::available($provider)) {
            throw new \RuntimeException($this->get('translator.default')->__f('Provider module "%s" is not available.', $provider));
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
            'isSubscriberSelfCapable' => ($this->get('zikula_extensions_module.api.capability')->isCapable($subscriber, CapabilityApiInterface::HOOK_SUBSCRIBE_OWN) ? true : false)
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
            throw new \InvalidArgumentException($this->get('translator.default')->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!\ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->get('translator.default')->__f('Subscriber module "%s" is not available.', $subscriber));
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
     * @todo move this to AbstractController
     * Check the CSRF token.
     * Checks will fall back to $token check if automatic checking fails.
     *
     * @param string $token Token, default null.
     * @throws AccessDeniedException If the CSFR token fails.
     * @throws \Exception if request is not an XmlHttpRequest
     * @return void
     */
    public function checkAjaxToken($token = null)
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
}
