<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ZAuthModule\Form\Type\ImportUserType;
use Zikula\ZAuthModule\Helper\FileIOHelper;

/**
 * @Route("/fileIO")
 */
class FileIOController extends AbstractController
{
    /**
     * @Route("/import")
     * @Theme("admin")
     * @Template("ZikulaZAuthModule:FileIO:import.html.twig")
     *
     * @param Request $request
     * @param VariableApiInterface $variableApi
     * @param GroupRepositoryInterface $groupRepository
     * @param FileIOHelper $ioHelper
     *
     * @return array
     */
    public function importAction(
        Request $request,
        VariableApiInterface $variableApi,
        GroupRepositoryInterface $groupRepository,
        FileIOHelper $ioHelper
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ImportUserType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('upload')->isClicked()) {
                $data = $form->getData();
                $importErrors = $ioHelper->importUsersFromFile($data['file'], $data['delimiter']);
                if (empty($importErrors)) {
                    $this->addFlash('status', $this->__('Done! Users imported.'));
                } else {
                    $this->addFlash('error', $importErrors);
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        $defaultGroupId = $variableApi->get('ZikulaGroupsModule', 'defaultgroup');
        $groupEntity = $groupRepository->find($defaultGroupId);

        return [
            'form' => $form->createView(),
            'defaultGroupName' => $groupEntity->getName()
        ];
    }
}
