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

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Zikula\Component\Wizard\StageInterface;

interface AjaxStageInterface extends StageInterface
{
    public const NAME = 'name';

    public const PRE = 'pre';

    public const DURING = 'during';

    public const SUCCESS = 'success';

    public const FAIL = 'fail';
}
