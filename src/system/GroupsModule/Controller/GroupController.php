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

namespace Zikula\GroupsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\GroupsModule\Constant;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\GroupsModule\Form\Type\CreateGroupType;
use Zikula\GroupsModule\Form\Type\EditGroupType;
use Zikula\GroupsModule\GroupEvents;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class GroupController extends AbstractController
{
    /**
     * @Route("/list/{page}", methods = {"GET"}, requirements={"page" = "\d+"})
     * @PermissionCheck("overview")
     * @Template("@ZikulaGroupsModule/Group/list.html.twig")
     *
     * View a list of all groups (user view).
     */
    public function listAction(GroupRepositoryInterface $groupRepository, int $page = 1): array
    {
        $pageSize = $this->getVar('itemsperpage', 25);
        $groupsCommon = new CommonHelper($this->getTranslator());

        $excludedGroups = [CommonHelper::GTYPE_CORE];
        if ($this->getVar('hidePrivate')) {
            $excludedGroups[] = CommonHelper::GTYPE_PRIVATE;
        }

        $paginator = $groupRepository->getGroups([], ['gtype' => $excludedGroups], [], $page, $pageSize);
        $paginator->setRoute('zikulagroupsmodule_group_list');

        return [
            'paginator' => $paginator,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'defaultGroup' => $this->getVar('defaultgroup')
        ];
    }

    /**
     * @Route("/admin/list/{page}", methods = {"GET"}, requirements={"page" = "\d+"})
     * @PermissionCheck("edit")
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/adminList.html.twig")
     *
     * View a list of all groups (admin view).
     */
    public function adminListAction(
        GroupRepositoryInterface $groupRepository,
        GroupApplicationRepository $applicationRepository,
        int $page = 1
    ): array {
        $pageSize = $this->getVar('itemsperpage', 25);
        $groupsCommon = new CommonHelper($this->getTranslator());

        $paginator = $groupRepository->getGroups([], [], [], $page, $pageSize);
        $paginator->setRoute('zikulagroupsmodule_group_adminlist');

        return [
            'paginator' => $paginator,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'applications' => $applicationRepository->findAll(),
            'defaultGroup' => $this->getVar('defaultgroup', GroupsConstant::GROUP_ID_USERS)
        ];
    }

    /**
     * @Route("/admin/create")
     * @PermissionCheck("add")
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/create.html.twig")
     *
     * Display a form to add a new group.
     */
    public function createAction(
        Request $request,
        EventDispatcherInterface $eventDispatcher
    ) {
        $form = $this->createForm(CreateGroupType::class, new GroupEntity());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->getDoctrine()->getManager()->persist($groupEntity);
                $this->getDoctrine()->getManager()->flush();
                $eventDispatcher->dispatch(new GenericEvent($groupEntity), GroupEvents::GROUP_CREATE);
                $this->addFlash('status', 'Done! Created the group.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/admin/edit/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @PermissionCheck({"$_zkModule::", "$gid::", "edit"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/edit.html.twig")
     *
     * Modify a group.
     */
    public function editAction(
        Request $request,
        GroupEntity $groupEntity,
        EventDispatcherInterface $eventDispatcher
    ) {
        $form = $this->createForm(EditGroupType::class, $groupEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->getDoctrine()->getManager()->persist($groupEntity); // this isn't technically required
                $this->getDoctrine()->getManager()->flush();
                $eventDispatcher->dispatch(new GenericEvent($groupEntity), GroupEvents::GROUP_UPDATE);
                $this->addFlash('status', 'Done! Updated the group.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/admin/remove/{gid}", requirements={"gid"="\d+"})
     * @PermissionCheck({"$_zkModule::", "$gid::", "delete"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/remove.html.twig")
     *
     * Deletes a group.
     */
    public function removeAction(
        Request $request,
        GroupEntity $groupEntity,
        EventDispatcherInterface $eventDispatcher
    ) {
        // get the user default group - we do not allow its deletion
        $defaultGroup = $this->getVar('defaultgroup', 1);
        if ($groupEntity->getGid() === $defaultGroup) {
            $this->addFlash('error', 'Error! You cannot delete the default user group.');

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        // get the primary admin group - we do not allow its deletion
        if (Constant::GROUP_ID_ADMIN === $groupEntity->getGid()) {
            $this->addFlash('error', 'Error! You cannot delete the primary administration group.');

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        $form = $this->createForm(DeletionType::class, $groupEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $groupEntity = $form->getData();
                $eventDispatcher->dispatch(new GenericEvent($groupEntity), GroupEvents::GROUP_PRE_DELETE);
                $this->getDoctrine()->getManager()->remove($groupEntity);
                $this->getDoctrine()->getManager()->flush();
                $eventDispatcher->dispatch(new GenericEvent($groupEntity), GroupEvents::GROUP_DELETE);
                $this->addFlash('status', 'Done! Group deleted.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView(),
            'group' => $groupEntity
        ];
    }
}
