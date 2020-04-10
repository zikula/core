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

namespace Zikula\ZAuthModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Validator\Constraints\ValidEmail;
use Zikula\UsersModule\Validator\Constraints\ValidUname;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;

class ZikulaZauthEditCommand extends Command
{
    protected static $defaultName = 'zikula:zauth:edit';

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $authenticationMappingRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        UserRepositoryInterface $userRepository,
        TranslatorInterface $translator,
        EncoderFactoryInterface $encoderFactory,
        ValidatorInterface $validator
    ) {
        parent::__construct();
        $this->authenticationMappingRepository = $authenticationMappingRepository;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->encoderFactory = $encoderFactory;
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'uid, uname or email')
            ->setDescription('Edit a ZAuth user mapping')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command can be used to modify the password, email or username of a user.

<info>php %command.full_name% 2</info>

This will load user uid=2 and then ask which property to set.

You can look up users by their <info>UID</info>, their <info>email address</info> or their <info>username</info>.

<info>php %command.full_name% foo@bar.com</info>

<info>php %command.full_name% fabien</info>

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');
        if (is_numeric($id)) {
            $criteria = ['uid' => (int) $id];
        } elseif (filter_var($id, FILTER_VALIDATE_EMAIL)) {
            $criteria = ['email' => $id];
        } else {
            $criteria = ['uname' => $id];
        }
        try {
            $mapping = $this->authenticationMappingRepository->findOneBy($criteria);
        } catch (\Exception $e) {
            $io->error($this->translator->trans('Found more than one user by that criteria. Try a different criteria (uid works best).'));

            return 1;
        }
        if (empty($mapping)) {
            $io->error($this->translator->trans('Found zero users by that criteria. Try a different criteria (uid works best).'));

            return 1;
        }
        $choices = [
            $this->translator->trans('password'),
            $this->translator->trans('email'),
            $this->translator->trans('username'),
        ];
        $choice = $io->choice($this->translator->trans('Value to change?'), $choices, $this->translator->trans('password'));
        $method = $this->translator->trans('password') === $choice ? 'askHidden' : 'ask';
        $value = $io->{$method}($this->translator->trans('New value for %choice%?', ['%choice%' => $choice]));
        $validators = array_combine($choices, [new ValidPassword(), new ValidEmail(), new ValidUname()]);
        $errors = $this->validator->validate($value, $validators[$choice]);
        if (0 !== count($errors)) {
            $io->error($this->translator->trans('Invalid %choice%', ['%choice%' => $choice]) . '. ' . $errors[0]->getMessage());

            return 2;
        }

        switch ($choice) {
            case 'password':
                $mapping->setPass($this->encoderFactory->getEncoder($mapping)->encodePassword($value, null));
                break;
            default:
                unset($choices[0]);
                $methods = array_combine($choices, ['setEmail', 'setUname']);
                $mapping->{$methods[$choice]}($value);
                $userEntity = $this->userRepository->findOneBy($criteria);
                $userEntity->{$methods[$choice]}($value);
                $this->userRepository->persistAndFlush($userEntity);
                break;
        }
        $this->authenticationMappingRepository->persistAndFlush($mapping);
        $io->success($this->translator->trans('The %choice% has been changed.', ['%choice%' => $choice]));

        return 0;
    }
}
