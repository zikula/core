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

namespace Zikula\ProfileModule\HookSubscriber;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

class UiHooksSubscriber implements HookSubscriberInterface
{
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
        return 'ZikulaProfileModule';
    }

    public function getCategory(): string
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle(): string
    {
        return $this->translator->trans('Profile Display');
    }

    public function getAreaName(): string
    {
        return 'subscriber.zikulaprofilemodule.ui_hooks.profile_display';
    }

    public function getEvents(): array
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'zikulaprofilemodule.ui_hooks.display.profile'
        ];
    }
}
