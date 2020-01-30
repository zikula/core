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

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
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
        $databaseUrl = $_ENV['DATABASE_URL'] ?? $params['database_url'] ?? '';
        if (!empty($databaseUrl)) {
            // test the connection here.
            $test = $this->testDBConnection($databaseUrl);
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
        $databaseUrl = 'pdo_' . $data['database_driver']
            . '://' . $data['database_user'] . ':' . $data['database_password']
            . '@' . $data['database_host'] . (!empty($data['database_port']) ? ':' . $data['database_port'] : '')
            . '/' . $data['database_name']
        ;
        $databaseUrl .= '?charset=UTF8';
        $databaseUrl .= '&serverVersion=5.7'; // any value will work (bypasses DBALException)

        $dbParams = [
            'database_url' => $databaseUrl
        ];
        $params = array_merge($this->yamlManager->getParameters(), $dbParams);
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

    public function testDBConnection(string $databaseUrl = '')
    {
        $connectionParams = [
            'url' => $databaseUrl
        ];

        try {
            DriverManager::getConnection($connectionParams, new Configuration());
        } catch (DBALException $exception) {
            return $exception->getMessage();
        }

        return true;
    }
}
