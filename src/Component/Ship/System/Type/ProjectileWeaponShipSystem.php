<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class ProjectileWeaponShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_TORPEDO;
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getTorpedoCount() === 0) {
            $reason = _('keine Torpedos vorhanden sind');
            return false;
        }

        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($ship->isAlertGreen()) {
            $reason = _('die Alarmstufe Grün ist');
            return false;
        }

        return true;
    }
}
