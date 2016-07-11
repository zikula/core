<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/fileIO")
 */
class FileIOController extends AbstractController
{
    /**
     * @Route("/export")
     * @Theme("admin")
     * @Template
     * @param Request $request
     * @return array
     */
    public function exportAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\UsersModule\Form\Type\ExportUsersType',
            [], ['translator' => $this->get('translator.default')]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('download')->isClicked()) {
                $data = $form->getData();
                $response = new StreamedResponse();
                $response->setCallback(function () use ($data) {
                    $fields = ['uid', 'uname', 'activated', 'email', 'user_regdate', 'lastlogin', 'groups'];
                    foreach ($fields as $k => $field) {
                        if (isset($data[$field]) && !$data[$field]) {
                            unset($fields[$k]); // remove unwanted fields
                        }
                    }
                    $handle = fopen('php://output', 'w+');
                    if ($data['title']) {
                        fputcsv($handle, $fields, $data['delimiter']);
                    }
                    $users = $this->get('zikula_users_module.user_repository')->findAllAsIterable();
                    foreach ($users as $user) {
                        $row = [];
                        foreach ($fields as $field) {
                            if ('groups' == $field) {
                                $gids = [];
                                foreach ($user[0]->getGroups() as $group) {
                                    $gids[] = $group->getGid();
                                }
                                $row[] = implode('|', $gids);
                            } else {
                                $method = 'get' . ucwords($field);
                                $value = $user[0]->$method();
                                $row[] = $value instanceof \DateTime ? $value->format('c') : $value;
                            }
                        }
                        $this->get('doctrine')->getManager()->detach($user[0]);
                        fputcsv($handle, $row, $data['delimiter']);
                    }
                    fclose($handle);
                });

                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $data['filename'] . '"');

                return $response;
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
