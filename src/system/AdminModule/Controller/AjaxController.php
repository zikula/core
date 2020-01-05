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

namespace Zikula\AdminModule\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the admin module
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/assigncategory", options={"expose"=true})
     *
     * Change the category a module belongs to by ajax.
     */
    public function changeModuleCategoryAction(
        Request $request,
        RouterInterface $router,
        ExtensionRepositoryInterface $extensionRepository,
        AdminCategoryRepositoryInterface $adminCategoryRepository,
        AdminModuleRepositoryInterface $adminModuleRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        $moduleId = $request->request->getInt('modid');
        $newParentCat = $request->request->getInt('cat');

        /** @var ExtensionEntity $module */
        $module = $extensionRepository->find($moduleId);
        if (!$module) {
            return $this->json($this->__f('Error! Could not get module name for id %s.', ['%s' => $moduleId]), Response::HTTP_NOT_FOUND);
        }

        // get the module name
        $displayname = $module->getDisplayName();
        $url = $module['capabilities']['admin']['url']
            ?? $router->generate($module['capabilities']['admin']['route']);
        $oldCategory = $adminCategoryRepository->getModuleCategory($moduleId);
        $sortOrder = $adminModuleRepository->countModulesByCategory($newParentCat);

        // move the module
        $adminModuleEntity = $adminModuleRepository->findOneBy(['mid' => $moduleId]);
        if (!$adminModuleEntity) {
            $adminModuleEntity = new AdminModuleEntity();
        }
        $adminModuleEntity->setMid($moduleId);
        $adminModuleEntity->setCid($newParentCat);
        $adminModuleEntity->setSortorder($sortOrder);
        $adminModuleRepository->persistAndFlush($adminModuleEntity);

        return $this->json([
            'id' => $moduleId,
            'name' => $displayname,
            'url' => $url,
            'parentCategory' => $newParentCat,
            'oldCategory' => null !== $oldCategory ? $oldCategory['cid'] : false
        ]);
    }

    /**
     * @Route("/newcategory", options={"expose"=true})
     *
     * Add a new admin category by ajax.
     */
    public function addCategoryAction(
        Request $request,
        RouterInterface $router,
        AdminCategoryRepositoryInterface $adminCategoryRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        // get form information
        $name = trim($request->request->get('name'));

        // make sure name is set.
        if ('' === $name) {
            return $this->json($this->__('Error! No category name given.'), Response::HTTP_BAD_REQUEST);
        }

        // Security check
        if (!$this->hasPermission('ZikulaAdminModule::Category', "${name}::", ACCESS_ADD)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        // check if category with same name exists
        $items = $adminCategoryRepository->findBy([], ['sortorder' => 'ASC']);
        foreach ($items as $cat) {
            if ($name === $cat['name']) {
                return $this->json($this->__('Error! A category by this name already exists.'), Response::HTTP_BAD_REQUEST);
            }
        }

        $item = new AdminCategoryEntity();
        $item->setName($name);
        $item->setDescription('');
        $item->setSortorder($adminCategoryRepository->countCategories());

        $this->getDoctrine()->getManager()->persist($item);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'id' => $item->getCid(),
            'name' => $name,
            'url' => $router->generate('zikulaadminmodule_admin_adminpanel', ['acid' => $item->getCid()])
        ]);
    }

    /**
     * @Route("/deletecategory", options={"expose"=true})
     *
     * Delete an admin category by ajax.
     */
    public function deleteCategoryAction(
        Request $request,
        AdminCategoryRepositoryInterface $adminCategoryRepository,
        AdminModuleRepositoryInterface $adminModuleRepository
    ): JsonResponse {
        //get passed cid to delete
        $cid = $request->request->getInt('cid');

        //check user has permission to delete this
        if (!$this->hasPermission('ZikulaAdminModule::Category', "::${cid}", ACCESS_DELETE)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        // retrieve the category object
        $item = $adminCategoryRepository->findOneBy(['cid' => $cid]);
        if (null === $item) {
            return $this->json($this->__('Error! No such category found.'), Response::HTTP_NOT_FOUND);
        }

        if (!$this->hasPermission('ZikulaAdminModule::Category', $item['name'] . '::' . $item['cid'], ACCESS_DELETE)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        // Avoid deletion of the default category
        $defaultcategory = $this->getVar('defaultcategory');
        if ($cid === $defaultcategory) {
            return new JsonResponse($this->__('Error! You cannot delete the default module category used in the administration panel.'), Response::HTTP_BAD_REQUEST);
        }

        // Avoid deletion of the start category
        $startcategory = $this->getVar('startcategory');
        if ($cid === $startcategory) {
            return new JsonResponse($this->__('Error! This module category is currently set as the category that is initially displayed when you visit the administration panel. You must first select a different category for initial display. Afterwards, you will be able to delete the category you have just attempted to remove.'), Response::HTTP_BAD_REQUEST);
        }

        // move all modules from the category to be deleted into the default category.
        $adminModuleRepository->changeCategory($cid, $defaultcategory);

        // delete the category
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($item);
        $entityManager->flush();

        return $this->json([
            'response' => $cid
        ]);
    }

    /**
     * @Route("/editcategory", options={"expose"=true})
     *
     * Edit an admin category by ajax.
     */
    public function editCategoryAction(
        Request $request,
        AdminCategoryRepositoryInterface $adminCategoryRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        //get form values
        $cid = trim($request->request->getInt('cid'));
        $name = trim($request->request->get('name'));

        //security checks
        if (!$this->hasPermission('ZikulaAdminModule::Category', $name . '::' . $cid, ACCESS_EDIT)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        //make sure cid and category name (cat) are both set
        if (!isset($cid, $name) || '' === $cid || '' === $name) {
            return $this->json($this->__('No category name or id set.'), Response::HTTP_BAD_REQUEST);
        }

        //check if category with same name exists
        $categories = [];
        $items = $adminCategoryRepository->findBy([], ['sortorder' => 'ASC']);
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[] = $item;
            }
        }

        foreach ($categories as $cat) {
            if ($name !== $cat['name']) {
                continue;
            }

            //check to see if the category with same name is the same category.
            if ($cat['cid'] === $cid) {
                return $this->json([
                    'response' => $name
                ]);
            }

            //a different category has the same name, not allowed.
            return $this->json($this->__('Error! A category by this name already exists.'), Response::HTTP_BAD_REQUEST);
        }

        // retrieve the category object
        $item = $adminCategoryRepository->findOneBy(['cid' => $cid]);
        if (null === $item) {
            return $this->json($this->__('Error! No such category found.'), Response::HTTP_NOT_FOUND);
        }

        // update the category using the info from the database and from the form.
        $item->merge([
            'name' => $name,
            'description' => $item['description']
        ]);
        $this->getDoctrine()->getManager()->flush();

        return $this->json([
            'response' => $name
        ]);
    }

    /**
     * @Route("/makedefault", options={"expose"=true})
     *
     * Make a category the initially selected one (by ajax).
     */
    public function defaultCategoryAction(
        Request $request,
        AdminCategoryRepositoryInterface $adminCategoryRepository,
        VariableApiInterface $variableApi
    ): JsonResponse {
        //check user has permission to change the initially selected category
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        //get passed cid
        $cid = trim($request->request->getInt('cid'));

        // retrieve the category object
        $item = $adminCategoryRepository->findOneBy(['cid' => $cid]);
        if (false === $item) {
            return $this->json($this->__('Error! No such category found.'), Response::HTTP_NOT_FOUND);
        }

        // make category the initially selected one
        $makeDefault = $variableApi->set('ZikulaAdminModule', 'startcategory', $cid);
        if ($makeDefault) {
            // Success
            return $this->json([
                'response' => $this->__f('Category "%s" was successfully made default.', ['%s' => $item['name']])
            ]);
        }

        //unknown error
        return $this->json($this->__('Error! Could not make this category default.'), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/sortcategories", options={"expose"=true})
     *
     * Sort the admin categories.
     */
    public function sortCategoriesAction(
        Request $request,
        AdminCategoryRepositoryInterface $adminCategoryRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->request->get('admintabs');

        foreach ($data as $order => $cid) {
            $item = $adminCategoryRepository->findOneBy(['cid' => $cid]);
            if (null !== $item) {
                $item->setSortorder($order);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return $this->json([]);
    }

    /**
     * @Route("/sortmodules", options={"expose"=true})
     *
     * Sort the modules.
     */
    public function sortModulesAction(
        Request $request,
        AdminModuleRepositoryInterface $adminModuleRepository
    ): JsonResponse {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            return $this->json($this->__('Access forbidden.'), Response::HTTP_FORBIDDEN);
        }

        $data = $request->request->get('modules');

        foreach ($data as $order => $mid) {
            $item = $adminModuleRepository->findOneBy(['mid' => $mid]);
            if (null !== $item) {
                $item->setSortorder($order);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return $this->json([]);
    }
}
