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

namespace Zikula\ZAuthBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\ZAuthBundle\Form\Type\ImportUserType;
use Zikula\ZAuthBundle\Helper\FileIOHelper;

/**
 * @PermissionCheck("admin")
 */
#[Route('/zauth/fileIO')]
class FileIOController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaZAuth/FileIO/import.html.twig")
     *
     * @return array|RedirectResponse
     */
    #[Route('/import', name: 'zikulazauthbundle_fileio_import')]
    public function import(
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

            $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
        }

        $defaultGroupId = $variableApi->get('ZikulaGroupsModule', 'defaultgroup');
        $groupEntity = $groupRepository->find($defaultGroupId);

        return [
            'form' => $form->createView(),
            'defaultGroupName' => null !== $groupEntity ? $groupEntity->getName() : '',
        ];
    }
}
