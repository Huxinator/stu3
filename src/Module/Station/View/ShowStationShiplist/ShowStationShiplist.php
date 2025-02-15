<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationShiplist;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShowStationShiplist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_SHIPLIST';

    private ShipLoaderInterface $shipLoader;

    private ShowStationShiplistRequestInterface $showStationShiplistRequest;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShowStationShiplistRequestInterface $showStationShiplistRequest,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->showStationShiplistRequest = $showStationShiplistRequest;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            $this->showStationShiplistRequest->getStationId(),
            $userId
        );

        if (!$station->isBase()) {
            return;
        }

        $shipList = array_map(
            function (ShipInterface $ship): ShipWrapperInterface {
                return $this->shipWrapperFactory->wrapShip($ship);
            },
            $station->getDockedShips()->toArray()
        );

        $game->setPageTitle(_('Angedockte Schiffe'));
        $game->setMacroInAjaxWindow('html/stationmacros.xhtml/shiplist');
        $game->setTemplateVar('SHIP', $station);
        $game->setTemplateVar('WRAPPERS', $shipList);
        $game->setTemplateVar('CAN_UNDOCK', true);
    }
}
