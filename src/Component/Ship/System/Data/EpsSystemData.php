<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class EpsSystemData extends AbstractSystemData
{
    // eps fields
    public int $eps = 0;
    public int $maxEps = 0;

    // battery fields
    public int $maxBattery = 0;
    public int $battery = 0;
    public int $batteryCooldown = 0;
    public bool $reloadBattery = false;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(ShipSystemRepositoryInterface $shipSystemRepository)
    {
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function update(): void
    {
        $this->updateSystemData(
            ShipSystemTypeEnum::SYSTEM_EPS,
            $this,
            $this->shipSystemRepository
        );
    }

    public function getEps(): int
    {
        return $this->eps;
    }

    public function setEps(int $eps): EpsSystemData
    {
        $this->eps = $eps;
        return $this;
    }

    public function setMaxEps(int $maxEps): EpsSystemData
    {
        $this->maxEps = $maxEps;
        $this->maxBattery = (int) round($maxEps / 3);
        return $this;
    }

    public function getTheoreticalMaxEps(): int
    {
        return $this->maxEps;
    }

    /**
     * proportional to eps system status
     */
    public function getMaxEps(): int
    {
        if (!$this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)) {
            return $this->maxEps;
        }

        return (int) (ceil($this->maxEps
            * $this->ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_EPS)->getStatus() / 100));
    }

    public function getMaxBattery(): int
    {
        return $this->maxBattery;
    }

    public function getBattery(): int
    {
        return $this->battery;
    }

    public function setBattery(int $battery): EpsSystemData
    {
        $this->battery = $battery;
        return $this;
    }

    public function getBatteryCooldown(): int
    {
        return $this->batteryCooldown;
    }

    public function setBatteryCooldown(int $batteryCooldown): EpsSystemData
    {
        $this->batteryCooldown = $batteryCooldown;
        return $this;
    }

    public function reloadBattery(): bool
    {
        return $this->reloadBattery;
    }

    public function setReloadBattery(bool $reloadBattery): EpsSystemData
    {
        $this->reloadBattery = $reloadBattery;
        return $this;
    }

    public function isEBattUseable(): bool
    {
        return $this->batteryCooldown < time();
    }

    public function getEpsStatusBar(): string
    {
        return $this->getTalStatusBar(
            _('Energie'),
            $this->getEps(),
            $this->getMaxEps(),
            StatusBarColorEnum::STATUSBAR_YELLOW
        )
            ->render();
    }

    public function getEpsStatusBarBig(): string
    {
        return $this->getTalStatusBar(
            _('Energie'),
            $this->getEps(),
            $this->getMaxEps(),
            StatusBarColorEnum::STATUSBAR_YELLOW
        )
            ->setSizeModifier(1.6)
            ->render();
    }
}
