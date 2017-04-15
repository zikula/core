<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\BlocksModule\Form\Type\AdminViewFilterType;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
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
     * @Template
     *
     * View all blocks.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     */
    public function viewAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $clear = $request->request->get('clear', 0);
        if ($clear) {
            $request->getSession()->set('zikulablocksmodule.filter', []);
        }
        $sessionFilterData = $request->getSession()->get('zikulablocksmodule.filter', []);
        $sortField = $request->query->get('sort-field', isset($sessionFilterData['sort-field']) ? $sessionFilterData['sort-field'] : 'bid');
        $currentSortDirection = $request->query->get('sort-direction', isset($sessionFilterData['sort-direction']) ? $sessionFilterData['sort-direction'] : Column::DIRECTION_ASCENDING);
        $filterForm = $this->createForm(AdminViewFilterType::class, $sessionFilterData, [
            'action' => $this->generateUrl('zikulablocksmodule_admin_view'),
            'method' => 'POST',
            'translator' => $this->get('translator'),
            'moduleChoices' => $this->get('zikula_blocks_module.api.block')->getModulesContainingBlocks(),
            'positionChoices' => $this->getDoctrine()->getRepository('ZikulaBlocksModule:BlockPositionEntity')->getPositionChoiceArray(),
            'localeChoices' => $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(null, $request->getLocale())
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

        $sortableColumns = new SortableColumns($this->get('router'), 'zikulablocksmodule_admin_view');
        $sortableColumns->addColumn(new Column('bid')); // first added is automatically the default
        $sortableColumns->addColumn(new Column('title'));
        $sortableColumns->addColumn(new Column('blocktype'));
        $sortableColumns->addColumn(new Column('language'));
        $sortableColumns->addColumn(new Column('state'));
        $sortableColumns->setOrderBy($sortableColumns->getColumn($sortField), $currentSortDirection);
        $sortableColumns->setAdditionalUrlParameters([
            'position' => isset($filterData['position']) ? $filterData['position'] : null,
            'module' => isset($filterData['module']) ? $filterData['module'] : null,
            'language' => isset($filterData['language']) ? $filterData['language'] : null,
            'status' => isset($filterData['status']) ? $filterData['status'] : null,
        ]);

        $templateParameters = [];
        $templateParameters['blocks'] = $this->getDoctrine()->getManager()->getRepository('ZikulaBlocksModule:BlockEntity')->getFilteredBlocks($filterData);
        $templateParameters['positions'] = $this->getDoctrine()->getManager()->getRepository('ZikulaBlocksModule:BlockPositionEntity')->findAll();
        $templateParameters['filter_active'] = !empty($filterData['position']) || !empty($filterData['module']) || !empty($filterData['language']) || (!empty($filterData['active']) && in_array($filterData['active'], [0, 1]));
        $templateParameters['sort'] = $sortableColumns->generateSortableColumns();
        $templateParameters['filterForm'] = $filterForm->createView();

        return $templateParameters;
    }
}
