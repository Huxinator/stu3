<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowDockingPrivilegesConfig;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Station\Lib\DockingPrivilegeItem;
use Stu\Module\Station\Lib\StationUiFactoryInterface;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class ShowDockingPrivilegesConfig implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DOCKPRIVILEGE_CONFIG';

    private ShipLoaderInterface $shipLoader;

    private AllianceRepositoryInterface $allianceRepository;

    private StationUiFactoryInterface $stationUiFactory;

    public function __construct(
        AllianceRepositoryInterface $allianceRepository,
        StationUiFactoryInterface $stationUiFactory,
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
        $this->allianceRepository = $allianceRepository;
        $this->stationUiFactory = $stationUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(_('Dockrechte'));
        $game->setMacroInAjaxWindow('html/stationmacros.xhtml/dockprivileges');
        $game->setTemplateVar('ALLIANCE_LIST', $this->allianceRepository->findAllOrdered());
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'DOCKING_PRIVILEGES',
            $ship->getDockPrivileges()->map(
                fn (DockingPrivilegeInterface $dockingPrivilege): DockingPrivilegeItem =>
                   $this->stationUiFactory->createDockingPrivilegeItem($dockingPrivilege)
            )
        );
    }
}
