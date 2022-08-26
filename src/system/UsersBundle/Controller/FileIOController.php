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

namespace Zikula\UsersBundle\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Form\Type\ExportUsersType;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

#[Route('/users/fileIO')]
#[PermissionCheck('admin')]
class FileIOController extends AbstractController
{
    #[Route('/export', name: 'zikulausersbundle_fileio_export')]
    #[Theme('admin')]
    public function export(Request $request, UserRepositoryInterface $userRepository)
    {
        $form = $this->createForm(ExportUsersType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('download')->isClicked()) {
                $data = $form->getData();
                $response = new StreamedResponse();
                $response->setCallback(function () use ($data, $userRepository) {
                    $fields = ['uid', 'uname', 'activated', 'email', 'registrationDate', 'lastLogin', 'groups'];
                    foreach ($fields as $k => $field) {
                        if (isset($data[$field]) && !$data[$field]) {
                            unset($fields[$k]); // remove unwanted fields
                        }
                    }
                    $handle = fopen('php://output', 'wb+');
                    if ($data['title']) {
                        fputcsv($handle, $fields, $data['delimiter']);
                    }
                    $users = $userRepository->findAllAsIterable();
                    /** @var UserEntity $user */
                    foreach ($users as $user) {
                        $row = [];
                        foreach ($fields as $field) {
                            if ('groups' === $field) {
                                $gids = [];
                                /** @var GroupEntity $group */
                                foreach ($user[0]->getGroups() as $group) {
                                    $gids[] = $group->getGid();
                                }
                                $row[] = implode('|', $gids);
                            } else {
                                $method = 'get' . ucwords($field);
                                $value = $user[0]->{$method}();
                                $row[] = $value instanceof DateTime ? $value->format('c') : $value;
                            }
                        }
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
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return $this->render('@ZikulaUsers/FileIO/export.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
