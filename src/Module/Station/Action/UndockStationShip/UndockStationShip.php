<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\UndockStationShip;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class UndockStationShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNDOCK_SHIP';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private CancelRepairInterface $cancelRepair;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        CancelRepairInterface $cancelRepair,
        EntityManagerInterface $entityManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->cancelRepair = $cancelRepair;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $stationId = request::indInt('id');
        $targetId = request::indInt('target');

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $stationId,
            $userId,
            $targetId
        );

        $wrapper = $shipArray[$stationId];
        $station = $wrapper->get();

        $targetWrapper = $shipArray[$targetId];
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if ($target->getDockedTo() !== $station) {
            return;
        }

        if ($target->getUser() !== $game->getUser()) {

            $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    _('Die %s %s hat die %s in Sektor %s abgedockt'),
                    $station->getRump()->getName(),
                    $station->getName(),
                    $target->getName(),
                    $station->getSectorString()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                $href
            );
        }

        $this->cancelRepair->cancelRepair($target);
        $target->setDockedTo(null);
        $target->setDockedToId(null);
        $station->getDockedShips()->remove($target->getId());

        $this->shipLoader->save($target);
        $this->shipLoader->save($station);

        $this->entityManager->flush();

        $game->addInformation(sprintf(_('Die %s wurde erfolgreich abgedockt'), $target->getName()));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
