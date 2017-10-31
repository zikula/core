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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\GroupEvents;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/application")
 */
class ApplicationController extends AbstractController
{
    /**
     * @Route("/admin/{action}/{app_id}", requirements={"action" = "deny|accept", "app_id" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * display a list of group applications
     *
     * @param Request $request
     * @param string $action Name of desired action
     * @param GroupApplicationEntity $groupApplicationEntity
     * @return array|RedirectResponse
     */
    public function adminAction(Request $request, $action, GroupApplicationEntity $groupApplicationEntity)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $formValues = [
            'theAction' => $action,
            'application' => $groupApplicationEntity,
        ];
        $form = $this->createForm('Zikula\GroupsModule\Form\Type\ManageApplicationType', $formValues, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                $groupApplicationEntity = $formData['application'];
                $this->get('doctrine')->getManager()->remove($groupApplicationEntity);
                if ('accept' == $action) {
                    $groupApplicationEntity->getUser()->addGroup($groupApplicationEntity->getGroup());
                    $addUserEvent = new GenericEvent(['gid' => $groupApplicationEntity->getGroup()->getGid(), 'uid' => $groupApplicationEntity->getUser()->getUid()]);
                    $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_ADD_USER, $addUserEvent);
                }
                $this->get('doctrine')->getManager()->flush();
                $applicationProcessedEvent = new GenericEvent($groupApplicationEntity, $formData);
                $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_APPLICATION_PROCESSED, $applicationProcessedEvent);
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
            'application' => $groupApplicationEntity,
        ];
    }

    /**
     * @Route("/create/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @Template
     *
     * Create an application to a group
     *
     * @param Request $request
     * @param GroupEntity $group
     * @return array|RedirectResponse
     */
    public function createAction(Request $request, GroupEntity $group)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        $currentUserApi = $this->get('zikula_users_module.current_user');
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }
        $userEntity = $this->get('zikula_users_module.user_repository')->find($currentUserApi->get('uid'));
        if ((CommonHelper::GTYPE_CORE == $group->getGtype())
            || (CommonHelper::STATE_CLOSED == $group->getState())
            || ($group->getNbumax() > 0 && $group->getUsers()->count() > $group->getNbumax())
            || ($group->getUsers()->contains($userEntity))) {
            $this->addFlash('error', $this->__('Sorry!, You cannot apply to join the requested group')); // @todo more specific info would be better

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }
        $existingApplication = $this->get('zikula_groups_module.group_application_repository')->findOneBy(['group' => $group, 'user' => $userEntity]);
        if ($existingApplication) {
            $this->addFlash('info', $this->__('You already have a pending application. Please wait until the administrator notifies you.'));

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        $groupApplicationEntity = new GroupApplicationEntity();
        $groupApplicationEntity->setGroup($group);
        $groupApplicationEntity->setUser($userEntity);
        $form = $this->createForm('Zikula\GroupsModule\Form\Type\MembershipApplicationType', $groupApplicationEntity, [
                'translator' => $this->get('translator.default'),
            ]
        );
        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('apply')->isClicked()) {
                $groupApplicationEntity = $form->getData();
                $this->get('doctrine')->getManager()->persist($groupApplicationEntity);
                $this->get('doctrine')->getManager()->flush();
                $newApplicationEvent = new GenericEvent($groupApplicationEntity);
                $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_NEW_APPLICATION, $newApplicationEvent);
                $this->addFlash('status', $this->__('Done! The application has been sent. You will be notified by email when the application is processed.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Application cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        return [
            'form' => $form->createView(),
            'group' => $group,
        ];
    }
}
