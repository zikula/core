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

namespace Zikula\Bundle\CoreInstallerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Bundle\CoreInstallerBundle\Manager\StageManager;

/**
 * Class AjaxInstallController
 */
class AjaxInstallController extends AbstractController
{
    /**
     * @var StageManager
     */
    private $stageManager;

    public function __construct(ContainerInterface $container, StageManager $stageManager)
    {
        parent::__construct($container);
        $this->stageManager = $stageManager;
    }

    public function ajaxAction(Request $request): JsonResponse
    {
        $stage = $request->request->get('stage');
        $status = $this->stageManager->executeStage($stage);

        return new JsonResponse(['status' => $status]);
    }
}
