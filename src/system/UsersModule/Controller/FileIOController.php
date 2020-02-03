<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Form\Type\ExportUsersType;

/**
 * @Route("/fileIO")
 */
class FileIOController extends AbstractController
{
    /**
     * @Route("/export")
     * @Theme("admin")
     * @Template("@ZikulaUsersModule/FileIO/export.html.twig")
     *
     * @return array|StreamedResponse
     * @throws AccessDeniedException Thrown if the user hasn't admin permissions for the module
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
                $response->setCallback(function() use ($data, $userRepository) {
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

        return [
            'form' => $form->createView()
        ];
    }
}
