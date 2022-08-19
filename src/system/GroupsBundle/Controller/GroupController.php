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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\GroupsBundle\Constant;
use Zikula\GroupsBundle\Constant as GroupsConstant;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\GroupsBundle\Event\GroupPostCreatedEvent;
use Zikula\GroupsBundle\Event\GroupPostDeletedEvent;
use Zikula\GroupsBundle\Event\GroupPostUpdatedEvent;
use Zikula\GroupsBundle\Event\GroupPreDeletedEvent;
use Zikula\GroupsBundle\Form\Type\CreateGroupType;
use Zikula\GroupsBundle\Form\Type\EditGroupType;
use Zikula\GroupsBundle\Helper\CommonHelper;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

#[Route('/groups')]
class GroupController extends AbstractController
{
    /**
     * @PermissionCheck("overview")
     * @Template("@ZikulaGroups/Group/list.html.twig")
     *
     * View a list of all groups (user view).
     */
    #[Route('/list/{page}', name: 'zikulagroupsbundle_group_listgroups', methods: ['GET'], requirements: ['page' => '\d+'])]
    public function listGroups(GroupRepositoryInterface $groupRepository, int $page = 1): array
    {
        $pageSize = $this->getVar('itemsperpage', 25);
        $groupsCommon = new CommonHelper($this->getTranslator());

        $excludedGroups = [CommonHelper::GTYPE_CORE];
        if ($this->getVar('hidePrivate')) {
            $excludedGroups[] = CommonHelper::GTYPE_PRIVATE;
        }

        $paginator = $groupRepository->getGroups([], ['gtype' => $excludedGroups], [], $page, $pageSize);
        $paginator->setRoute('zikulagroupsbundle_group_listgroups');

        return [
            'paginator' => $paginator,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'defaultGroup' => $this->getVar('defaultgroup')
        ];
    }

    /**
     * @PermissionCheck("edit")
     * @Theme("admin")
     * @Template("@ZikulaGroups/Group/adminList.html.twig")
     *
     * View a list of all groups (admin view).
     */
    #[Route('/admin/list/{page}', name: 'zikulagroupsbundle_group_adminlist', methods: ['GET'], requirements: ['page' => '\d+'])]
    public function adminList(GroupRepositoryInterface $groupRepository, int $page = 1): array
    {
        $pageSize = $this->getVar('itemsperpage', 25);
        $groupsCommon = new CommonHelper($this->getTranslator());

        $paginator = $groupRepository->getGroups([], [], [], $page, $pageSize);
        $paginator->setRoute('zikulagroupsbundle_group_adminlist');

        return [
            'paginator' => $paginator,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'defaultGroup' => $this->getVar('defaultgroup', GroupsConstant::GROUP_ID_USERS)
        ];
    }

    /**
     * @PermissionCheck("add")
     * @Theme("admin")
     * @Template("@ZikulaGroups/Group/create.html.twig")
     *
     * Display a form to add a new group.
     */
    #[Route('/admin/create', name: 'zikulagroupsbundle_group_create')]
    public function create(
        Request $request,
        ManagerRegistry $doctrine,
        EventDispatcherInterface $eventDispatcher
    ) {
        $form = $this->createForm(CreateGroupType::class, new GroupEntity());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $doctrine->getManager()->persist($groupEntity);
                $doctrine->getManager()->flush();
                $eventDispatcher->dispatch(new GroupPostCreatedEvent($groupEntity));
                $this->addFlash('status', 'Done! Created the group.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @PermissionCheck({"$_zkModule::", "$gid::", "edit"})
     * @Theme("admin")
     * @Template("@ZikulaGroups/Group/edit.html.twig")
     *
     * Modify a group.
     */
    #[Route('/admin/edit/{gid}', name: 'zikulagroupsbundle_group_edit', requirements: ['gid' => "^[1-9]\d*$"])]
    public function edit(
        Request $request,
        ManagerRegistry $doctrine,
        GroupEntity $groupEntity,
        EventDispatcherInterface $eventDispatcher
    ) {
        $form = $this->createForm(EditGroupType::class, $groupEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $doctrine->getManager()->persist($groupEntity); // this isn't technically required
                $doctrine->getManager()->flush();
                $eventDispatcher->dispatch(new GroupPostUpdatedEvent($groupEntity));
                $this->addFlash('status', 'Done! Updated the group.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @PermissionCheck({"$_zkModule::", "$gid::", "delete"})
     * @Theme("admin")
     * @Template("@ZikulaGroups/Group/remove.html.twig")
     *
     * Deletes a group.
     */
    #[Route('/admin/remove/{gid}', name: 'zikulagroupsbundle_group_remove', requirements: ['gid' => "\d+"])]
    public function remove(
        Request $request,
        ManagerRegistry $doctrine,
        GroupEntity $groupEntity,
        EventDispatcherInterface $eventDispatcher
    ) {
        // get the user default group - we do not allow its deletion
        $defaultGroup = $this->getVar('defaultgroup', 1);
        if ($groupEntity->getGid() === $defaultGroup) {
            $this->addFlash('error', 'Error! You cannot delete the default user group.');

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        // get the primary admin group - we do not allow its deletion
        if (Constant::GROUP_ID_ADMIN === $groupEntity->getGid()) {
            $this->addFlash('error', 'Error! You cannot delete the primary administration group.');

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        $form = $this->createForm(DeletionType::class, $groupEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $groupEntity = $form->getData();
                $eventDispatcher->dispatch(new GroupPreDeletedEvent($groupEntity));
                $doctrine->getManager()->remove($groupEntity);
                $doctrine->getManager()->flush();
                $eventDispatcher->dispatch(new GroupPostDeletedEvent($groupEntity));
                $this->addFlash('status', 'Done! Group deleted.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        return [
            'form' => $form->createView(),
            'group' => $groupEntity
        ];
    }
}
