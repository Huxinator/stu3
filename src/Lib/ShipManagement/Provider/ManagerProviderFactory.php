<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

class ManagerProviderFactory implements ManagerProviderFactoryInterface
{
    private CrewCreatorInterface $crewCreator;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(
        CrewCreatorInterface $crewCreator,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        ColonyStorageManagerInterface $colonyStorageManager,
        ShipStorageManagerInterface $shipStorageManager
    ) {
        $this->crewCreator = $crewCreator;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->shipStorageManager = $shipStorageManager;
    }

    public function getManagerProviderColony(ColonyInterface $colony): ManagerProviderInterface
    {
        return new ManagerProviderColony(
            $colony,
            $this->crewCreator,
            $this->colonyLibFactory,
            $this->shipCrewRepository,
            $this->colonyStorageManager
        );
    }

    public function getManagerProviderStation(ShipWrapperInterface $wrapper): ManagerProviderInterface
    {
        return new ManagerProviderStation(
            $wrapper,
            $this->crewCreator,
            $this->shipCrewCalculator,
            $this->shipCrewRepository,
            $this->shipStorageManager
        );
    }
}
