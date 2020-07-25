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

namespace Zikula\BlocksModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;
use Zikula\BlocksModule\Block\HtmlBlock;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;
use Zikula\BlocksModule\Form\Type\AdminViewFilterType;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class AdminController
 *
 * @Route("/admin")
 * @PermissionCheck("admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/view")
     * @Theme("admin")
     * @Template("@ZikulaBlocksModule/Admin/view.html.twig")
     *
     * View all blocks.
     */
    public function viewAction(
        Request $request,
        BlockRepositoryInterface $blockRepository,
        BlockPositionRepositoryInterface $positionRepository,
        BlockApiInterface $blockApi,
        LocaleApiInterface $localeApi,
        RouterInterface $router
    ): array {
        $sessionFilterData = [];
        if ($request->hasSession() && ($session = $request->getSession())) {
            $clear = $request->request->get('clear', 0);
            if ($clear) {
                $session->set('zikulablocksmodule.filter', []);
            }
            $sessionFilterData = $session->get('zikulablocksmodule.filter', []);
        }
        $sortField = $request->query->get('sort-field', $sessionFilterData['sort-field'] ?? 'bid');
        $currentSortDirection = $request->query->get('sort-direction', $sessionFilterData['sort-direction'] ?? Column::DIRECTION_ASCENDING);
        $filterForm = $this->createForm(AdminViewFilterType::class, $sessionFilterData, [
            'action' => $this->generateUrl('zikulablocksmodule_admin_view'),
            'method' => 'POST',
            'moduleChoices' => array_flip($blockApi->getModulesContainingBlocks()),
            'positionChoices' => array_flip($positionRepository->getPositionChoiceArray()),
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
        if ($request->hasSession() && ($session = $request->getSession())) {
            $session->set('zikulablocksmodule.filter', $filterData); // remember
        }

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
            || (!empty($filterData['active']) && in_array($filterData['active'], [0, 1], true));

        $this->checkForDeprecatedBlockTypes($blockRepository);

        return [
            'blocks' => $blockRepository->getFilteredBlocks($filterData),
            'positions' => $positionRepository->findAll(),
            'filter_active' => $filterActive,
            'sort' => $sortableColumns->generateSortableColumns(),
            'filterForm' => $filterForm->createView()
        ];
    }

    private function checkForDeprecatedBlockTypes(BlockRepositoryInterface $blockRepository): void
    {
        $blocks = $blockRepository->findBy(['bkey' => HtmlBlock::class]);
        if (isset($count) && $count($blocks) > 0) {
            $this->addFlash('warning', $this->trans(
                'A block of type %type% is in use. This type is deprecated and will not be available in future versions of Zikula. Please replace it with %replace%',
                [
                '%type%' => '<code>' . HtmlBlock::class . '</code>',
                '%replace%' => '<code>Zikula\StaticContentModule\Block\HtmlBlock</code>'
            ]
            ));
        }
    }
}
