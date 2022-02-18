<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BeamFrom;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class BeamFrom implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BEAMFROM';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipLoaderInterface $shipLoader
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($colony->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        $target = $this->shipLoader->find(request::postIntFatal('target'));
        if ($target === null) {
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if (
            $target->getShieldState()
            && $target->getUser()->getId() != $userId
        ) {
            $game->addInformationf(_('Die %s hat die Schilde aktiviert'), $target->getName());
            return;
        }
        if (
            $target->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_BEAM_BLOCKER)
            && $target->getUser()->getId() != $userId
        ) {
            $game->addInformation(sprintf(_('Die %s hat einen Beamblocker aktiviert. Transfer nicht möglich.'), $target->getName()));
            return;
        }
        if (!$colony->storagePlaceLeft()) {
            $game->addInformationf(_('Der Lagerraum der %s ist voll'), $colony->getName());
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');

        $targetStorage = $target->getStorage();

        if ($targetStorage->isEmpty()) {
            $game->addInformation(_('Keine Waren zum Beamen vorhanden'));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_('Es wurde keine Waren zum Beamen ausgewählt'));
            return;
        }
        if ($target->isOwnedByCurrentUser()) {
            $link = "ship.php?SHOW_SHIP=1&id=" . $target->getId();

            $game->addInformationfWithLink(
                _('Die Kolonie %s hat folgende Waren von der %s transferiert'),
                $link,
                $colony->getName(),
                $target->getName()
            );
        } else {
            $game->addInformationf(
                _('Die Kolonie %s hat folgende Waren von der %s transferiert'),
                $colony->getName(),
                $target->getName()
            );
        }
        foreach ($goods as $key => $value) {
            $commodityId = (int) $value;
            if ($colony->getEps() < 1) {
                break;
            }
            if ($colony->getStorageSum() >= $colony->getMaxStorage()) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            $count = $gcount[$key];

            $storage = $targetStorage[$commodityId] ?? null;
            if ($storage === null) {
                continue;
            }

            $commodity = $storage->getCommodity();

            if (!$commodity->isBeamable()) {
                $game->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
                continue;
            }
            if ($count == "m") {
                $count = $storage->getAmount();
            } else {
                $count = (int) $count;
            }
            if ($count < 1) {
                continue;
            }
            $count = min($count, $storage->getAmount());

            $transferAmount = $storage->getCommodity()->getTransferCount() * $colony->getBeamFactor();

            if (ceil($count / $transferAmount) > $colony->getEps()) {
                $count = $colony->getEps() * $transferAmount;
            }
            if ($colony->getStorageSum() + $count > $colony->getMaxStorage()) {
                $count = $colony->getMaxStorage() - $colony->getStorageSum();
            }

            $eps_usage = ceil($count / $transferAmount);
            $game->addInformationf(
                _('%d %s (Energieverbrauch: %d)'),
                $count,
                $commodity->getName(),
                $eps_usage
            );

            $this->shipStorageManager->lowerStorage($target, $commodity, $count);
            $this->colonyStorageManager->upperStorage($colony, $commodity, $count);

            $colony->lowerEps((int)ceil($count / $transferAmount));
        }
        $game->sendInformation(
            $target->getUser()->getId(),
            $userId,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId())
        );

        $this->colonyRepository->save($colony);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
