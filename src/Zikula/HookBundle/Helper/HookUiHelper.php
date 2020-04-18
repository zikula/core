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

namespace Zikula\Bundle\HookBundle\Helper;

use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * HookUiHelper class.
 */
class HookUiHelper
{
    /**
     * @var HookCollectorInterface
     */
    private $collector;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var HookDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        HookCollectorInterface $collector,
        ExtensionRepositoryInterface $extensionRepository,
        HookDispatcherInterface $dispatcher,
        PermissionApiInterface $permissionApi
    ) {
        $this->collector = $collector;
        $this->extensionRepository = $extensionRepository;
        $this->dispatcher = $dispatcher;
        $this->permissionApi = $permissionApi;
    }

    public function prepareSubscriberAreasForSubscriber(array $subscriberAreas, iterable $subscribers): array
    {
        $result = [
            'titles' => [],
            'categories' => [],
            'categoryGroups' => []
        ];

        foreach ($subscriberAreas as $subscriberArea) {
            $category = null;
            if (isset($subscribers[$subscriberArea])) {
                $result['titles'][$subscriberArea] = $subscribers[$subscriberArea]->getTitle();
                $category = $subscribers[$subscriberArea]->getCategory();
            }
            $result['categories'][$subscriberArea] = $category;
            if (null !== $category) {
                $result['categoryGroups'][$category][] = $subscriberArea;
            }
        }

        return $result;
    }

    public function prepareAvailableSubscriberAreasForProvider(string $moduleName, iterable $subscribers): array
    {
        /** @var ExtensionEntity[] $hookSubscribers */
        $hookSubscribers = $this->getExtensionsCapableOf(HookCollectorInterface::HOOK_SUBSCRIBER);
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
            if (!$this->permissionApi->hasPermission($hookSubscriber->getName() . '::', '::', ACCESS_ADMIN)) {
                unset($hookSubscribers[$i]);
                continue;
            }

            // get the areas of the subscriber
            $subscriberAreas = $this->collector->getSubscriberAreasByOwner($hookSubscriber->getName());
            $hookSubscribers[$i]['areas'] = $subscriberAreas;
            $amountOfAvailableSubscriberAreas += count($subscriberAreas);

            $subscriberAreasToTitles = []; // and get the titles
            $subscriberAreasToCategories = []; // and get the categories
            foreach ($subscriberAreas as $area) {
                $category = null;
                if (isset($subscribers[$area])) {
                    $subscriberAreasToTitles[$area] = $subscribers[$area]->getTitle();
                    $category = $subscribers[$area]->getCategory();
                }
                $subscriberAreasToCategories[$area] = $category;
            }
            $hookSubscribers[$i]['areasToTitles'] = $subscriberAreasToTitles;
            $hookSubscribers[$i]['areasToCategories'] = $subscriberAreasToCategories;
        }

        return [$hookSubscribers, $amountOfAvailableSubscriberAreas];
    }

    public function prepareAttachedProvidersForSubscriber(array $subscriberAreas, iterable $providers): array
    {
        $sorting = [];
        $sortingTitles = [];
        $amountOfAttachedProviderAreas = 0;
        foreach ($subscriberAreas as $hookSubscriber) {
            $sortsByArea = $this->dispatcher->getBindingsFor($hookSubscriber);
            foreach ($sortsByArea as $sba) {
                $areaname = $sba['areaname'];
                $category = $sba['category'];

                if (!isset($sorting[$category])) {
                    $sorting[$category] = [];
                }

                if (!isset($sorting[$category][$hookSubscriber])) {
                    $sorting[$category][$hookSubscriber] = [];
                }

                $sorting[$category][$hookSubscriber][] = $areaname;
                $amountOfAttachedProviderAreas++;

                // get the bundle title
                if (isset($providers[$areaname])) {
                    $sortingTitles[$areaname] = $providers[$areaname]->getTitle();
                }
            }
        }

        return [$sorting, $sortingTitles, $amountOfAttachedProviderAreas];
    }

    public function prepareAvailableProviderAreasForSubscriber(string $moduleName, iterable $providers, bool $isSubscriberSelfCapable): array
    {
        /** @var ExtensionEntity[] $hookProviders */
        $hookProviders = $this->getExtensionsCapableOf(HookCollectorInterface::HOOK_PROVIDER);
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
            if (!$this->permissionApi->hasPermission($hookProvider->getName() . '::', '::', ACCESS_ADMIN)) {
                unset($hookProviders[$i]);
                continue;
            }

            // get the areas of the provider
            $providerAreas = $this->collector->getProviderAreasByOwner($hookProvider->getName());
            $hookProviders[$i]['areas'] = $providerAreas;
            $amountOfAvailableProviderAreas += count($providerAreas);

            $providerAreasToTitles = []; // and get the titles
            $providerAreasToCategories = []; // and get the categories
            $providerAreasAndCategories = []; // and build array with category => areas
            foreach ($providerAreas as $area) {
                $category = null;
                if (isset($providers[$area])) {
                    $providerAreasToTitles[$area] = $providers[$area]->getTitle();
                    $category = $providers[$area]->getCategory();
                }
                $providerAreasToCategories[$area] = $category;
                $providerAreasAndCategories[$category][] = $area;
            }
            $hookProviders[$i]['areasToTitles'] = $providerAreasToTitles;
            $hookProviders[$i]['areasToCategories'] = $providerAreasToCategories;
            $hookProviders[$i]['areasAndCategories'] = $providerAreasAndCategories;
        }

        return [$hookProviders, $amountOfAvailableProviderAreas];
    }

    private function getExtensionsCapableOf(string $type): array
    {
        $owners = $this->collector->getOwnersCapableOf($type);
        $extensions = [];
        foreach ($owners as $owner) {
            $extensions[] = $this->extensionRepository->findOneBy(['name' => $owner]);
        }

        return $extensions;
    }
}
