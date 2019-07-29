<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace App\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepository;
use Webauthn\PublicKeyCredentialSourceRepository;

final class ListAuthenticatorsCommand extends Command
{
    protected static $defaultName = 'app:list-authenticators';

    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $credentialSourceRepository;
    /**
     * @var PublicKeyCredentialUserEntityRepository
     */
    private $userEntityRepository;

    public function __construct(PublicKeyCredentialUserEntityRepository $userEntityRepository, PublicKeyCredentialSourceRepository $credentialSourceRepository)
    {
        parent::__construct();
        $this->credentialSourceRepository = $credentialSourceRepository;
        $this->userEntityRepository = $userEntityRepository;
    }

    protected function configure()
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $userEntity = $this->userEntityRepository->findOneByUsername($username);
        if (null === $userEntity) {
            throw new InvalidArgumentException('Unable to find the user');
        }

        $authenticators = $this->credentialSourceRepository->findAllForUserEntity($userEntity);
        dump(json_encode($authenticators));
    }
}
