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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\GroupsModule\Constant;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\HookSubscriber\UserManagementUiHooksSubscriber;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;

class DeleteCommand extends Command
{
    protected static $defaultName = 'zikula:users:delete';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRespository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HookDispatcherInterface $hookDispatcher,
        UserRepositoryInterface $userRepository,
        GroupRepositoryInterface $groupRespository
    ) {
        parent::__construct();
        $this->eventDispatcher = $eventDispatcher;
        $this->hookDispatcher = $hookDispatcher;
        $this->userRepository = $userRepository;
        $this->groupRespository = $groupRespository;
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
        $uid = $input->getOption('uid');
        $gid = $input->getOption('gid');
        $status = $input->getOption('status');
        $date = $input->getOption('date');

        if (($uid && $gid) || ($uid && $status) || ($gid && $status)) {
            $io->error('Do not use more than one option or argument.');

            return 1;
        }

        if (isset($gid)) {
            if (Constant::GROUP_ID_USERS === (int) $gid) {
                if (!$io->confirm('You have selected to delete from the main user group. This is not recommended. Do you wish to proceed?', false)) {
                    $io->caution('Deletion cancelled');

                    return 0;
                }
            }
            $users = $this->groupRespository->find($gid)->getUsers();
        }

        if (isset($status)) {
            $statuses = [
                'I' => UsersConstant::ACTIVATED_INACTIVE,
                'P' => UsersConstant::ACTIVATED_PENDING_REG,
                'M' => UsersConstant::ACTIVATED_PENDING_DELETE
            ];
            if (!array_key_exists($status, $statuses)) {
                $io->error('Invalid status value');

                return 2;
            }
            $users = $this->userRepository->findBy(['activated' => $statuses[$status]]);
            $users = new ArrayCollection($users);
        }

        if (isset($uid)) {
            $user = $this->userRepository->find($uid);
            $users = new ArrayCollection([$user]);
        }

        if (isset($date)) {
            $date = \DateTime::createFromFormat('YmdHis', $date, new \DateTimeZone('UTC'));
            $users = $users->filter(function (UserEntity $user) use ($date) {
                return $user->getRegistrationDate() < $date;
            });
        }

        $adminUser = $this->userRepository->find(UsersConstant::USER_ID_ADMIN);
        if ($users->contains($adminUser)) {
            $users->removeElement($adminUser);
            $io->note(sprintf('The main admin user cannot be deleted (uname: %s)', $adminUser->getUname()));
        }

        if ($users->isEmpty()) {
            $io->error('No users found!');

            return 3;
        }

        $io->title('Zikula user deletion');
        $count = count($users);
        $io->progressStart($count);
        foreach ($users as $user) {
            $eventName = UsersConstant::ACTIVATED_ACTIVE === $user->getActivated() ? UserEvents::DELETE_ACCOUNT : RegistrationEvents::DELETE_REGISTRATION;
            $this->eventDispatcher->dispatch(new GenericEvent($user->getUid()), $eventName);
            $this->eventDispatcher->dispatch(new GenericEvent(null, ['id' => $user->getUid()]), UserEvents::DELETE_PROCESS);
            $this->hookDispatcher->dispatch(UserManagementUiHooksSubscriber::DELETE_PROCESS, new ProcessHook($user->getUid()));
            $this->userRepository->removeAndFlush($user);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success(sprintf('Success! %d users deleted.', $count));

        return 0;
    }
}
