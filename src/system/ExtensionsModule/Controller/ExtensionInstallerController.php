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

namespace Zikula\ExtensionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Event\ExtensionPostCacheRebuildEvent;
use Zikula\ExtensionsModule\Form\Type\ExtensionInstallType;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;
use Zikula\ExtensionsModule\Helper\ExtensionStateHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("")
 */
class ExtensionInstallerController extends AbstractController
{
    /**
     * @var ExtensionStateHelper
     */
    private $extensionStateHelper;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        ExtensionStateHelper $extensionStateHelper,
        ExtensionRepositoryInterface $extensionRepository
    ) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
        $this->extensionStateHelper = $extensionStateHelper;
        $this->extensionRepository = $extensionRepository;
    }

    /**
     * @Route("/preinstall/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @PermissionCheck("admin")
     * @Theme("admin")
     * @Template("@ZikulaExtensionsModule/Extension/preinstall.html.twig")
     */
    public function preInstall(ExtensionEntity $extension)
    {
        if (Constant::STATE_TRANSITIONAL !== $extension->getState()) {
            $this->extensionStateHelper->updateState($extension->getId(), Constant::STATE_TRANSITIONAL);
            $this->addFlash('success', $this->renderView('@ZikulaExtensionsModule/Extension/installReadyFlashMessage.html.twig', ['extension' => $extension]));

            return $this->redirectToRoute('zikulaextensionsmodule_extension_listextensions');
        }
        $form = $this->createForm(ExtensionInstallType::class, [], [
            'action' => $this->generateUrl('zikulaextensionsmodule_extensioninstaller_install', ['id' => $extension->getId()])
        ]);

        return [
            'extension' => $extension,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/install/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @PermissionCheck("admin")
     * @Theme("admin")
     *
     * Install and initialise an extension.
     *
     * @return RedirectResponse
     */
    public function install(
        Request $request,
        ExtensionEntity $extension,
        ExtensionHelper $extensionHelper
    ) {
        $id = $extension->getId();

        $form = $this->createForm(ExtensionInstallType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('install')->isClicked()) {
                $extensionsInstalled = [];
                $data = $form->getData();

                if ($extensionHelper->install($extension)) {
                    $this->addFlash('status', $this->trans('Done! Installed "%name%".', ['%name%' => $extension->getName()]));
                    $extensionsInstalled[] = $id;

                    return $this->redirectToRoute('zikulaextensionsmodule_extensioninstaller_postinstall', ['extensions' => json_encode($extensionsInstalled)]);
                }
                $this->extensionStateHelper->updateState($id, Constant::STATE_UNINITIALISED);
                $this->addFlash('error', $this->trans('Initialization of "%name%" failed!', ['%name%' => $extension->getName()]));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->extensionStateHelper->updateState($id, Constant::STATE_UNINITIALISED);
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return $this->redirectToRoute('zikulaextensionsmodule_extension_listextensions');
    }

    /**
     * Post-installation action to trigger the MODULE_POSTINSTALL event.
     * The additional Action is required because this event must occur AFTER the rebuild of the cache which occurs on Request.
     *
     * @Route("/postinstall/{extensions}", methods = {"GET"})
     */
    public function postInstall(
        ZikulaHttpKernelInterface $kernel,
        EventDispatcherInterface $eventDispatcher,
        string $extensions = null
    ): RedirectResponse {
        if (!empty($extensions)) {
            $extensions = json_decode($extensions);
            foreach ($extensions as $extensionId) {
                /** @var ExtensionEntity $extensionEntity */
                $extensionEntity = $this->extensionRepository->find($extensionId);
                if (null === $this->extensionRepository) {
                    continue;
                }
                /** @var AbstractExtension $extensionBundle */
                $extensionBundle = $kernel->getBundle($extensionEntity->getName());
                $eventDispatcher->dispatch(new ExtensionPostCacheRebuildEvent($extensionBundle, $extensionEntity));
            }
        }

        return $this->redirectToRoute('zikulaextensionsmodule_extension_listextensions', ['justinstalled' => json_encode($extensions)]);
    }

    /**
     * @Route("/cancel-install/{id}", requirements={"id" = "^[1-9]\d*$"})
     * @PermissionCheck("admin")
     *
     * @return RedirectResponse
     */
    public function cancelInstall(int $id)
    {
        $this->extensionStateHelper->updateState($id, Constant::STATE_UNINITIALISED);
        $this->addFlash('status', 'Operation cancelled.');

        return $this->redirectToRoute('zikulaextensionsmodule_extension_listextensions');
    }
}
