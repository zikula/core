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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\GroupsBundle\Helper\DefaultHelper;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\ZAuthBundle\Form\Type\ImportUserType;
use Zikula\ZAuthBundle\Helper\FileIOHelper;

#[Route('/zauth/fileIO')]
#[PermissionCheck('admin')]
class FileIOController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly DefaultHelper $defaultHelper,
        private readonly int $minimumPasswordLength
    ) {
    }

    #[Route('/import', name: 'zikulazauthbundle_fileio_import')]
    #[Theme('admin')]
    public function import(
        Request $request,
        GroupRepositoryInterface $groupRepository,
        FileIOHelper $ioHelper
    ): Response {
        $form = $this->createForm(ImportUserType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('upload')->isClicked()) {
                $data = $form->getData();
                $importErrors = $ioHelper->importUsersFromFile($data['file'], $data['delimiter']);
                if (empty($importErrors)) {
                    $createdUsers = $ioHelper->getCreatedUsers();
                    $this->addFlash('status', $this->translator->trans('Done! %count% users imported.', ['%count%' => count($createdUsers)]));
                } else {
                    $this->addFlash('error', $importErrors);
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            $this->redirectToRoute('zikulazauthbundle_useradministration_listmappings');
        }

        $group = $groupRepository->find($this->defaultHelper->getDefaultGroupId());

        return $this->render('@ZikulaZAuth/FileIO/import.html.twig', [
            'form' => $form->createView(),
            'defaultGroupName' => null !== $group ? $group->getName() : '',
            'minimumPasswordLength' => $this->minimumPasswordLength,
        ]);
    }
}
