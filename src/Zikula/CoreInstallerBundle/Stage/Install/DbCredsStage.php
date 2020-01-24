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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use PDO;
use PDOException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class DbCredsStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    /**
     * @var YamlDumper
     */
    private $yamlManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getProjectDir() . '/config', 'services_custom.yaml');
    }

    public function getName(): string
    {
        return 'dbcreds';
    }

    public function getFormType(): string
    {
        return DbCredsType::class;
    }

    public function getFormOptions(): array
    {
        return [];
    }

    public function getTemplateName(): string
    {
        return '@ZikulaCoreInstaller/Install/dbcreds.html.twig';
    }

    public function isNecessary(): bool
    {
        $params = $this->yamlManager->getParameters();
        if (!empty($params['database_host']) && !empty($params['database_user']) && !empty($params['database_name'])) {
            // test the connection here.
            $test = $this->testDBConnection($params);
            if (true !== $test) {
                throw new AbortStageException($test);
            }

            return false;
        }

        return true;
    }

    public function getTemplateParams(): array
    {
        return [];
    }

    public function handleFormResult(FormInterface $form): bool
    {
        $data = $form->getData();
        $params = array_merge($this->yamlManager->getParameters(), $data);
        if (0 !== mb_strpos($params['database_driver'], 'pdo_')) {
            $params['database_driver'] = 'pdo_' . $params['database_driver']; // doctrine requires prefix in services_custom.yaml
        }
        $this->writeParams($params);

        return true;
    }

    private function writeParams($params): void
    {
        try {
            $this->yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException(sprintf('Cannot write parameters to %s file.', 'services_custom.yaml'));
        }
        // clear the cache
        $this->container->get(CacheClearer::class)->clear('symfony.config');
    }

    public function testDBConnection($params)
    {
        $params['database_driver'] = mb_substr($params['database_driver'], 4);
        $dsn = $params['database_driver'] . ':host=' . $params['database_host'] . ';dbname=' . $params['database_name'];
        try {
            new PDO($dsn, $params['database_user'], $params['database_password']);
        } catch (PDOException $exception) {
            return $exception->getMessage();
        }

        return true;
    }
}
