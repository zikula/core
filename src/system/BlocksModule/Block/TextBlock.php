<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Block;

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\AbstractBlockHandler;

/**
 * Block to display simple rendered text
 */
class TextBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered block
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('Textblock::', "$properties[title]::", ACCESS_OVERVIEW)) {
            return '';
        }

        return nl2br($properties['content']);
    }

    /**
     * @param Request $request
     * @param array $properties
     * @return array|string
     */
    public function modify(Request $request, array $properties)
    {
        $defaults = [
            'content' => ''
        ];
        $vars = array_merge($defaults, $properties);
        $form = $this->createFormBuilder($vars)
            ->add('content', 'Symfony\Component\Form\Extension\Core\Type\TextareaType')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $data['content'] = strip_tags($data['content']);

            return $data;
        }

        return $this->renderView('ZikulaBlocksModule:Block:default_modify.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
