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

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ZAuthModule\Form\Type\ImportUserType;
use Zikula\ZAuthModule\Helper\FileIOHelper;

/**
 * @Route("/fileIO")
 * @PermissionCheck("admin")
 */
class FileIOController extends AbstractController
{
    /**
     * @Route("/import")
     * @Theme("admin")
     * @Template("@ZikulaZAuthModule/FileIO/import.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function importAction(
        Request $request,
        VariableApiInterface $variableApi,
        GroupRepositoryInterface $groupRepository,
        FileIOHelper $ioHelper
    ) {
        $form = $this->createForm(ImportUserType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('upload')->isClicked()) {
                $data = $form->getData();
                $importErrors = $ioHelper->importUsersFromFile($data['file'], $data['delimiter']);
                if (empty($importErrors)) {
                    $createdUsers = $ioHelper->getCreatedUsers();
                    $this->addFlash('status', $this->trans('Done! %count% users imported.', ['%count%' => count($createdUsers)]));
                } else {
                    $this->addFlash('error', $importErrors);
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        $defaultGroupId = $variableApi->get('ZikulaGroupsModule', 'defaultgroup');
        $groupEntity = $groupRepository->find($defaultGroupId);

        return [
            'form' => $form->createView(),
            'defaultGroupName' => null !== $groupEntity ? $groupEntity->getName() : ''
        ];
    }
}
