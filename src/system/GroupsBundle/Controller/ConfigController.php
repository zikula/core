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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\GroupsBundle\Form\Type\ConfigType;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

/**
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaGroups/Config/config.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function config(Request $request, GroupRepositoryInterface $groupRepository)
    {
        // build a groups array suitable for the form choices
        $groupsList = [];
        $groups = $groupRepository->findAll();
        foreach ($groups as $group) {
            $groupsList[$group->getName()] = $group->getGid();
        }

        $form = $this->createForm(ConfigType::class, $this->getVars(), [
            'groups' => $groupsList
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', 'Done! Configuration updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_group_adminlist');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
