<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the groups module
 */
class GroupController extends AbstractController
{
    /**
     * @Route("/list/{startnum}", requirements={"startnum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * View a list of all groups
     *
     * @param integer $startnum
     * @return array
     * @throws AccessDeniedException Thrown if the user hasn't permissions to administer any groups
     */
    public function listAction($startnum = 0)
    {
        $itemsPerPage = $this->getVar('itemsperpage', 25);

        // get the default user group
        $defaultGroup = $this->getVar('defaultgroup');

        // get the primary admin group
        $primaryAdminGroup = $this->getVar('primaryadmingroup', 2);

        $items = $this->get('doctrine')->getManager()->getRepository('ZikulaGroupsModule:GroupEntity')->findBy([], [], $itemsPerPage, $startnum);

        // Setting various defines
        $groupsCommon = new CommonHelper();
        $typeLabels = $groupsCommon->gtypeLabels();
        $stateLabels = $groupsCommon->stateLabels();

        $groups = [];
        $router = $this->get('router');

        /** @var GroupEntity $item */
        foreach ($items as $item) {
            if (!$this->hasPermission('ZikulaGroupsModule::', $item->getGid() . '::', ACCESS_EDIT)) {
                continue;
            }

            // Options for the item.
            $options = [];

            $routeArgs = ['gid' => $item->getGid()];
            $editUrl = $router->generate('zikulagroupsmodule_group_edit', $routeArgs);
            $membersUrl = $router->generate('zikulagroupsmodule_membershipadministration_list', $routeArgs);

            $options[] = [
                'url' => $router->generate('zikulagroupsmodule_group_edit', $routeArgs),
                'title'   => $this->__('Edit'),
                'icon' => 'pencil'
            ];

            if ($this->hasPermission('ZikulaGroupsModule::', $item->getGid() . '::', ACCESS_DELETE)
                    && $item->getGid() != $defaultGroup && $item->getGid() != $primaryAdminGroup) {
                $options[] = [
                    'url' => $router->generate('zikulagroupsmodule_group_remove', $routeArgs),
                    'title'   => $this->__('Delete'),
                    'icon' => 'trash-o'
                ];
            }

            $options[] = [
                'url' => $router->generate('zikulagroupsmodule_membershipadministration_list', $routeArgs),
                'title'   => $this->__('Group membership'),
                'icon' => 'users'
            ];

            $groups[] = [
                'name'        => $item->getName(),
                'gid'         => $item->getGid(),
                'gtype'       => $item->getGtype(),
                'gtypelbl'    => $this->__(/** @Ignore */$typeLabels[$item->getGtype()]),
                'description' => $item->getDescription(),
                'prefix'      => $item->getPrefix(),
                'state'       => $item->getState(),
                'statelbl'    => $this->__(/** @Ignore */$stateLabels[$item->getState()]),
                'nbuser'      => $item->getUsers()->count(),
                'nbumax'      => $item->getNbumax(),
                'link'        => $item->getLink(),
                'uidmaster'   => $item->getUidmaster(),
                'options'     => $options,
                'editurl'     => $editUrl,
                'membersurl'  => $membersUrl
            ];
        }

        if (count($groups) == 0) {
            // groups array is empty
            throw new AccessDeniedException();
        }

        $users = $this->get('zikula_groups_module.group_application_repository')->getFilteredApplications();

        return [
            'groups' => $groups,
            //'groupTypes' => $typeLabels,
            //'states' => $stateLabels,
            'userItems' => $users,
            'defaultGroup' => $defaultGroup,
            'primaryAdminGroup' => $primaryAdminGroup,
            'pager' => [
                'amountOfItems' => count($groups),
                'itemsPerPage' => $itemsPerPage
            ]
        ];
    }

    /**
     * @Route("/create")
     * @Theme("admin")
     * @Template
     *
     * Display a form to add a new group.
     *
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\CreateGroupType', new GroupEntity(), [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->get('doctrine')->getManager()->persist($groupEntity);
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done! Created the group.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * Modify a group.
     *
     * @param Request $request
     * @param GroupEntity $groupEntity
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, GroupEntity $groupEntity)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', $groupEntity->getGid() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\EditGroupType', $groupEntity, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->get('doctrine')->getManager()->persist($groupEntity); // this isn't technically required
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done! Updated the group.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/remove/{gid}", requirements={"gid"="\d+"})
     * @Theme("admin")
     * @Template
     *
     * Deletes a group.
     *
     * @param Request $request
     * @param GroupEntity $groupEntity
     * @return array|RedirectResponse
     */
    public function removeAction(Request $request, GroupEntity $groupEntity)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', $groupEntity->getGid() . '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // get the user default group - we do not allow its deletion
        $defaultGroup = $this->getVar('defaultgroup', 1);
        if ($groupEntity->getGid() == $defaultGroup) {
            $this->addFlash('error', $this->__('Error! You cannot delete the default user group.'));

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        // get the primary admin group - we do not allow its deletion
        $primaryAdminGroup = $this->getVar('primaryadmingroup', 2);
        if ($groupEntity->getGid() == $primaryAdminGroup) {
            $this->addFlash('error', $this->__('Error! You cannot delete the primary administration group.'));

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\DeleteGroupType', $groupEntity, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $groupEntity = $form->getData();
                $this->get('doctrine')->getManager()->remove($groupEntity);
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done! Group deleted.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        return [
            'form' => $form->createView(),
            'group' => $groupEntity
        ];
    }
}
