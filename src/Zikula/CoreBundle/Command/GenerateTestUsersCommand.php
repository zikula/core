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

namespace Zikula\Bundle\CoreBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Symfony\Component\String\s;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\ZAuthModule\ZAuthConstant;

class GenerateTestUsersCommand extends Command
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var \DateTime
     */
    private $nowUTC;

    /**
     * @var \DateTime
     */
    private $startUTC;

    /**
     * @var \DateTime
     */
    private $regDate;

    /**
     * @var bool
     */
    private $range = false;

    /**
     * @var string
     */
    private $active;

    /**
     * @var int
     */
    private $verified;

    protected static $defaultName = 'zikula:users:generate';

    public function __construct(
        Connection $connection
    ) {
        parent::__construct();
        $this->conn = $connection;
        $utcTZ = new \DateTimeZone('UTC');
        $this->nowUTC = new \DateTime('now', $utcTZ);
        $this->startUTC = new \DateTime('1970-01-01 00:00:00', $utcTZ);
    }

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('amount', InputArgument::REQUIRED, 'The number of users to create'),
            ])
            ->addOption(
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'All the users are A=active, I=inactive, P=all users pending, M=all users marked for deletion, R=random choice A|I|P|M',
                'A'
            )
            ->addOption(
                'verified',
                null,
                InputOption::VALUE_REQUIRED,
                'All the user emails marked as 1=verified, 0=unverified, 2=random choice 0|1',
                1
            )
            ->addOption(
                'regdate',
                null,
                InputOption::VALUE_REQUIRED,
                'Registration date in format YYYYMMDD or >YYYYMMDD if random dates between provided date and now'
            )
            ->setDescription('Generates users for testing purposes')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command generates users in order to fill a database for testing purposes.
These users will not be able to login. The users are placed into a newly created group, not the standard users group.

<info>php %command.full_name% 1000</info>

This will generate 1000 randomly named users using all the default values.

Options:
<info>--active</info> A|I|P|M|R (default: A) A=all users active, I=all users inactive, P=all users pending, M=all users marked for deletion R=random assignment A|I|P|M

<info>--verified</info> 0|1|2 (default: 1) 1=all user emails verified, 0=all user emails unverified, 2=random assignment 0|1

<info>--regdate</info> YYYYMMDD or >YYYYMMDD if random dates between provided date and now

<info>php %command.full_name% 1000 --active=I --verified=2 --regdate='>20200101'</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $amount = (int) abs($input->getArgument('amount'));
        $key = bin2hex(random_bytes(3));
        $groupId = $this->createGroup($key);
        $this->active = (string) $input->getOption('active');
        $this->verified = in_array((int) $input->getOption('verified'), [0, 1, 2]) ? (int) $input->getOption('verified') : 1;
        $regDate = $input->getOption('regdate') ?? $this->nowUTC;
        $this->range = is_string($regDate) && '>' === $regDate[0];
        $this->regDate = $this->range ? s($regDate)->slice(1)->__toString() : $regDate;

        $io->title('User generation utility');
        $io->text('Generating users...');
        $io->progressStart($amount);

        for ($i = 1; $i <= $amount; $i++) {
            $uname = 'user' . $key . $i;
            $this->insertUser($uname);
            $uid = (int) $this->conn->lastInsertId();
            $this->insertAttributes($uid);
            $this->insertMapping($uid, $uname);
            $this->insertGroup($uid, $groupId);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('User generation complete!');
        $io->text(sprintf('%d users created (group name: <info>group%s</info>).', $amount, $key));

        return Command::SUCCESS;
    }

    private function configureActivatedStatus(string $value): int
    {
        $statuses = [
            'A' => UsersConstant::ACTIVATED_ACTIVE,
            'I' => UsersConstant::ACTIVATED_INACTIVE,
            'P' => UsersConstant::ACTIVATED_PENDING_REG,
            'M' => UsersConstant::ACTIVATED_PENDING_DELETE
        ];
        if ('R' === $value) {
            return array_rand(array_flip($statuses));
        }

        return array_key_exists($value, $statuses) ? $statuses[$value] : UsersConstant::ACTIVATED_ACTIVE;
    }

    private function configureRegDate(): \DateTime
    {
        if ($this->regDate instanceof \DateTime) {
            return $this->regDate;
        }
        $utcTz = new \DateTimeZone('UTC');
        $regDate = \DateTime::createFromFormat('Ymd', $this->regDate, $utcTz);
        if (!$this->range) {
            return $regDate;
        }
        $randTimeStamp = mt_rand($regDate->getTimestamp(), $this->nowUTC->getTimestamp());

        return \DateTime::createFromFormat("U", "${randTimeStamp}", $utcTz);
    }

    private function insertUser(string $uname): void
    {
        $types = [\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_INT, 'datetime', \PDO::PARAM_INT, 'datetime', 'datetime', \PDO::PARAM_STR, \PDO::PARAM_STR];
        try {
            $this->conn->insert('users', [
                'uname' => $uname,
                'email' => $uname . '@example.com',
                'activated' => $activated = $this->configureActivatedStatus($this->active),
                'approved_date' => UsersConstant::ACTIVATED_ACTIVE === $activated ? $this->nowUTC : $this->startUTC,
                'approved_by' => UsersConstant::ACTIVATED_ACTIVE === $activated ? 2 : 0,
                'user_regdate' => $this->configureRegDate(),
                'lastlogin' => $this->startUTC,
                'tz' => '',
                'locale' => ''
            ], $types);
        } catch (DBALException $exception) {
            // do nothing?
        }
    }

    private function insertAttributes(int $uid): void
    {
        $types = [\PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_STR];
        try {
            $this->conn->insert('users_attributes', [
                'name' => UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY,
                'user_id' => $uid,
                'value' => ZAuthConstant::AUTHENTICATION_METHOD_EITHER,
            ], $types);
        } catch (DBALException $exception) {
            // do nothing?
        }
    }

    private function insertMapping(int $uid, string $uname): void
    {
        $types = [\PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_STR];
        try {
            $this->conn->insert('zauth_authentication_mapping', [
                'method' => ZAuthConstant::AUTHENTICATION_METHOD_EITHER,
                'uid' => $uid,
                'uname' => $uname,
                'email' => $uname . '@example.com',
                'verifiedEmail' => 2 === $this->verified ? random_int(0, 1) : $this->verified,
                'pass' => '',
            ], $types);
        } catch (DBALException $exception) {
            // do nothing?
        }
    }

    private function createGroup(string $key): int
    {
        $types = [\PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_INT];
        try {
            $this->conn->insert('groups', [
                'name' => 'group' . $key,
                'gtype' => 0,
                'description' => 'temp group for testing',
                'state' => 0,
                'nbumax' => 0,
            ], $types);
        } catch (DBALException $exception) {
            // do nothing?
        }

        return (int) $this->conn->lastInsertId();
    }

    private function insertGroup(int $uid, int $gid): void
    {
        $types = [\PDO::PARAM_INT, \PDO::PARAM_INT];
        try {
            $this->conn->insert('group_membership', [
                'uid' => $uid,
                'gid' => $gid,
            ], $types);
        } catch (DBALException $exception) {
            // do nothing?
        }
    }
}
