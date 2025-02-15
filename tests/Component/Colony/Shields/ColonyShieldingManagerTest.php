<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Shields;

use Mockery\MockInterface;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class ColonyShieldingManagerTest extends StuTestCase
{
    /** @var MockInterface&PlanetFieldRepositoryInterface */
    private MockInterface $planetFieldRepository;

    /** @var MockInterface&ColonyFunctionManagerInterface */
    private MockInterface $colonyFunctionManager;

    /** @var MockInterface&ColonyInterface */
    private MockInterface $colony;

    private ColonyShieldingManager $subject;

    protected function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->colonyFunctionManager = $this->mock(ColonyFunctionManagerInterface::class);
        $this->colony = $this->mock(ColonyInterface::class);

        $this->subject = new ColonyShieldingManager(
            $this->planetFieldRepository,
            $this->colonyFunctionManager,
            $this->colony
        );
    }

    public function testHasShieldingReturnsValue(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasFunction')
            ->with(
                $this->colony,
                BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
            )
            ->once()
            ->andReturnTrue();

        static::assertTrue(
            $this->subject->hasShielding()
        );
    }

    public function testGetMaxShieldingReturnsValue(): void
    {
        $colonyId = 666;
        $value = 42;

        $this->colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyId);

        $this->planetFieldRepository->shouldReceive('getMaxShieldsOfColony')
            ->with($colonyId)
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->subject->getMaxShielding()
        );
    }

    public function testIsShieldingEnabledReturnsFalseIfNotActiveShieldGeneratorExists(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with(
                $this->colony,
                BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
            )
            ->once()
            ->andReturnFalse();

        static::assertFalse(
            $this->subject->isShieldingEnabled()
        );
    }

    public function testIsShieldingEnabledReturnsFalseIfShieldIsNotLoaded(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with(
                $this->colony,
                BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
            )
            ->once()
            ->andReturnTrue();

        $this->colony->shouldReceive('getShields')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        static::assertFalse(
            $this->subject->isShieldingEnabled()
        );
    }

    public function testIsShieldingEnabledReturnsTrueIfActive(): void
    {
        $this->colonyFunctionManager->shouldReceive('hasActiveFunction')
            ->with(
                $this->colony,
                BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
            )
            ->once()
            ->andReturnTrue();

        $this->colony->shouldReceive('getShields')
            ->withNoArgs()
            ->once()
            ->andReturn(52);

        static::assertTrue(
            $this->subject->isShieldingEnabled()
        );
    }
}
