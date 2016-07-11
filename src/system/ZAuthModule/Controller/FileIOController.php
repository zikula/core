<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/fileIO")
 */
class FileIOController extends AbstractController
{
    /**
     * @Route("/import")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @return array
     */
    public function importAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaZAuthModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\ZAuthModule\Form\Type\ImportUserType',
            [], ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('upload')->isClicked()) {
                $data = $form->getData();
                $importErrors = $this->get('zikula_zauth_module.helper.file_io')->importUsersFromFile($data['file'], $data['delimiter']);
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

        $defaultGroupId = $this->get('zikula_extensions_module.api.variable')->get('ZikulaGroupsModule', 'defaultgroup');
        $groupEntity = $this->get('doctrine')->getManager()->getRepository('ZikulaGroupsModule:GroupEntity')->find($defaultGroupId);

        return [
            'form' => $form->createView(),
            'defaultGroupName' => $groupEntity->getName()
        ];
    }
}
