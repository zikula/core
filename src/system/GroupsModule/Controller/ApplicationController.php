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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository;
use Zikula\GroupsModule\Form\Type\ManageApplicationType;
use Zikula\GroupsModule\Form\Type\MembershipApplicationType;
use Zikula\GroupsModule\GroupEvents;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * @Route("/application")
 */
class ApplicationController extends AbstractController
{
    /**
     * @Route("/admin/{action}/{app_id}", requirements={"action" = "deny|accept", "app_id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Application/admin.html.twig")
     *
     * Display a list of group applications.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't permissions to edit any groups
     */
    public function adminAction(Request $request, string $action, GroupApplicationEntity $groupApplicationEntity)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $formValues = [
            'theAction' => $action,
            'application' => $groupApplicationEntity,
        ];
        $form = $this->createForm(ManageApplicationType::class, $formValues);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                /** @var GroupApplicationEntity $groupApplicationEntity */
                $groupApplicationEntity = $formData['application'];
                $this->getDoctrine()->getManager()->remove($groupApplicationEntity);
                if ('accept' === $action) {
                    $groupApplicationEntity->getUser()->addGroup($groupApplicationEntity->getGroup());
                    $addUserEvent = new GenericEvent(['gid' => $groupApplicationEntity->getGroup()->getGid(), 'uid' => $groupApplicationEntity->getUser()->getUid()]);
                    $this->get('event_dispatcher')->dispatch($addUserEvent, GroupEvents::GROUP_ADD_USER);
                }
                $this->getDoctrine()->getManager()->flush();
                $applicationProcessedEvent = new GenericEvent($groupApplicationEntity, $formData);
                $this->get('event_dispatcher')->dispatch($applicationProcessedEvent, GroupEvents::GROUP_APPLICATION_PROCESSED);
                $this->addFlash('success', $this->__f('Application processed (%action %user)', ['%action' => $action, '%user' => $groupApplicationEntity->getUser()->getUname()]));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('success', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView(),
            'action' => $action,
            'application' => $groupApplicationEntity
        ];
    }

    /**
     * @Route("/create/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @Template("@ZikulaGroupsModule/Application/create.html.twig")
     *
     * Create an application to a group.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't permissions to view any groups
     */
    public function createAction(
        Request $request,
        GroupEntity $group,
        GroupApplicationRepository $applicationRepository,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ) {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $userRepository->find($currentUserApi->get('uid'));
        $groupTypeIsCore = CommonHelper::GTYPE_CORE === $group->getGtype();
        $groupStateIsClosed = CommonHelper::STATE_CLOSED === $group->getState();
        $groupCountIsLimit = 0 < $group->getNbumax() && $group->getUsers()->count() > $group->getNbumax();
        $alreadyGroupMember = $group->getUsers()->contains($userEntity);
        if ($groupTypeIsCore || $groupStateIsClosed || $groupCountIsLimit || $alreadyGroupMember) {
            $this->addFlash('error', $this->getSpecificGroupMessage($groupTypeIsCore, $groupStateIsClosed, $groupCountIsLimit, $alreadyGroupMember));

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }
        $existingApplication = $applicationRepository->findOneBy(['group' => $group, 'user' => $userEntity]);
        if ($existingApplication) {
            $this->addFlash('info', $this->__('You already have a pending application. Please wait until the administrator notifies you.'));

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        $groupApplicationEntity = new GroupApplicationEntity();
        $groupApplicationEntity->setGroup($group);
        $groupApplicationEntity->setUser($userEntity);
        $form = $this->createForm(MembershipApplicationType::class, $groupApplicationEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('apply')->isClicked()) {
                $groupApplicationEntity = $form->getData();
                $this->getDoctrine()->getManager()->persist($groupApplicationEntity);
                $this->getDoctrine()->getManager()->flush();
                $newApplicationEvent = new GenericEvent($groupApplicationEntity);
                $this->get('event_dispatcher')->dispatch($newApplicationEvent, GroupEvents::GROUP_NEW_APPLICATION);
                $this->addFlash('status', $this->__('Done! The application has been sent. You will be notified by email when the application is processed.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Application cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        return [
            'form' => $form->createView(),
            'group' => $group
        ];
    }

    private function getSpecificGroupMessage(
        bool $groupTypeIsCore,
        bool $groupStateIsClosed,
        bool $groupCountIsLimit,
        bool $alreadyGroupMember
    ) {
        $messages = [];
        $messages[] = $this->__('Sorry!, You cannot apply to join the requested group');
        if ($groupTypeIsCore) {
            $messages[] = $this->__('This group is a core-only group');
        }
        if ($groupStateIsClosed) {
            $messages[] = $this->__('This group is closed.');
        }
        if ($groupCountIsLimit) {
            $messages[] = $this->__('This group is has reached its membership limit.');
        }
        if ($alreadyGroupMember) {
            $messages[] = $this->__('You are already a member of this group.');
        }

        return implode('<br />', $messages);
    }
}
