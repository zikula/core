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

namespace Zikula\BlocksModule\HookSubscriber;

use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;

class UiHooksSubscriber implements HookSubscriberInterface
{
    public const HTMLBLOCK_EDIT_FORM = 'blocks.ui_hooks.htmlblock.content.form_edit';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getOwner(): string
    {
        return 'ZikulaBlocksModule';
    }

    public function getCategory(): string
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle(): string
    {
        return $this->translator->trans('HTML Block content hook');
    }

    public function getAreaName(): string
    {
        return 'subscriber.blocks.ui_hooks.htmlblock.content';
    }

    public function getEvents(): array
    {
        return [
            UiHooksCategory::TYPE_FORM_EDIT => 'blocks.ui_hooks.htmlblock.content.form_edit'
        ];
    }
}
