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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\Repository\GroupApplicationRepository;
use Zikula\GroupsModule\Event\GroupApplicationPostCreatedEvent;
use Zikula\GroupsModule\Event\GroupApplicationPostProcessedEvent;
use Zikula\GroupsModule\Event\GroupPostUserAddedEvent;
use Zikula\GroupsModule\Form\Type\ManageApplicationType;
use Zikula\GroupsModule\Form\Type\MembershipApplicationType;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
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
     * @PermissionCheck("edit")
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Application/admin.html.twig")
     *
     * Display a list of group applications.
     *
     * @return array|RedirectResponse
     */
    public function adminAction(
        Request $request,
        string $action,
        GroupApplicationEntity $groupApplicationEntity,
        EventDispatcherInterface $eventDispatcher
    ) {
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
                    $this->getDoctrine()->getManager()->flush();
                    $eventDispatcher->dispatch(new GroupPostUserAddedEvent($groupApplicationEntity->getGroup(), $groupApplicationEntity->getUser()));
                }
                $eventDispatcher->dispatch(new GroupApplicationPostProcessedEvent($groupApplicationEntity, $formData['reason']));
                $this->addFlash(
                    'success',
                    $this->trans(
                        'Application processed (%action% %user%)',
                        ['%action%' => $action, '%user%' => $groupApplicationEntity->getUser()->getUname()]
                    )
                );
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('success', 'Operation cancelled.');
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
     * @PermissionCheck("overview")
     * @Template("@ZikulaGroupsModule/Application/create.html.twig")
     *
     * Create an application to a group.
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    public function createAction(
        Request $request,
        GroupEntity $group,
        EventDispatcherInterface $eventDispatcher,
        GroupApplicationRepository $applicationRepository,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ) {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->trans('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $userRepository->find($currentUserApi->get('uid'));
        $groupTypeIsCore = CommonHelper::GTYPE_CORE === $group->getGtype();
        $groupStateIsClosed = CommonHelper::STATE_CLOSED === $group->getState();
        $groupCountIsLimit = 0 < $group->getNbumax() && $group->getUsers()->count() > $group->getNbumax();
        $alreadyGroupMember = $group->getUsers()->contains($userEntity);
        if ($groupTypeIsCore || $groupStateIsClosed || $groupCountIsLimit || $alreadyGroupMember) {
            $this->addFlash(
                'error',
                $this->getSpecificGroupMessage($groupTypeIsCore, $groupStateIsClosed, $groupCountIsLimit, $alreadyGroupMember)
            );

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }
        $existingApplication = $applicationRepository->findOneBy(['group' => $group, 'user' => $userEntity]);
        if ($existingApplication) {
            $this->addFlash('info', 'You already have a pending application. Please wait until the administrator notifies you.');

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
                $eventDispatcher->dispatch(new GroupApplicationPostCreatedEvent($groupApplicationEntity));
                $this->addFlash('status', 'Done! The application has been sent. You will be notified by email when the application is processed.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Application cancelled.');
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
        $messages[] = $this->trans('Error! You cannot apply to join the requested group');
        if ($groupTypeIsCore) {
            $messages[] = $this->trans('This group is a core-only group');
        }
        if ($groupStateIsClosed) {
            $messages[] = $this->trans('This group is closed.');
        }
        if ($groupCountIsLimit) {
            $messages[] = $this->trans('This group is has reached its membership limit.');
        }
        if ($alreadyGroupMember) {
            $messages[] = $this->trans('You are already a member of this group.');
        }

        return implode('<br />', $messages);
    }
}
