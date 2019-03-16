<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Form\Type\ExportUsersType;

/**
 * @Route("/fileIO")
 */
class FileIOController extends AbstractController
{
    /**
     * @Route("/export")
     * @Theme("admin")
     * @Template("ZikulaUsersModule:FileIO:export.html.twig")
     *
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     *
     * @return array|StreamedResponse
     */
    public function exportAction(Request $request, UserRepositoryInterface $userRepository)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ExportUsersType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('download')->isClicked()) {
                $data = $form->getData();
                $response = new StreamedResponse();
                $response->setCallback(function() use ($data) {
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
                    $users = $userRepository->findAllAsIterable();
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
            'form' => $form->createView()
        ];
    }
}
