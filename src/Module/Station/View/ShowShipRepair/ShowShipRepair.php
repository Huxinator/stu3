<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipRepair;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ShowShipRepair implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    private ShipLoaderInterface $shipLoader;

    private StationUtilityInterface $stationUtility;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        StationUtilityInterface $stationUtility,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->stationUtility = $stationUtility;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$this->stationUtility->canRepairShips($station)) {
            return;
        }

        $repairableShips = [];
        foreach ($station->getDockedShips() as $ship) {
            $wrapper = $this->shipWrapperFactory->wrapShip($ship);
            if (
                !$wrapper->canBeRepaired() || $ship->isUnderRepair()
            ) {
                continue;
            }
            $repairableShips[$ship->getId()] = $wrapper;
        }

        $game->appendNavigationPart(
            'station.php',
            _('Stationen')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%d',
                ShowShip::VIEW_IDENTIFIER,
                $station->getId()
            ),
            $station->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                '?id=%s&%d=1',
                $station->getId(),
                static::VIEW_IDENTIFIER
            ),
            _('Schiffreparatur')
        );
        $game->setPagetitle(_('Schiffreparatur'));
        $game->setTemplateFile('html/station_shiprepair.xhtml');

        $game->setTemplateVar('REPAIRABLE_SHIP_LIST', $repairableShips);
        $game->setTemplateVar('STATION', $station);
    }
}
