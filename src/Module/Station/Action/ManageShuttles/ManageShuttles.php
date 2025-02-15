<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ManageShuttles;

use request;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Module\Colony\Lib\ShuttleManagementItem;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Station\View\ShowShipManagement\ShowShipManagement;
use Stu\Orm\Entity\ShipInterface;

final class ManageShuttles implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MANAGE_STATION_SHUTTLES';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipStorageManagerInterface $shipStorageManager;

    private CommodityRepositoryInterface $commodityRepository;

    private InteractionCheckerInterface $interactionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipStorageManagerInterface $shipStorageManager,
        CommodityRepositoryInterface $commodityRepository,
        InteractionCheckerInterface $interactionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipStorageManager = $shipStorageManager;
        $this->commodityRepository = $commodityRepository;
        $this->interactionChecker = $interactionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $stationId = request::indInt('id');
        $shipId = request::indInt('sid');

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $stationId,
            $userId,
            $shipId
        );

        $wrapper = $shipArray[$stationId];
        $station = $wrapper->get();

        $targetWrapper = $shipArray[$shipId];
        if ($targetWrapper === null) {
            return;
        }
        $ship = $targetWrapper->get();

        if (!$this->interactionChecker->checkPosition($station, $ship)) {
            return;
        }

        $isForeignShip = $userId !== $ship->getUser()->getId();

        $commodities = request::postArray('shuttles');
        $shuttlecount = request::postArrayFatal('shuttlecount');

        if (array_sum($shuttlecount) > $ship->getRump()->getShuttleSlots()) {
            return;
        }

        $shuttles = [];
        $currentlyStored = 0;

        foreach ($ship->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                $smi = new ShuttleManagementItem($stor->getCommodity());
                $smi->setCurrentLoad($stor->getAmount());
                $currentlyStored += $stor->getAmount();

                $shuttles[$stor->getCommodity()->getId()] = $smi;
            }
        }

        foreach ($station->getStorage() as $stor) {
            if ($stor->getCommodity()->isShuttle()) {
                if (array_key_exists($stor->getCommodity()->getId(), $shuttles)) {
                    $smi = $shuttles[$stor->getCommodity()->getId()];
                    $smi->setColonyLoad($stor->getAmount());
                } else {
                    $smi = new ShuttleManagementItem($stor->getCommodity());
                    $smi->setColonyLoad($stor->getAmount());

                    $shuttles[$stor->getCommodity()->getId()] = $smi;
                }
            }
        }

        $msgArray = [];

        foreach ($commodities as $commodityId) {
            $wantedCount = (int)$shuttlecount[$commodityId];

            $smi = $shuttles[(int)$commodityId];

            if ($wantedCount > $smi->getMaxUnits()) {
                continue;
            }

            if ($isForeignShip && $smi->getCurrentLoad() > $wantedCount) {
                continue;
            }

            if ($smi->getCurrentLoad() !== $wantedCount) {
                $msgArray[] = $this->transferShuttles(
                    (int)$commodityId,
                    $smi->getCurrentLoad(),
                    $wantedCount,
                    $ship,
                    $station
                );
            }
        }

        $game->addInformationMerge($msgArray);

        if ($isForeignShip && !empty($msgArray)) {
            $pm = sprintf(
                _('Die %s %s des Spielers %s transferiert Shuttles in Sektor %d|%d') . "\n",
                $station->getRump()->getName(),
                $station->getName(),
                $station->getUser()->getName(),
                $ship->getPosX(),
                $ship->getPosY()
            );
            foreach ($msgArray as $value) {
                $pm .= $value . "\n";
            }
            $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId());
            $this->privateMessageSender->send(
                $userId,
                $ship->getUser()->getId(),
                $pm,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }
    }

    private function transferShuttles(
        int $commodityId,
        int $current,
        int $wanted,
        ShipInterface $ship,
        ShipInterface $station
    ): string {
        $commodity = $this->commodityRepository->find($commodityId);
        $diff = abs($wanted - $current);

        if ($current < $wanted) {
            $this->shipStorageManager->upperStorage($ship, $commodity, $diff);
            $this->shipStorageManager->lowerStorage($station, $commodity, $diff);

            $msg = _('Es wurden %d %s zur %s transferiert');
        } else {
            $this->shipStorageManager->lowerStorage($ship, $commodity, $diff);
            $this->shipStorageManager->upperStorage($station, $commodity, $diff);

            $msg = _('Es wurden %d %s von der %s transferiert');
        }

        return sprintf(
            $msg,
            $diff,
            $commodity->getName(),
            $ship->getName()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
