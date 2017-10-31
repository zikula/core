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

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Bundle\FormExtensionBundle\Form\Type\DeletionType;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\GroupsModule\Constant;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Form\Type\CreateGroupType;
use Zikula\GroupsModule\Form\Type\EditGroupType;
use Zikula\GroupsModule\GroupEvents;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class GroupController extends AbstractController
{
    /**
     * @Route("/list/{startnum}", requirements={"startnum" = "\d+"})
     * @Method("GET")
     * @Template("ZikulaGroupsModule:Group:list.html.twig")
     *
     * View a list of all groups (user view)
     *
     * @param integer $startnum
     * @return array
     * @throws AccessDeniedException Thrown if the user hasn't permissions to view any groups
     */
    public function listAction($startnum = 0)
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
            ->where(Criteria::expr()->notIn("gtype", $excludedGroups))
            ->setMaxResults($itemsPerPage)
            ->setFirstResult($startnum);
        $groups = $this->get('doctrine')->getManager()->getRepository('ZikulaGroupsModule:GroupEntity')->matching($criteria);

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
     * @Route("/admin/list/{startnum}", requirements={"startnum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template("ZikulaGroupsModule:Group:adminList.html.twig")
     *
     * View a list of all groups (admin view)
     *
     * @param integer $startnum
     * @return array
     * @throws AccessDeniedException Thrown if the user hasn't permissions to administer any groups
     */
    public function adminListAction($startnum = 0)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $itemsPerPage = $this->getVar('itemsperpage', 25);
        $groupsCommon = new CommonHelper($this->getTranslator());
        $groups = $this->get('doctrine')->getManager()->getRepository('ZikulaGroupsModule:GroupEntity')->findBy([], [], $itemsPerPage, $startnum);

        return [
            'groups' => $groups,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'applications' => $this->get('zikula_groups_module.group_application_repository')->findAll(),
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
     * @Template("ZikulaGroupsModule:Group:create.html.twig")
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

        $form = $this->createForm(CreateGroupType::class, new GroupEntity(), [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->get('doctrine')->getManager()->persist($groupEntity);
                $this->get('doctrine')->getManager()->flush();
                $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_CREATE, new GenericEvent($groupEntity));
                $this->addFlash('status', $this->__('Done! Created the group.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
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
     * @Template("ZikulaGroupsModule:Group:edit.html.twig")
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

        $form = $this->createForm(EditGroupType::class, $groupEntity, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $groupEntity = $form->getData();
                $this->get('doctrine')->getManager()->persist($groupEntity); // this isn't technically required
                $this->get('doctrine')->getManager()->flush();
                $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_UPDATE, new GenericEvent($groupEntity));
                $this->addFlash('status', $this->__('Done! Updated the group.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
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
     * @Template("ZikulaGroupsModule:Group:remove.html.twig")
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

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        // get the primary admin group - we do not allow its deletion
        if (Constant::GROUP_ID_ADMIN == $groupEntity->getGid()) {
            $this->addFlash('error', $this->__('Error! You cannot delete the primary administration group.'));

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        $form = $this->createForm(DeletionType::class, $groupEntity);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $groupEntity = $form->getData();
                $this->get('doctrine')->getManager()->remove($groupEntity);
                $this->get('doctrine')->getManager()->flush();
                $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_DELETE, new GenericEvent($groupEntity));
                $this->addFlash('status', $this->__('Done! Group deleted.'));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView(),
            'group' => $groupEntity
        ];
    }
}
