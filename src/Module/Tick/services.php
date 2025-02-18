<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Colony\ColonyTick;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManager;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Module\Tick\Lock\LockManager;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunnerFactory;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunnerFactoryInterface;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\Ship\ShipTick;
use Stu\Module\Tick\Ship\ShipTickInterface;
use Stu\Module\Tick\Ship\ShipTickManager;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;
use Stu\Module\Tick\Ship\ShipTickRunner;
use function DI\autowire;
use function DI\get;

return [
    ColonyTickInterface::class => autowire(ColonyTick::class),
    ColonyTickManagerInterface::class => autowire(ColonyTickManager::class),
    ShipTickInterface::class => autowire(ShipTick::class),
    ShipTickManagerInterface::class => autowire(ShipTickManager::class),
    TickManagerInterface::class => autowire(TickManager::class),
    LockManagerInterface::class => autowire(LockManager::class),
    'process_tick_handler' => [
        autowire(Process\FinishBuildJobs::class),
        autowire(Process\FinishShipBuildJobs::class),
        autowire(Process\FinishTerraformingJobs::class),
        autowire(Process\ShieldRegeneration::class),
        autowire(Process\RepairTaskJobs::class),
        autowire(Process\FinishTholianWebs::class)
    ],
    MaintenanceTickRunnerFactoryInterface::class => autowire(MaintenanceTickRunnerFactory::class),
    MaintenanceTickRunner::class => fn (ContainerInterface $dic): TickRunnerInterface => $dic
        ->get(MaintenanceTickRunnerFactoryInterface::class)
        ->createMaintenanceTickRunner($dic->get('maintenance_handler')),
    ProcessTickRunner::class => autowire(ProcessTickRunner::class)
        ->constructorParameter(
            'handlerList',
            get('process_tick_handler')
        ),
    ShipTickRunner::class => autowire(),
];
