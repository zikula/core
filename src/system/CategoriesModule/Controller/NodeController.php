<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Zikula\CategoriesModule\Entity\CategoryEntity;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Zikula\Core\Response\Ajax\ForbiddenResponse;

/**
 * @Route("/admin/category")
 *
 * Controller for handling category registries.
 */
class NodeController extends AbstractController
{
    private $domTreeNodePrefix = 'node_';

    /**
     * @Route("/contextMenu/{action}/{id}", options={"expose"=true})
     * @param Request $request
     * @param string $action
     * @param CategoryEntity $category
     * @return AjaxResponse|BadDataResponse|ForbiddenResponse
     */
    public function contextMenuAction(Request $request, $action = 'edit', CategoryEntity $category = null)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }
        if (!in_array($action, ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate'])) {
            return new BadDataResponse($this->__('Data provided was inappropriate.'));
        }
        $repo = $this->get('zikula_categories_module.category_repository');
        $mode = $request->request->get('mode', 'edit');

        switch ($action) {
            case 'edit':
                if (!isset($category)) {
                    $category = new CategoryEntity($this->get('zikula_settings_module.locale_api')->getSupportedLocales());
                    $parentId = $request->request->get('parent');
                    $mode = 'new';
                    if (!empty($parentId)) {
                        $parent = $repo->find($request->request->get('parent'));
                        $category->setParent($parent);
                        $category->setRoot($parent->getRoot());
                    } elseif (empty($parent) && $request->request->has('after')) { // sibling of top-level child
                        $sibling = $repo->find($request->request->get('after'));
                        $category->setParent($sibling->getParent());
                        $category->setRoot($sibling->getRoot());
                    }
                }
                $form = $this->createForm('Zikula\CategoriesModule\Form\Type\CategoryType', $category, [
                    'translator' => $this->get('translator.default'),
                    'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocales(),
                ]);
                $form->get('after')->setData($request->request->get('after', null));
                if ($form->handleRequest($request)->isValid()) {
                    $category = $form->getData();
                    $after = $form->get('after')->getData();
                    if (!empty($after)) {
                        $sibling = $repo->find($after);
                        $repo->persistAsNextSiblingOf($category, $sibling);
                    } elseif ($mode == 'new') {
                        $repo->persistAsLastChild($category);
                    } // no need to persist edited entity
                    $this->get('doctrine')->getManager()->flush();

                    return new AjaxResponse([
                        'node' => $category->toJson($this->domTreeNodePrefix, $request->getLocale()),
                        'mode' => $mode
                    ]);
                }
                $response = [
                    'result' => $this->renderView('@ZikulaCategoriesModule/Category/edit.html.twig', [
                        'locales' => $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames(),
                        'form' => $form->createView()
                    ]),
                    'action' => $action,
                    'id' => $category->getId(),
                    'mode' => $mode
                ];
                break;
            case 'delete':
                $id = $category->getId();
                $this->get('doctrine')->getManager()->remove($category);
                $this->get('doctrine')->getManager()->flush();
                $response = [
                    'id' => $id,
                    'action' => $action,
                ];
                break;
            default:
                $response = ['result' => true];
        }

        return new AjaxResponse($response);
    }

    /**
     * Ajax function for use on Drag and Drop of nodes.
     * @Route("/move", options={"expose"=true})
     * @param Request $request
     * @return AjaxResponse|ForbiddenResponse
     */
    public function moveAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaCategoriesModule::', '::', ACCESS_ADMIN)) {
            return new ForbiddenResponse($this->__('No permission for this action'));
        }
        $repo = $this->get('zikula_categories_module.category_repository');
        $node = $request->request->get('node');
        $entityId = str_replace($this->domTreeNodePrefix, '', $node['id']);
        $category = $repo->find($entityId);
        $oldParent = $request->request->get('old_parent');
        $oldPosition = (int) $request->request->get('old_position');
        $parent = $request->request->get('parent');
        $position = (int) $request->request->get('position');
        if ($oldParent == $parent) {
            $diff = $oldPosition - $position; // if $diff is positive, then node moved up
            $methodName = $diff > 0 ? 'moveUp' : 'moveDown';
            $repo->$methodName($category, abs($diff));
        } else {
            $parentEntity = $repo->find(str_replace($this->domTreeNodePrefix, '', $parent));
            $children = $repo->children($parentEntity);
            $repo->persistAsNextSiblingOf($category, $children[$position - 1]);
        }
        $this->get('doctrine')->getManager()->flush();

        return new AjaxResponse(['result' => true]);
    }
}
