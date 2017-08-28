<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

class UiHooksSubscriber implements HookSubscriberInterface
{
    const HTMLBLOCK_EDIT_FORM = 'blocks.ui_hooks.htmlblock.content.form_edit';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getOwner()
    {
        return 'ZikulaBlocksModule';
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('HTML Block content hook');
    }

    public function getEvents()
    {
        return [
            UiHooksCategory::TYPE_FORM_EDIT => 'blocks.ui_hooks.htmlblock.content.form_edit'
        ];
    }
}
