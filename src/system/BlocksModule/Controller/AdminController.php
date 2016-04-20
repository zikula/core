<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class AdminController
 * @package Zikula\BlocksModule\Controller
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     * @deprecated remove at Core-2.0
     */
    public function indexAction()
    {
        @trigger_error('The zikulablocksmodule_admin_index route is deprecated. please use zikulablocksmodule_admin_view instead.', E_USER_DEPRECATED);

        return $this->redirect($this->generateUrl('zikulablocksmodule_admin_view'));
    }

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
        $filterForm = $this->createForm('Zikula\BlocksModule\Form\Type\AdminViewFilterType', $sessionFilterData, [
            'action' => $this->generateUrl('zikulablocksmodule_admin_view'),
            'method' => 'POST',
            'translator' => $this->get('translator'),
            'moduleChoices' => $this->get('zikula_blocks_module.api.block')->getModulesContainingBlocks(),
            'positionChoices' => $this->getDoctrine()->getRepository('ZikulaBlocksModule:BlockPositionEntity')->getPositionChoiceArray()
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
        $sortableColumns->setAdditionalUrlParameters(array(
            'position' => isset($filterData['position']) ? $filterData['position'] : null,
            'module' => isset($filterData['module']) ? $filterData['module'] : null,
            'language' => isset($filterData['language']) ? $filterData['language'] : null,
            'status' => isset($filterData['status']) ? $filterData['status'] : null,
        ));

        $templateParameters = [];
        $templateParameters['blocks'] = $this->getDoctrine()->getManager()->getRepository('ZikulaBlocksModule:BlockEntity')->getFilteredBlocks($filterData);
        $templateParameters['positions'] = $this->getDoctrine()->getManager()->getRepository('ZikulaBlocksModule:BlockPositionEntity')->findAll();
        $templateParameters['filter_active'] = !empty($filterData['position']) || !empty($filterData['module']) || !empty($filterData['language']) || (!empty($filterData['active']) && in_array($filterData['active'], [0, 1]));
        $templateParameters['sort'] = $sortableColumns->generateSortableColumns();
        $templateParameters['filterForm'] = $filterForm->createView();

        return $templateParameters;
    }

    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaBlocksModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder($this->getVars())
            ->add('collapseable', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('Enable block collapse icons'),
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirect($this->generateUrl('zikulablocksmodule_admin_view'));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
