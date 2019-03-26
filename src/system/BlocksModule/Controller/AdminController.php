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

namespace Zikula\BlocksModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\BlocksModule\Form\Type\AdminViewFilterType;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class AdminController
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/view")
     * @Theme("admin")
     * @Template("ZikulaBlocksModule:Admin:view.html.twig")
     *
     * View all blocks.
     *
     * @param Request $request
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockPositionRepositoryInterface $positionRepository
     * @param BlockApiInterface $blockApi
     * @param LocaleApiInterface $localeApi
     * @param RouterInterface $router
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     */
    public function viewAction(
        Request $request,
        BlockRepositoryInterface $blockRepository,
        BlockPositionRepositoryInterface $positionRepository,
        BlockApiInterface $blockApi,
        LocaleApiInterface $localeApi,
        RouterInterface $router
    ) {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $clear = $request->request->get('clear', 0);
        if ($clear) {
            $request->getSession()->set('zikulablocksmodule.filter', []);
        }
        $sessionFilterData = $request->getSession()->get('zikulablocksmodule.filter', []);
        $sortField = $request->query->get('sort-field', $sessionFilterData['sort-field'] ?? 'bid');
        $currentSortDirection = $request->query->get('sort-direction', $sessionFilterData['sort-direction'] ?? Column::DIRECTION_ASCENDING);
        $filterForm = $this->createForm(AdminViewFilterType::class, $sessionFilterData, [
            'action' => $this->generateUrl('zikulablocksmodule_admin_view'),
            'method' => 'POST',
            'moduleChoices' => $blockApi->getModulesContainingBlocks(),
            'positionChoices' => $positionRepository->getPositionChoiceArray(),
            'localeChoices' => $localeApi->getSupportedLocaleNames(null, $request->getLocale())
        ]);
        $filterFormClone = clone $filterForm;

        $filterForm->handleRequest($request);
        $filterData = $sessionFilterData;
        if ($filterForm->isSubmitted()) {
            if ($filterForm->get('filterButton')->isClicked()) {
                $filterData = $filterForm->getData();
            } else {
                $filterForm = $filterFormClone; //  set to empty form on 'clear' submission.
            }
        }
        $filterData['sort-field'] = $sortField;
        $filterData['sort-direction'] = $currentSortDirection;
        $request->getSession()->set('zikulablocksmodule.filter', $filterData); // remember

        $sortableColumns = new SortableColumns($router, 'zikulablocksmodule_admin_view');
        $sortableColumns->addColumn(new Column('bid')); // first added is automatically the default
        $sortableColumns->addColumn(new Column('title'));
        $sortableColumns->addColumn(new Column('blocktype'));
        $sortableColumns->addColumn(new Column('language'));
        $sortableColumns->addColumn(new Column('state'));
        $sortableColumns->setOrderBy($sortableColumns->getColumn($sortField), $currentSortDirection);
        $sortableColumns->setAdditionalUrlParameters([
            'position' => $filterData['position'] ?? null,
            'module' => $filterData['module'] ?? null,
            'language' => $filterData['language'] ?? null,
            'status' => $filterData['status'] ?? null,
        ]);

        $filterActive = !empty($filterData['position']) || !empty($filterData['module']) || !empty($filterData['language'])
            || (!empty($filterData['active']) && in_array($filterData['active'], [0, 1]));

        return [
            'blocks' => $blockRepository->getFilteredBlocks($filterData),
            'positions' => $positionRepository->findAll(),
            'filter_active' => $filterActive,
            'sort' => $sortableColumns->generateSortableColumns(),
            'filterForm' => $filterForm->createView()
        ];
    }
}
