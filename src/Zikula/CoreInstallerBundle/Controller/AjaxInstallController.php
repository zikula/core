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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreInstallerBundle\Helper\StageHelper;

class AjaxInstallController
{
    /**
     * @var StageHelper
     */
    private $stageHelper;

    public function __construct(StageHelper $stageHelper)
    {
        $this->stageHelper = $stageHelper;
    }

    /**
     * @Route("/ajaxinstall", name="ajaxinstall", options={"expose": true, "i18n": false})
     */
    public function ajax(Request $request): JsonResponse
    {
        $stage = $request->request->get('stage');
        $status = $this->stageHelper->executeStage($stage);

        return new JsonResponse(['status' => $status]);
    }
}
