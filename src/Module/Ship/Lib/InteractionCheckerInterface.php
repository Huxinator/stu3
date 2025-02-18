<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface InteractionCheckerInterface
{
    public function checkPosition(ShipInterface $shipa, ShipInterface $shipb): bool;

    public function checkColonyPosition(ColonyInterface $col, ShipInterface $ship): bool;

    public static function canInteractWith(ShipInterface $ship, $target, GameControllerInterface $game, bool $colony = false, bool $doCloakCheck = false): bool;
}
