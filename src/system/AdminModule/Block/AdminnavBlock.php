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

namespace Zikula\AdminModule\Block;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Administrative navigation block
 */
class AdminnavBlock extends AbstractBlockHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AdminCategoryRepositoryInterface
     */
    private $adminCategoryRepository;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var CapabilityApiInterface
     */
    private $capabilityApi;

    public function display(array $properties): string
    {
        if (!$this->hasPermission('ZikulaAdminModule:adminnavblock', $properties['title'] . '::' . $properties['bid'], ACCESS_ADMIN)) {
            return '';
        }

        // Get all categories
        $items = $this->adminCategoryRepository->findBy([], ['sortorder' => 'ASC']);

        // Check for no items returned
        if (empty($items)) {
            return '';
        }

        // get admin capable modules
        $adminModules = $this->capabilityApi->getExtensionsCapableOf('admin');
        $defaultCategory = $this->variableApi->get('ZikulaAdminModule', 'defaultcategory');

        // Display each item, permissions permitting
        $adminCategories = [];
        foreach ($items as $item) {
            if (!$this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                continue;
            }

            $adminLinks = [];
            /** @var ExtensionEntity[] $adminModules */
            foreach ($adminModules as $adminModule) {
                $category = $this->adminCategoryRepository->getModuleCategory($adminModule->getId());
                if (null === $category) {
                    continue;
                }
                if ($category['cid'] === $item['cid'] || (false === $category['cid'] && $item['cid'] === $defaultCategory)) {
                    $menuText = $adminModule->getDisplayname();
                    // url
                    try {
                        $menuTextUrl = isset($adminModule['capabilities']['admin']['route']) ? $this->router->generate($adminModule['capabilities']['admin']['route']) : $adminModule['capabilities']['admin']['url'];
                    } catch (RouteNotFoundException $routeNotFoundException) {
                        $menuTextUrl = 'javascript:void(0)';
                        $menuText .= ' (<i class="fas fa-exclamation-triangle"></i> ' . $this->trans('invalid route') . ')';
                    }
                    $adminLinks[] = [
                        'menuTextUrl' => $menuTextUrl,
                        'menuTextTitle' => $menuText
                    ];
                }
            }
            $adminCategories[] = [
                'url' => $this->router->generate('zikulaadminmodule_admin_adminpanel', ['cid' => $item['cid']]),
                'title' => $item['name'],
                'modules' => $adminLinks
            ];
        }

        $templateParameters = [
            'adminCategories' => $adminCategories
        ];

        return $this->renderView('@ZikulaAdminModule/Block/adminNav.html.twig', $templateParameters);
    }

    /**
     * @required
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * @required
     */
    public function setAdminCategoryRepository(AdminCategoryRepositoryInterface $adminCategoryRepository): void
    {
        $this->adminCategoryRepository = $adminCategoryRepository;
    }

    /**
     * @required
     */
    public function setVariableApi(VariableApiInterface $variableApi): void
    {
        $this->variableApi = $variableApi;
    }

    /**
     * @required
     */
    public function setCapabilityApi(CapabilityApiInterface $capabilityApi): void
    {
        $this->capabilityApi = $capabilityApi;
    }
}
