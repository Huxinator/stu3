<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class EnergyWeaponShipSystemTest extends StuTestCase
{
    /**
     * @var null|EnergyWeaponShipSystem
     */
    private $system;

    private ShipInterface $ship;
    private ShipWrapperInterface $wrapper;

    public function setUp(): void
    {
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->system = new EnergyWeaponShipSystem();
    }

    public function testCheckActivationConditionsReturnFalseIfCloaked(): void
    {
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->ship, $reason)
        );
        $this->assertEquals('die Tarnung aktiviert ist', $reason);
    }

    public function testCheckActivationConditionsReturnFalseIfAlertGreen(): void
    {
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $reason = null;
        $this->assertFalse(
            $this->system->checkActivationConditions($this->ship, $reason)
        );
        $this->assertEquals('die Alarmstufe Grün ist', $reason);
    }

    public function testCheckActivationConditionsReturnTrueIfActivateable(): void
    {
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $this->ship->shouldReceive('isAlertGreen')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $reason = null;
        $this->assertTrue(
            $this->system->checkActivationConditions($this->ship, $reason)
        );
        $this->assertNull($reason);
    }

    public function testGetEnergyUsageForActivationReturnsValus(): void
    {
        $this->assertSame(
            1,
            $this->system->getEnergyUsageForActivation()
        );
    }

    public function testActivateActivates(): void
    {
        $managerMock = $this->mock(ShipSystemManagerInterface::class);
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_ON)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->system->activate($this->wrapper, $managerMock);
    }

    public function testDeactivateDeactivates(): void
    {
        $system = $this->mock(ShipSystemInterface::class);

        $this->ship->shouldReceive('getShipSystem')
            ->with(ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andReturn($system);
        $system->shouldReceive('setMode')
            ->with(ShipSystemModeEnum::MODE_OFF)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->system->deactivate($this->wrapper);
    }
}
