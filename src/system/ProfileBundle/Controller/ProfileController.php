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

namespace Zikula\ProfileBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\ProfileBundle\Form\ProfileTypeFactory;
use Zikula\ProfileBundle\Helper\GravatarHelper;
use Zikula\ProfileBundle\Helper\UploadHelper;
use Zikula\ProfileBundle\Repository\PropertyRepositoryInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly PermissionApiInterface $permissionApi,
        private readonly bool $displayRegistrationDate,
        private readonly string $avatarImagePath
    ) {
    }

    /**
     * @PermissionCheck({"$_zkModule::view", "::", "read"})
     */
    #[Route('/display/{uid}', name: 'zikulaprofilebundle_profile_display', requirements: ['uid' => '\d+'], defaults: ['uid' => null])]
    public function display(
        PropertyRepositoryInterface $propertyRepository,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        UserEntity $userEntity = null
    ): Response {
        if (null === $userEntity) {
            $userEntity = $userRepository->find($currentUserApi->get('uid'));
        }

        return $this->render('@ZikulaProfile/Profile/display.html.twig', [
            'prefix' => $this->getParameter('zikula_profile_module.property_prefix'),
            'user' => $userEntity,
            'activeProperties' => $propertyRepository->getDynamicFieldsSpecification(),
            'displayRegistrationDate' => $this->displayRegistrationDate,
        ]);
    }

    #[Route('/edit/{uid}', name: 'zikulaprofilebundle_profile_edit', requirements: ['uid' => '\d+'], defaults: ['uid' => null])]
    public function edit(
        Request $request,
        ManagerRegistry $doctrine,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        ProfileTypeFactory $profileTypeFactory,
        UploadHelper $uploadHelper,
        GravatarHelper $gravatarHelper,
        UserEntity $userEntity = null
    ): Response {
        $currentUserUid = $currentUserApi->get('uid');
        if (null === $userEntity) {
            $userEntity = $userRepository->find($currentUserUid);
        }
        if ($userEntity->getUid() !== $currentUserUid && !$this->permissionApi->hasPermission('ZikulaProfileModule::edit', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $attributes = $userEntity->getAttributes() ?? [];

        // unpack json values (e.g. array for multi-valued options)
        foreach ($attributes as $key => $attribute) {
            $value = $attribute->getValue();
            if (is_string($value) && is_array(json_decode($value, true)) && JSON_ERROR_NONE === json_last_error()) {
                $attribute->setValue(json_decode($value, true));
            }
        }

        $form = $profileTypeFactory->createForm($attributes);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->get('save')->isClicked()) {
            if (!$form->isValid()) {
                $this->addFlash('error', 'Your input was not valid. Please review your input.');
            } else {
                $attributes = $form->getData();
                foreach ($attributes as $attribute => $value) {
                    if (!empty($value)) {
                        if ($value instanceof UploadedFile) {
                            $value = $uploadHelper->handleUpload($value, $userEntity->getUid());
                        } elseif (is_array($value)) {
                            // pack multi-valued options into json
                            $value = json_encode($value);
                        }
                        $userEntity->setAttribute($attribute, $value);
                    } elseif (false === mb_strpos($attribute, 'avatar')) {
                        $userEntity->delAttribute($attribute);
                    }
                }
                $doctrine->getManager()->flush();
            }

            return $this->redirectToRoute('zikulaprofilebundle_profile_display', ['uid' => $userEntity->getUid()]);
        }

        // detach user entity because attributes may be altered for the form (e.g. multiple choice fields)
        $doctrine->getManager()->detach($userEntity);

        return $this->render('@ZikulaProfile/Profile/edit.html.twig', [
            'user' => $userEntity,
            'form' => $form->createView(),
            'imagePath' => $this->avatarImagePath,
            'gravatarUrl' => $gravatarHelper->getGravatarUrl($userEntity->getEmail())
        ]);
    }
}
