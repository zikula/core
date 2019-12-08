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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;
use Zikula\ThemeModule\Entity\ThemeEntity;
use Zikula\ThemeModule\Helper\BundleSyncHelper;

class ThemeHelper
{
    /**
     * @var BundleSyncHelper
     */
    private $themeSyncHelper;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ThemeHelper constructor.
     */
    public function __construct(
        BundleSyncHelper $themeSyncHelper,
        EntityManagerInterface $em
    ) {
        $this->themeSyncHelper = $themeSyncHelper;
        $this->em = $em;
    }

    public function regenerateThemes(): bool
    {
        // regenerate the themes list
        $this->themeSyncHelper->regenerate();
        // set all themes as active @todo this is probably overkill
        $themes = $this->em->getRepository('ZikulaThemeModule:ThemeEntity')->findAll();
        /** @var ThemeEntity $theme */
        foreach ($themes as $theme) {
            $theme->setState(ThemeEntityRepository::STATE_ACTIVE);
        }
        $this->em->flush();

        return true;
    }
}
