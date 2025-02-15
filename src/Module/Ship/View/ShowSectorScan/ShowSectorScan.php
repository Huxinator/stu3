<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowSectorScan;

use request;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Lib\SignatureWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowSectorScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SECTOR_SCAN';

    private ShipLoaderInterface $shipLoader;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private ShipRepositoryInterface $shipRepository;

    private $fadedSignaturesUncloaked = [];
    private $fadedSignaturesCloaked = [];

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->flightSignatureRepository = $flightSignatureRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true
        );
        $ship = $wrapper->get();

        $game->setPageTitle("Sektor Scan");
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/sectorscan');

        $game->setTemplateVar('ERROR', true);

        if (!$ship->getNbs()) {
            $game->addInformation("Die Nahbereichssensoren sind nicht aktiv");
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem->getEps() < 1) {
            $game->addInformation("Nicht genügend Energie vorhanden (1 benötigt)");
            return;
        }

        $epsSystem->setEps($epsSystem->getEps() - 1)->update();
        $this->shipRepository->save($ship);

        $mapField = $ship->getCurrentMapField();

        $colonyClass = $mapField->getFieldType()->getColonyClass();
        if ($colonyClass !== null) {
            $game->checkDatabaseItem($colonyClass->getDatabaseId());
        }
        if ($mapField->getFieldType()->getIsSystem()) {
            $game->checkDatabaseItem($ship->getCurrentMapField()->getSystem()->getSystemType()->getDatabaseEntryId());
        }

        $game->setTemplateVar('SIGNATURES', $this->getSignatures($mapField, $ship->getSystem() !== null, $userId));
        $game->setTemplateVar('OTHER_SIG_COUNT', empty($this->fadedSignaturesUncloaked) ? null : count($this->fadedSignaturesUncloaked));
        $game->setTemplateVar('OTHER_CLOAKED_COUNT', empty($this->fadedSignaturesCloaked) ? null : count($this->fadedSignaturesCloaked));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('ERROR', false);
    }

    private function getSignatures($field, $isSystem, $ignoreId)
    {
        $allSigs = $this->flightSignatureRepository->getVisibleSignatures($field, $isSystem, $ignoreId);

        $filteredSigs = [];

        foreach ($allSigs as $sig) {
            $id = $sig->getShipId();

            if (!array_key_exists($id, $filteredSigs)) {
                $wrapper = new SignatureWrapper($sig);

                if ($wrapper->getRump() == null) {
                    if ($sig->isCloaked()) {
                        if ($sig->getTime() > (time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED)) {
                            $this->fadedSignaturesCloaked[$id] = $id;
                        }
                    } else {
                        $this->fadedSignaturesUncloaked[$id] = $id;
                    }
                } else {
                    $filteredSigs[$id] = $wrapper;
                }
            }
        }

        return $filteredSigs;
    }
}
