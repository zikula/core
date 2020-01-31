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
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType;
use Zikula\Bundle\CoreInstallerBundle\Helper\DbCredsHelper;
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

    /**
     * @var string
     */
    private $localEnvFile;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $projectDir = $this->container->get('kernel')->getProjectDir();
        $this->yamlManager = new YamlDumper($projectDir . '/config', 'services_custom.yaml');
        $this->localEnvFile = $projectDir . '/.env.local';
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
        $databaseUrl = $_ENV['DATABASE_URL'] ?? '';
        if (empty($databaseUrl) || 'nothing' === $databaseUrl) {
            // check if credentials are temporarily stored as parameter during installation
            $databaseUrl = $params['database_url'] ?? '';
        }
        if (!empty($databaseUrl) && 'nothing' !== $databaseUrl) {
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
        $dbCredsHelper = new DbCredsHelper();
        $databaseUrl = $dbCredsHelper->buildDatabaseUrl($form->getData());

        $this->writeDatabaseUrl($databaseUrl);

        return true;
    }

    private function writeDatabaseUrl(string $databaseUrl): void
    {
        // write env vars into .env.local
        $content = 'DATABASE_URL=\'' . $databaseUrl . "'\n";

        $fileSystem = new Filesystem();
        try {
            $fileSystem->dumpFile($this->localEnvFile, $content);
        } catch (IOExceptionInterface $exception) {
            throw new AbortStageException(sprintf('Cannot write parameters to %s file.', $this->localEnvFile) . ' ' . $exception->getMessage());
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
            $connection = DriverManager::getConnection($connectionParams, new Configuration());
            if ($connection->connect()) {
                return true;
            }
        } catch (DBALException $exception) {
            return $exception->getMessage();
        }

        return true;
    }
}
