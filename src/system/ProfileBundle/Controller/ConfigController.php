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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ProfileBundle\Form\Type\ConfigType;
use Zikula\ProfileBundle\ProfileConstant;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\UsersBundle\Constant as UsersConstant;

/**
 * @PermissionCheck("admin")
 */
#[Route('/profile')]
class ConfigController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaProfile/Config/config.html.twig")
     */
    #[Route('/config', name: 'zikulaprofilebundle_config_config')]
    public function config(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi
    ): array {
        $modVars = $this->getVars();

        $varsInUsersModule = [
            ProfileConstant::MODVAR_AVATAR_IMAGE_PATH => ProfileConstant::DEFAULT_AVATAR_IMAGE_PATH,
            ProfileConstant::MODVAR_GRAVATARS_ENABLED => ProfileConstant::DEFAULT_GRAVATARS_ENABLED,
            ProfileConstant::MODVAR_GRAVATAR_IMAGE =>  ProfileConstant::DEFAULT_GRAVATAR_IMAGE
        ];
        foreach ($varsInUsersModule as $varName => $defaultValue) {
            $modVars[$varName] = $variableApi->get(UsersConstant::MODNAME, $varName, $defaultValue);
        }

        $form = $this->createForm(ConfigType::class, $modVars);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                foreach ($varsInUsersModule as $varName => $defaultValue) {
                    $value = $formData[$varName] ?? $defaultValue;
                    $variableApi->set(UsersConstant::MODNAME, $varName, $value);
                    if (isset($formData[$varName])) {
                        unset($formData[$varName]);
                    }
                }

                $this->setVars($formData);
                $this->addFlash('status', $this->trans('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }
        }

        $pathWarning = '';
        if (true === $modVars['allowUploads']) {
            $path = $kernel->getProjectDir() . '/' . $modVars[ProfileConstant::MODVAR_AVATAR_IMAGE_PATH];
            if (!file_exists($path) || !is_readable($path)) {
                $pathWarning = $this->trans('Warning! The avatar directory does not exist or is not readable for the webserver.');
            } elseif (!is_writable($path)) {
                $pathWarning = $this->trans('Warning! The webserver cannot write to the avatar directory.');
            }
        }

        return [
            'form' => $form->createView(),
            'pathWarning' => $pathWarning
        ];
    }
}
