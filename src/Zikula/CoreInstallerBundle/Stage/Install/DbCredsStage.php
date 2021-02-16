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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType;
use Zikula\Bundle\CoreInstallerBundle\Helper\DbCredsHelper;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\StageInterface;

class DbCredsStage implements StageInterface, FormHandlerInterface
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var string
     */
    private $databaseUrl;

    public function __construct(string $projectDir, string $databaseUrl = '')
    {
        $this->projectDir = $projectDir;
        $this->databaseUrl = $databaseUrl;
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
        return '@ZikulaCoreInstaller/Install/dbCreds.html.twig';
    }

    public function isNecessary(): bool
    {
        $databaseUrl = $this->databaseUrl;
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
        if (!(new DbCredsHelper($this->projectDir))->writeDatabaseDsn($form->getData())) {
            throw new AbortStageException(sprintf('Cannot write to %s file.', $this->projectDir . '\.env.local'));
        }

        return true;
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
