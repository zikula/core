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
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Helper\ParameterHelper;
use Zikula\Bundle\CoreInstallerBundle\Helper\StageHelper;

/**
 * Class AjaxUpgradeController
 */
class AjaxUpgradeController extends AbstractController
{
    /**
     * @var YamlDumper
     */
    private $yamlManager;

    /**
     * @var StageHelper
     */
    private $stageHelper;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->yamlManager = $container->get(ParameterHelper::class)->getYamlManager();
        $this->stageHelper = $container->get(StageHelper::class);
    }

    public function ajaxAction(Request $request): JsonResponse
    {
        $stage = $request->request->get('stage');
        $this->yamlManager->setParameter('upgrading', true);
        $status = $this->stageHelper->executeStage($stage);

        return new JsonResponse(['status' => $status]);
    }
}
