<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Selfrepair;

use request;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class Selfrepair implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SELF_REPAIR';

    private ShipLoaderInterface $shipLoader;

    private RepairUtilInterface $repairUtil;

    private ShipRepositoryInterface $shipRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        RepairUtilInterface $repairUtil,
        ShipRepositoryInterface $shipRepository,
        ShipStorageManagerInterface $shipStorageManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->repairUtil = $repairUtil;
        $this->shipRepository = $shipRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(request::postIntFatal('id'), $userId);

        if (!$ship->isAlertGreen()) {
            return;
        }

        $repairType = request::postInt('partschoice');
        $systemType = request::postInt('sid');

        if (!$repairType) {
            $game->addInformation(_('Es muss ausgewählt werden, welche Teile verwenden werden sollen.'));
        }

        if (!$systemType) {
            $game->addInformation(_('Es muss ausgewählt werden, welches System repariert werden soll.'));
        }

        if ($repairType < RepairTaskEnum::SPARE_PARTS_ONLY || $repairType > RepairTaskEnum::BOTH) {
            return;
        }

        $repairOptions = $this->repairUtil->determineRepairOptions($ship);
        if (!array_key_exists($systemType, $repairOptions)) {
            return;
        }

        $isInstantRepair = request::postString('instantrepair');

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->isUnderRepair()) {
            $game->addInformation(_('Das Schiff wird bereits repariert.'));
            return;
        }

        if ($ship->getState() == ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $game->addInformation(_('Das Schiff kartographiert derzeit und kann daher nicht repariert werden.'));
            return;
        }

        $neededSparePartCount = (int) ($ship->getMaxHull() / 150);


        if (!$isInstantRepair) {
            if (!$this->checkForSpareParts($ship, $neededSparePartCount, $repairType, $game)) {
                return;
            }

            $ship->setState(ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE);

            $freeEngineerCount = $this->repairUtil->determineFreeEngineerCount($ship);
            $duration = RepairTaskEnum::STANDARD_REPAIR_DURATION * (1 - $freeEngineerCount / 10);

            $this->consumeCommodities($ship, $repairType, $neededSparePartCount, $game);
            $this->repairUtil->createRepairTask($ship, $systemType, $repairType, time() + (int) $duration);
            $game->addInformationf(_('Das Schiffssystem %s wird repariert. Fertigstellung %s'), ShipSystemTypeEnum::getDescription($systemType), date("d.m.Y H:i", (time() + (int) $duration)));
        } else {
            if (!$this->checkForSpareParts($ship, 3 * $neededSparePartCount, $repairType, $game)) {
                return;
            }

            $this->consumeCommodities($ship, $repairType, 3 * $neededSparePartCount, $game);
            $healingPercentage = $this->repairUtil->determineHealingPercentage($repairType);
            $isSuccess = $this->repairUtil->instantSelfRepair($ship, $systemType, $healingPercentage);

            if ($isSuccess) {
                $game->addInformationf(
                    _('Die Crew hat das System %s auf %d %% reparieren können'),
                    ShipSystemTypeEnum::getDescription($systemType),
                    $healingPercentage
                );
            } else {
                $game->addInformationf(
                    _('Der Reparaturversuch des Systems %s brachte keine Besserung'),
                    ShipSystemTypeEnum::getDescription($systemType),
                    $ship->getName()
                );
            }
        }

        $this->shipRepository->save($ship);
    }

    private function checkForSpareParts(ShipInterface $ship, int $neededSparePartCount, int $repairType, GameControllerInterface $game): bool
    {
        $result = true;

        if (
            ($repairType === RepairTaskEnum::SPARE_PARTS_ONLY || $repairType === RepairTaskEnum::BOTH)
            && (!$ship->getStorage()->containsKey(CommodityTypeEnum::COMMODITY_SPARE_PART)
                || $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SPARE_PART)->getAmount() < $neededSparePartCount)
        ) {
            $game->addInformationf(_('Für die Reparatur werden %d Ersatzteile benötigt'), $neededSparePartCount);
            $result = false;
        }

        if (
            ($repairType === RepairTaskEnum::SYSTEM_COMPONENTS_ONLY || $repairType === RepairTaskEnum::BOTH)
            && (!$ship->getStorage()->containsKey(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT)
                || $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT)->getAmount() < $neededSparePartCount)
        ) {
            $game->addInformationf(_('Für die Reparatur werden %d Systemkomponenten benötigt'), $neededSparePartCount);
            $result = false;
        }

        return $result;
    }

    private function consumeCommodities(ShipInterface $ship, int $repairType, $neededSparePartCount, GameControllerInterface $game): void
    {
        if (
            $repairType === RepairTaskEnum::SPARE_PARTS_ONLY
            || $repairType === RepairTaskEnum::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SPARE_PART)->getCommodity();
            $this->shipStorageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
            $game->addInformationf(_('Für die Reparatur werden %d Ersatzteile verwendet'), $neededSparePartCount);
        }

        if (
            $repairType === RepairTaskEnum::SYSTEM_COMPONENTS_ONLY
            || $repairType === RepairTaskEnum::BOTH
        ) {
            $commodity = $ship->getStorage()->get(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT)->getCommodity();
            $this->shipStorageManager->lowerStorage($ship, $commodity, $neededSparePartCount);
            $game->addInformationf(_('Für die Reparatur werden %d Systemkomponenten verwendet'), $neededSparePartCount);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
