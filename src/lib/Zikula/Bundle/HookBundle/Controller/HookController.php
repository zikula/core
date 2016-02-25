<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\HookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Component\HookDispatcher\HookDispatcherInterface;
use Zikula\Core\AbstractBundle;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Util as ExtensionsUtil;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class HookController
 * @package Zikula\HookBundle\Controller
 * @Route("/hooks")
 */
class HookController extends AbstractController
{
    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    /**
     * Constructor.
     *
     * @param AbstractBundle $bundle
     *            A AbstractBundle instance
     * @throws \InvalidArgumentException
     */
    public function __construct(AbstractBundle $bundle)
    {
        parent::__construct($bundle);
        $this->hookDispatcher = $bundle->getContainer()->get('hook_dispatcher');
    }

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
        if (!$this->hasPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // create an instance of the module's version
        // we will use it to get the bundles
        $moduleVersionObj = ExtensionsUtil::getVersionMeta($moduleName);
        if ($moduleVersionObj instanceof MetaData) {
            // Core-2.0 Spec module
            $moduleVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($moduleVersionObj);
        }

        // find out the capabilities of the module
        $isProvider = (\ModUtil::isCapable($moduleName, \HookUtil::PROVIDER_CAPABLE)) ? true : false;
        $templateParameters['isProvider'] = $isProvider;

        $isSubscriber = (\ModUtil::isCapable($moduleName, \HookUtil::SUBSCRIBER_CAPABLE)) ? true : false;
        $templateParameters['isSubscriber'] = $isSubscriber;

        $isSubscriberSelfCapable = (\HookUtil::isSubscriberSelfCapable($moduleName)) ? true : false;
        $templateParameters['isSubscriberSelfCapable'] = $isSubscriberSelfCapable;
        $templateParameters['providerAreas'] = [];

        // get areas of module and bundle titles also
        if ($isProvider) {
            $providerAreas = $this->hookDispatcher->getProviderAreasByOwner($moduleName);
            $templateParameters['providerAreas'] = $providerAreas;

            $providerAreasToTitles = array();
            foreach ($providerAreas as $providerArea) {
                $providerAreasToTitles[$providerArea] = $this->__(/** @Ignore */$moduleVersionObj->getHookProviderBundle($providerArea)->getTitle());
            }
            $templateParameters['providerAreasToTitles'] = $providerAreasToTitles;
        }
        $templateParameters['subscriberAreas'] = [];
        $templateParameters['hooksubscribers'] = [];

        if ($isSubscriber) {
            $subscriberAreas = $this->hookDispatcher->getSubscriberAreasByOwner($moduleName);
            $templateParameters['subscriberAreas'] = $subscriberAreas;

            $subscriberAreasToTitles = array();
            foreach ($subscriberAreas as $subscriberArea) {
                $subscriberAreasToTitles[$subscriberArea] = $this->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getTitle());
            }
            $templateParameters['subscriberAreasToTitles'] = $subscriberAreasToTitles;

            $subscriberAreasToCategories = array();
            foreach ($subscriberAreas as $subscriberArea) {
                $category = $this->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getCategory());
                $subscriberAreasToCategories[$subscriberArea] = $category;
            }
            $templateParameters['subscriberAreasToCategories'] = $subscriberAreasToCategories;

            $subscriberAreasAndCategories = array();
            foreach ($subscriberAreas as $subscriberArea) {
                $category = $this->__(/** @Ignore */$moduleVersionObj->getHookSubscriberBundle($subscriberArea)->getCategory());
                $subscriberAreasAndCategories[$category][] = $subscriberArea;
            }
            $templateParameters['subscriberAreasAndCategories'] = $subscriberAreasAndCategories;
        }

        // get available subscribers that can attach to provider
        if ($isProvider && !empty($providerAreas)) {
            $hooksubscribers = \ModUtil::getModulesCapableOf(\HookUtil::SUBSCRIBER_CAPABLE);
            $total_hooksubscribers = count($hooksubscribers);
            $total_available_subscriber_areas = 0;
            for ($i = 0; $i < $total_hooksubscribers; $i++) {
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if ($hooksubscribers[$i]['name'] == $moduleName) {
                    unset($hooksubscribers[$i]);
                    continue;
                }
                // does the user have admin permissions on the subscriber module?
                if (!$this->hasPermission($hooksubscribers[$i]['name'] . "::", '::', ACCESS_ADMIN)) {
                    unset($hooksubscribers[$i]);
                    continue;
                }

                // create an instance of the subscriber's version
                $hooksubscriberVersionObj = ExtensionsUtil::getVersionMeta($hooksubscribers[$i]['name']);
                if ($hooksubscriberVersionObj instanceof MetaData) {
                    // Core-2.0 Spec module
                    $hooksubscriberVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($hooksubscriberVersionObj);
                }

                // get the areas of the subscriber
                $hooksubscriberAreas = $this->hookDispatcher->getSubscriberAreasByOwner($hooksubscribers[$i]['name']);
                $hooksubscribers[$i]['areas'] = $hooksubscriberAreas;
                $total_available_subscriber_areas += count($hooksubscriberAreas);

                // and get the titles
                $hooksubscriberAreasToTitles = array();
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    $hooksubscriberAreasToTitles[$hooksubscriberArea] = $this->__(/** @Ignore */$hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getTitle());
                }
                $hooksubscribers[$i]['areasToTitles'] = $hooksubscriberAreasToTitles;

                // and get the categories
                $hooksubscriberAreasToCategories = array();
                foreach ($hooksubscriberAreas as $hooksubscriberArea) {
                    $category = $this->__(/** @Ignore */$hooksubscriberVersionObj->getHookSubscriberBundle($hooksubscriberArea)->getCategory());
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
            $currentSortingTitles = array();
            $currentSorting = array();
            $total_attached_provider_areas = 0;
            for ($i = 0; $i < count($subscriberAreas); $i++) {
                $sortsByArea = $this->hookDispatcher->getBindingsFor($subscriberAreas[$i]);
                foreach ($sortsByArea as $sba) {
                    $areaname = $sba['areaname'];
                    $category = $sba['category'];

                    if (!isset($currentSorting[$category])) {
                        $currentSorting[$category] = array();
                    }

                    if (!isset($currentSorting[$category][$subscriberAreas[$i]])) {
                        $currentSorting[$category][$subscriberAreas[$i]] = array();
                    }

                    array_push($currentSorting[$category][$subscriberAreas[$i]], $areaname);
                    $total_attached_provider_areas++;

                    // get hook provider from it's area
                    $sbaProviderModule = $this->hookDispatcher->getOwnerByArea($areaname);

                    // create an instance of the provider's version
                    $sbaProviderModuleVersionObj = ExtensionsUtil::getVersionMeta($sbaProviderModule);
                    if ($sbaProviderModuleVersionObj instanceof MetaData) {
                        // Core-2.0 Spec module
                        $sbaProviderModuleVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($sbaProviderModuleVersionObj);
                    }

                    // get the bundle title
                    $currentSortingTitles[$areaname] = $this->__(/** @Ignore */$sbaProviderModuleVersionObj->getHookProviderBundle($areaname)->getTitle());
                }
            }
            $templateParameters['areasSorting'] = $currentSorting;
            $templateParameters['areasSortingTitles'] = $currentSortingTitles;
            $templateParameters['total_attached_provider_areas'] = $total_attached_provider_areas;

            // get available providers
            $hookproviders = \ModUtil::getModulesCapableOf(\HookUtil::PROVIDER_CAPABLE);
            $total_hookproviders = count($hookproviders);
            $total_available_provider_areas = 0;
            for ($i = 0; $i < $total_hookproviders; $i++) {
                // don't allow subscriber and provider to be the same
                // unless subscriber has the ability to connect to it's own providers
                if ($hookproviders[$i]['name'] == $moduleName && !$isSubscriberSelfCapable) {
                    unset($hookproviders[$i]);
                    continue;
                }

                // does the user have admin permissions on the provider module?
                if (!$this->hasPermission($hookproviders[$i]['name']."::", '::', ACCESS_ADMIN)) {
                    unset($hookproviders[$i]);
                    continue;
                }

                // create an instance of the provider's version
                $hookproviderVersionObj = ExtensionsUtil::getVersionMeta($hookproviders[$i]['name']);
                if ($hookproviderVersionObj instanceof MetaData) {
                    // Core-2.0 Spec module
                    $hookproviderVersionObj = $this->get('zikula_extensions_module.api.hook')->getHookContainerInstance($hookproviderVersionObj);
                }

                // get the areas of the provider
                $hookproviderAreas = $this->hookDispatcher->getProviderAreasByOwner($hookproviders[$i]['name']);
                $hookproviders[$i]['areas'] = $hookproviderAreas;
                $total_available_provider_areas += count($hookproviderAreas);

                // and get the titles
                $hookproviderAreasToTitles = array();
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $hookproviderAreasToTitles[$hookproviderArea] = $this->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getTitle());
                }
                $hookproviders[$i]['areasToTitles'] = $hookproviderAreasToTitles;

                // and get the categories
                $hookproviderAreasToCategories = array();
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $hookproviderAreasToCategories[$hookproviderArea] = $this->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getCategory());
                }
                $hookproviders[$i]['areasToCategories'] = $hookproviderAreasToCategories;

                // and build array with category => areas
                $hookproviderAreasAndCategories = array();
                foreach ($hookproviderAreas as $hookproviderArea) {
                    $category = $this->__(/** @Ignore */$hookproviderVersionObj->getHookProviderBundle($hookproviderArea)->getCategory());
                    $hookproviderAreasAndCategories[$category][] = $hookproviderArea;
                }
                $hookproviders[$i]['areasAndCategories'] = $hookproviderAreasAndCategories;
            }
            $templateParameters['hookproviders'] = $hookproviders;
            $templateParameters['total_available_provider_areas'] = $total_available_provider_areas;
        } else {
            $templateParameters['hookproviders'] = [];
        }

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
            throw new \InvalidArgumentException($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $this->hookDispatcher->getOwnerByArea($subscriberArea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!\ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        if (!$this->hasPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providerarea from POST
        $providerArea = $request->request->get('providerarea', '');
        if (empty($providerArea)) {
            throw new \InvalidArgumentException($this->__('No provider area passed.'));
        }

        // get provider module based on area and do some checks
        $provider = $this->hookDispatcher->getOwnerByArea($providerArea);
        if (empty($provider)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid provider.', $provider));
        }
        if (!\ModUtil::available($provider)) {
            throw new \RuntimeException($this->__f('Provider module "%s" is not available.', $provider));
        }
        if (!$this->hasPermission($provider.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // check if binding between areas exists
        $binding = $this->hookDispatcher->getBindingBetweenAreas($subscriberArea, $providerArea);
        if (!$binding) {
            $this->hookDispatcher->bindSubscriber($subscriberArea, $providerArea);
        } else {
            $this->hookDispatcher->unbindSubscriber($subscriberArea, $providerArea);
        }

        // ajax response
        $response = array(
            'result' => true,
            'action' => $binding ? 'unbind' : 'bind',
            'subscriberarea' => $subscriberArea,
            'subscriberarea_id' => md5($subscriberArea),
            'providerarea' => $providerArea,
            'providerarea_id' => md5($providerArea),
            'isSubscriberSelfCapable' => ($this->get('zikula_extensions_module.api.capability')->isCapable($subscriber, CapabilityApiInterface::HOOK_SUBSCRIBE_OWN) ? true : false)
        );

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
            throw new \InvalidArgumentException($this->__('No subscriber area passed.'));
        }

        // get subscriber module based on area and do some checks
        $subscriber = $this->hookDispatcher->getOwnerByArea($subscriberarea);
        if (empty($subscriber)) {
            throw new \InvalidArgumentException($this->__f('Module "%s" is not a valid subscriber.', $subscriber));
        }
        if (!\ModUtil::available($subscriber)) {
            throw new \RuntimeException($this->__f('Subscriber module "%s" is not available.', $subscriber));
        }
        if (!$this->hasPermission($subscriber.'::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get providers' areas from POST
        $providerarea = $request->request->get('providerarea', '');
        if (!(is_array($providerarea) && count($providerarea) > 0)) {
            throw new \InvalidArgumentException($this->__('Providers\' areas order is not an array.'));
        }

        // set sorting
        $this->hookDispatcher->setBindOrder($subscriberarea, $providerarea);

        $ol_id = $request->request->get('ol_id', '');

        return new AjaxResponse(array('result' => true, 'ol_id' => $ol_id));
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
