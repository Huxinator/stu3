<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Orm\Entity\ShipInterface;

final class ComputerShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function activate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_COMPUTER)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
    }
    
    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_COMPUTER)->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    public function getEnergyConsumption(): int
    {
        return 0;
    }

    public function getPriority(): int
    {
        return 2;
    }

    public function getDefaultMode(): int
    {
        return ShipSystemModeEnum::MODE_ALWAYS_ON;
    }
}
