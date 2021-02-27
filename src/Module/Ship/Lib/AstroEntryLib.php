<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class AstroEntryLib implements AstroEntryLibInterface
{
    private AstroEntryRepositoryInterface $astroEntryRepository;

    public function __construct(
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function cancelAstroFinalizing(ShipInterface $ship): void
    {
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $ship->setAstroStartTurn(null);

        $entry = $this->astroEntryRepository->getByUserAndSystem($ship->getUserId(), $ship->getSystemsId());

        $entry->setState(AstronomicalMappingEnum::MEASURED);
        $this->astroEntryRepository->save($entry);
    }

    public function finish(ShipInterface $ship): void
    {
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $ship->setAstroStartTurn(null);

        $entry = $this->astroEntryRepository->getByUserAndSystem($ship->getUserId(), $ship->getSystemsId());

        $entry->setState(AstronomicalMappingEnum::DONE);
        $this->astroEntryRepository->save($entry);
    }
}
