<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Config\Init;
use Stu\Module\Control\GameControllerInterface;

/**
 * @deprecated Session handling should be part of the application
 */
@session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic) {
    $em = $dic->get(EntityManagerInterface::class);
    $em->beginTransaction();

    $dic->get(GameControllerInterface::class)->main(
        'notes',
        $dic->get('NOTES_ACTIONS'),
        $dic->get('NOTES_VIEWS')
    );

    $em->commit();
});
