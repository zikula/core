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

namespace Zikula\ZAuthModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;

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

    public function __construct(
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        UserRepositoryInterface $userRepository,
        TranslatorInterface $translator,
        EncoderFactoryInterface $encoderFactory
    ) {
        parent::__construct();
        $this->authenticationMappingRepository = $authenticationMappingRepository;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->encoderFactory = $encoderFactory;
    }

    protected function configure()
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'uid, uname or email')
            ->setDescription('Edit a ZAuth user mapping')
        ;
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
        $choice = $io->choice($this->translator->trans('Value to change?'), [
            $this->translator->trans('password'),
            $this->translator->trans('email'),
            $this->translator->trans('username'),
        ], $this->translator->trans('password'));

        switch ($choice) {
            case 'password':
                $value = $io->askHidden($this->translator->trans('New value for %choice%?', ['%choice%' => $choice]));
                $mapping->setPass($this->encoderFactory->getEncoder($mapping)->encodePassword($value, null));
                break;
            default:
                $value = $io->ask($this->translator->trans('New value for %choice%?', ['%choice%' => $choice]));
                $methods = ['email' => 'setEmail', 'username' => 'setUname'];
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
