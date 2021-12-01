<?php

namespace Stu\Component\Player\Register;

use Stu\Component\Player\Register\Exception\RegistrationException;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Entity\UserInterface;

interface PlayerCreatorInterface
{
    /**
     * @throws RegistrationException
     */
    public function create(
        string $loginName,
        string $emailAddress,
        int $mobileAddress,
        FactionInterface $faction,
        string $token
    ): void;

    public function createPlayer(
        string $loginName,
        string $emailAddress,
        int $mobileAddress,
        FactionInterface $faction
    ): UserInterface;
}
