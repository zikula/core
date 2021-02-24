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

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;

class SchemaToolInstallCommand extends Command
{
    protected static $defaultName = 'zikula:schema:create';

    /**
     * @var SchemaHelper
     */
    private $schemaHelper;

    public function __construct(SchemaHelper $schemaHelper)
    {
        parent::__construct();
        $this->schemaHelper = $schemaHelper;
    }

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('entityClass', InputArgument::REQUIRED, 'The FqCN of the Entity Class'),
            ])
            ->setDescription('Create a new table in the DB for the provided entity class.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityClass = $input->getArgument('entityClass');
        if (!class_exists($entityClass)) {
            throw new InvalidArgumentException('The entity class does not exist.');
        }
        $this->schemaHelper->create([$entityClass]);

        return Command::SUCCESS;
    }
}
