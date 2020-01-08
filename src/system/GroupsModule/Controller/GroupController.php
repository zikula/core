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

use Doctrine\Common\Collections\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\GroupsModule\Constant;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\GroupsModule\Form\Type\CreateGroupType;
use Zikula\GroupsModule\Form\Type\EditGroupType;
use Zikula\GroupsModule\GroupEvents;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class GroupController extends AbstractController
{
    /**
     * @Route("/list/{startnum}", methods = {"GET"}, requirements={"startnum" = "\d+"})
     * @Template("@ZikulaGroupsModule/Group/list.html.twig")
     *
     * View a list of all groups (user view).
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to view any groups
     */
    public function listAction(GroupRepositoryInterface $groupRepository, int $startnum = 0): array
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $itemsPerPage = $this->getVar('itemsperpage', 25);
        $groupsCommon = new CommonHelper($this->getTranslator());
        $excludedGroups = [CommonHelper::GTYPE_CORE];
        if ($this->getVar('hidePrivate')) {
            $excludedGroups[] = CommonHelper::GTYPE_PRIVATE;
        }
        $criteria = Criteria::create()
            ->where(Criteria::expr()->notIn('gtype', $excludedGroups))
            ->setMaxResults($itemsPerPage)
            ->setFirstResult($startnum);
        $groups = $groupRepository->matching($criteria);

        return [
            'groups' => $groups,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'defaultGroup' => $this->getVar('defaultgroup'),
            'pager' => [
                'amountOfItems' => count($groups),
                'itemsPerPage' => $itemsPerPage
            ]
        ];
    }

    /**
     * @Route("/admin/list/{startnum}", methods = {"GET"}, requirements={"startnum" = "\d+"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/adminList.html.twig")
     *
     * View a list of all groups (admin view).
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to edit any groups
     */
    public function adminListAction(
        GroupRepositoryInterface $groupRepository,
        GroupApplicationRepository $applicationRepository,
        int $startnum = 0
    ): array {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $itemsPerPage = $this->getVar('itemsperpage', 25);
        $groupsCommon = new CommonHelper($this->getTranslator());
        $groups = $groupRepository->findBy([], [], $itemsPerPage, $startnum);

        return [
            'groups' => $groups,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'applications' => $applicationRepository->findAll(),
            'defaultGroup' => $this->getVar('defaultgroup', GroupsConstant::GROUP_ID_USERS),
            'pager' => [
                'amountOfItems' => count($groups),
                'itemsPerPage' => $itemsPerPage
            ]
        ];
    }

    /**
     * @Route("/admin/create")
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/create.html.twig")
     *
     * Display a form to add a new group.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't permissions to add any groups
     */
    public function createAction(
        Request $request,
        EventDispatcherInterface $eventDispatcher
    ) {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(CreateGroupType::class, new GroupEntity());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->getDoctrine()->getManager()->persist($groupEntity);
                $this->getDoctrine()->getManager()->flush();
                $eventDispatcher->dispatch(new GenericEvent($groupEntity), GroupEvents::GROUP_CREATE);
                $this->addFlash('status', $this->trans('Done! Created the group.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/admin/edit/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/edit.html.twig")
     *
     * Modify a group.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't permissions to edit any groups
     */
    public function editAction(
        Request $request,
        GroupEntity $groupEntity,
        EventDispatcherInterface $eventDispatcher
    ) {
        if (!$this->hasPermission('ZikulaGroupsModule::', $groupEntity->getGid() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(EditGroupType::class, $groupEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->getDoctrine()->getManager()->persist($groupEntity); // this isn't technically required
                $this->getDoctrine()->getManager()->flush();
                $eventDispatcher->dispatch(new GenericEvent($groupEntity), GroupEvents::GROUP_UPDATE);
                $this->addFlash('status', $this->trans('Done! Updated the group.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/admin/remove/{gid}", requirements={"gid"="\d+"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Group/remove.html.twig")
     *
     * Deletes a group.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't permissions to delete any groups
     */
    public function removeAction(
        Request $request,
        GroupEntity $groupEntity,
        EventDispatcherInterface $eventDispatcher
    ) {
        if (!$this->hasPermission('ZikulaGroupsModule::', $groupEntity->getGid() . '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // get the user default group - we do not allow its deletion
        $defaultGroup = $this->getVar('defaultgroup', 1);
        if ($groupEntity->getGid() === $defaultGroup) {
            $this->addFlash('error', $this->trans('Error! You cannot delete the default user group.'));

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        // get the primary admin group - we do not allow its deletion
        if (Constant::GROUP_ID_ADMIN === $groupEntity->getGid()) {
            $this->addFlash('error', $this->trans('Error! You cannot delete the primary administration group.'));

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
                $this->addFlash('status', $this->trans('Done! Group deleted.'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView(),
            'group' => $groupEntity
        ];
    }
}
