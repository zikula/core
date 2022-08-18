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

namespace Zikula\ProfileModule\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ProfileModule\Form\ProfileTypeFactory;
use Zikula\ProfileModule\Helper\GravatarHelper;
use Zikula\ProfileModule\Helper\UploadHelper;
use Zikula\ProfileModule\Repository\PropertyRepositoryInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Repository\UserRepositoryInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/display/{uid}", requirements={"uid" = "\d+"}, defaults={"uid" = null})
     * @PermissionCheck({"$_zkModule::view", "::", "read"})
     * @Template("@ZikulaProfileModule/Profile/display.html.twig")
     */
    public function display(
        PropertyRepositoryInterface $propertyRepository,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        UserEntity $userEntity = null
    ): array {
        if (null === $userEntity) {
            $userEntity = $userRepository->find($currentUserApi->get('uid'));
        }

        return [
            'prefix' => $this->getParameter('zikula_profile_module.property_prefix'),
            'user' => $userEntity,
            'activeProperties' => $propertyRepository->getDynamicFieldsSpecification(),
        ];
    }

    /**
     * @Route("/edit/{uid}", requirements={"uid" = "\d+"}, defaults={"uid" = null})
     * @Template("@ZikulaProfileModule/Profile/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function edit(
        Request $request,
        ManagerRegistry $doctrine,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        ProfileTypeFactory $profileTypeFactory,
        UploadHelper $uploadHelper,
        GravatarHelper $gravatarHelper,
        UserEntity $userEntity = null
    ) {
        $currentUserUid = $currentUserApi->get('uid');
        if (null === $userEntity) {
            $userEntity = $userRepository->find($currentUserUid);
        }
        if ($userEntity->getUid() !== $currentUserUid && !$this->hasPermission('ZikulaProfileModule::edit', '::', ACCESS_EDIT)) {
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

            return $this->redirectToRoute('zikulaprofilemodule_profile_display', ['uid' => $userEntity->getUid()]);
        }

        // detach user entity because attributes may be altered for the form (e.g. multiple choice fields)
        $doctrine->getManager()->detach($userEntity);

        return [
            'user' => $userEntity,
            'form' => $form->createView(),
            'gravatarUrl' => $gravatarHelper->getGravatarUrl($userEntity->getEmail())
        ];
    }
}
