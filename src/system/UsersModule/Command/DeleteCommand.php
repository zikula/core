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

namespace Zikula\UsersModule\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\UsersModule\Helper\DeleteHelper;

class DeleteCommand extends Command
{
    protected static $defaultName = 'zikula:users:delete';

    /**
     * @var DeleteHelper
     */
    private $deleteHelper;

    public function __construct(DeleteHelper $deleteHelper)
    {
        parent::__construct();
        $this->deleteHelper = $deleteHelper;
    }

    protected function configure()
    {
        $this
            ->addOption('uid', 'u', InputOption::VALUE_REQUIRED, 'User ID (int)')
            ->addOption('gid', 'g', InputOption::VALUE_REQUIRED, 'Group ID (int)')
            ->addOption('status', 's', InputOption::VALUE_REQUIRED, 'User activated status (A|I|P|M)')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'filter by user_regdate *before* YYYYMMDDHHMMSS')
            ->setDescription('Delete one or more users')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command deletes one or more users.
This command dispatches all events and hooks like a standard user deletion.
Do not use uid, gid or status simultaneously. Use only one

<info>php %command.full_name% -u 478</info>

This will delete user with uid 478.

Options:
<info>--uid</info> <comment>(int)</comment> delete one user by uid

<info>--gid</info> <comment>(int)</comment> delete all users in group gid (does not delete the actual group)

<info>--status</info> <comment>I|P|M</comment> delete users by status (active members cannot be deleted this way) (I=inactive, P=pending, M=marked for deletion)

<info>--date</info> <comment>YYYYMMDDHHMMSS</comment> before deleting, filter user collection by date <comment>before</comment> this date.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $params = [
            'gid' => $input->getOption('gid'),
            'status' => $input->getOption('status'),
            'uid' => $input->getOption('uid'),
        ];
        $date = $input->getOption('date');

        if (count(array_filter($params)) > 1) {
            $io->error('Do not use more than one option or argument.');

            return 1;
        }

        $users = new ArrayCollection();
        foreach ($params as $name => $value) {
            if (isset($value)) {
                try {
                    $users = $this->deleteHelper->getUserCollection($name, $value, $date);
                } catch (\InvalidArgumentException $exception) {
                    $io->error($exception->getMessage());

                    return 2;
                }
                break;
            }
        }

        if ($users->isEmpty()) {
            $io->error('No users found!');

            return 3;
        }

        $io->title('Zikula user deletion');
        $count = count($users);
        $io->progressStart($count);
        foreach ($users as $user) {
            $this->deleteHelper->deleteUser($user);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(sprintf('Success! %d users deleted.', $count));

        return 0;
    }
}
