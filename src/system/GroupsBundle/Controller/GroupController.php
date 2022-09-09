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

namespace Zikula\GroupsBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\GroupsBundle\Entity\Group;
use Zikula\GroupsBundle\Event\GroupPostCreatedEvent;
use Zikula\GroupsBundle\Event\GroupPostDeletedEvent;
use Zikula\GroupsBundle\Event\GroupPostUpdatedEvent;
use Zikula\GroupsBundle\Event\GroupPreDeletedEvent;
use Zikula\GroupsBundle\Form\Type\CreateGroupType;
use Zikula\GroupsBundle\Form\Type\EditGroupType;
use Zikula\GroupsBundle\GroupsConstant;
use Zikula\GroupsBundle\Helper\TranslationHelper;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;

#[Route('/groups')]
class GroupController extends AbstractController
{
    public function __construct(
        private readonly TranslationHelper $translationHelper,
        private readonly int $groupsPerPage,
        private readonly int $defaultGroupId,
        private readonly bool $hideClosedGroups,
        private readonly bool $hidePrivateGroups
    ) {
    }

    /**
     * View a list of all groups (user view).
     */
    #[Route('/list/{page}', name: 'zikulagroupsbundle_group_listgroups', methods: ['GET'], requirements: ['page' => '\d+'])]
    #[PermissionCheck('overview')]
    public function listGroups(GroupRepositoryInterface $groupRepository, int $page = 1): Response
    {
        $excludedStates = [];
        if ($this->hideClosedGroups) {
            $excludedStates[] = GroupsConstant::STATE_CLOSED;
        }
        $excludedGroupTypes = [GroupsConstant::GTYPE_CORE];
        if ($this->hidePrivateGroups) {
            $excludedGroupTypes[] = GroupsConstant::GTYPE_PRIVATE;
        }
        $exclusions = ['gtype' => $excludedGroupTypes, 'state' => $excludedStates];

        $paginator = $groupRepository->getGroups([], $exclusions, [], $page, $this->groupsPerPage);
        $paginator->setRoute('zikulagroupsbundle_group_listgroups');

        return $this->render('@ZikulaGroups/Group/list.html.twig', [
            'paginator' => $paginator,
            'groupTypes' => $this->translationHelper->gtypeLabels(),
            'states' => $this->translationHelper->stateLabels(),
        ]);
    }

    /**
     * View a list of all groups (admin view).
     */
    #[Route('/admin/list/{page}', name: 'zikulagroupsbundle_group_adminlist', methods: ['GET'], requirements: ['page' => '\d+'])]
    #[PermissionCheck('edit')]
    public function adminList(GroupRepositoryInterface $groupRepository, int $page = 1): Response
    {
        $paginator = $groupRepository->getGroups([], [], [], $page, $this->groupsPerPage);
        $paginator->setRoute('zikulagroupsbundle_group_adminlist');

        return $this->render('@ZikulaGroups/Group/adminList.html.twig', [
            'paginator' => $paginator,
            'groupTypes' => $this->translationHelper->gtypeLabels(),
            'states' => $this->translationHelper->stateLabels(),
            'defaultGroupId' => $this->defaultGroupId,
        ]);
    }

    /**
     * Display a form to add a new group.
     */
    #[Route('/admin/create', name: 'zikulagroupsbundle_group_create')]
    #[PermissionCheck('add')]
    public function create(
        Request $request,
        ManagerRegistry $doctrine,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $form = $this->createForm(CreateGroupType::class, new Group());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $group = $form->getData();
                $doctrine->getManager()->persist($group);
                $doctrine->getManager()->flush();
                $eventDispatcher->dispatch(new GroupPostCreatedEvent($group));
                $this->addFlash('status', 'Done! Created the group.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        return $this->render('@ZikulaGroups/Group/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modify a group.
     */
    #[Route('/admin/edit/{gid}', name: 'zikulagroupsbundle_group_edit', requirements: ['gid' => "^[1-9]\d*$"])]
    #[PermissionCheck(['$_zkModule::', '$gid::', 'edit'])]
    public function edit(
        Request $request,
        ManagerRegistry $doctrine,
        Group $group,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $form = $this->createForm(EditGroupType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $group = $form->getData();
                $doctrine->getManager()->persist($group); // this isn't technically required
                $doctrine->getManager()->flush();
                $eventDispatcher->dispatch(new GroupPostUpdatedEvent($group));
                $this->addFlash('status', 'Done! Updated the group.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        return $this->render('@ZikulaGroups/Group/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a group.
     */
    #[Route('/admin/remove/{gid}', name: 'zikulagroupsbundle_group_remove', requirements: ['gid' => "\d+"])]
    #[PermissionCheck(['$_zkModule::', '$gid::', 'delete'])]
    public function remove(
        Request $request,
        ManagerRegistry $doctrine,
        Group $group,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        // get the user default group - we do not allow its deletion
        $defaultGroupId = $this->defaultGroupId;
        if ($group->getGid() === $defaultGroupId) {
            $this->addFlash('error', 'Error! You cannot delete the default user group.');

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        // get the primary admin group - we do not allow its deletion
        if (GroupsConstant::GROUP_ID_ADMIN === $group->getGid()) {
            $this->addFlash('error', 'Error! You cannot delete the primary administration group.');

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        $form = $this->createForm(DeletionType::class, $group);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $group = $form->getData();
                $eventDispatcher->dispatch(new GroupPreDeletedEvent($group));
                $doctrine->getManager()->remove($group);
                $doctrine->getManager()->flush();
                $eventDispatcher->dispatch(new GroupPostDeletedEvent($group));
                $this->addFlash('status', 'Done! Group deleted.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        return $this->render('@ZikulaGroups/Group/remove.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
        ]);
    }
}