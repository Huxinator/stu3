<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\StuTestCase;

class ManagerProviderStationTest extends StuTestCase
{
    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&CrewCreatorInterface */
    private MockInterface $crewCreator;

    /** @var MockInterface&ShipCrewCalculatorInterface */
    private MockInterface $shipCrewCalculator;

    /** @var MockInterface&ShipCrewRepositoryInterface */
    private MockInterface $shipCrewRepository;

    /** @var MockInterface&ShipStorageManagerInterface */
    private MockInterface $shipStorageManager;

    private ShipInterface $station;

    private ManagerProviderInterface $subject;

    protected function setUp(): void
    {
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->crewCreator = $this->mock(CrewCreatorInterface::class);
        $this->shipCrewCalculator = $this->mock(ShipCrewCalculatorInterface::class);
        $this->shipCrewRepository = $this->mock(ShipCrewRepositoryInterface::class);
        $this->shipStorageManager = $this->mock(ShipStorageManagerInterface::class);

        $this->station = $this->mock(ShipInterface::class);

        $this->subject = new ManagerProviderStation(
            $this->wrapper,
            $this->crewCreator,
            $this->shipCrewCalculator,
            $this->shipCrewRepository,
            $this->shipStorageManager
        );
    }

    public function testGetUser(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->wrapper->shouldReceive('get->getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->assertSame($user, $this->subject->getUser());
    }

    public function testGetEpsExpectZeroWhenNoEpsSystem(): void
    {
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->assertEquals(0, $this->subject->getEps());
    }

    public function testGetEpsExpectCorrectValueWhenEpsSystemExistent(): void
    {
        $eps = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $eps->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->assertEquals(42, $this->subject->getEps());
    }

    public function testLowerEpsExpectErrorWhenNoEpsInstalled(): void
    {
        static::expectExceptionMessage('can not lower eps without eps system');
        static::expectException(RuntimeException::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->lowerEps(5);
    }

    public function testLowerEpsExpectLoweringIfEpsInstalled(): void
    {
        $eps = $this->mock(EpsSystemData::class);

        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);

        $eps->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $eps->shouldReceive('setEps')
            ->with(37)
            ->once()
            ->andReturn($eps);
        $eps->shouldReceive('update')
            ->with()
            ->once();

        $this->assertSame($this->subject, $this->subject->lowerEps(5));
    }

    public function testGetName(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->station->shouldReceive('getRump->getName')
            ->withNoArgs()
            ->once()
            ->andReturn('rumpname');
        $this->station->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('stationname');

        $this->assertEquals('rumpname stationname', $this->subject->getName());
    }

    public function testGetSectorString(): void
    {
        $this->wrapper->shouldReceive('get->getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('foo');

        $this->assertEquals('foo', $this->subject->getSectorString());
    }

    public function testGetFreeCrewAmount(): void
    {
        $this->wrapper->shouldReceive('get->getExcessCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(123);

        $this->assertEquals(123, $this->subject->getFreeCrewAmount());
    }

    public function testCreateShipCrew(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $this->crewCreator->shouldReceive('createShipCrew')
            ->with($ship, null, $this->station)
            ->once()
            ->andReturn(123);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->subject->createShipCrew($ship);
    }

    public function testIsAbleToStoreCrewExpectFalseWhenSpaceInsufficient(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->station->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(16);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByShip')
            ->with($this->station)
            ->once()
            ->andReturn(20);

        $this->assertFalse($this->subject->isAbleToStoreCrew(5));
    }

    public function testIsAbleToStoreCrewExpectTrueWhenSpaceSufficient(): void
    {
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->station->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(15);

        $this->shipCrewCalculator->shouldReceive('getMaxCrewCountByShip')
            ->with($this->station)
            ->once()
            ->andReturn(20);

        $this->assertTrue($this->subject->isAbleToStoreCrew(5));
    }

    public function testAddCrewAssignments(): void
    {
        $crewAssignments = new ArrayCollection();
        $crewAssignment = $this->mock(ShipCrewInterface::class);
        $crewAssignments->add($crewAssignment);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $crewAssignment->shouldReceive('setShip')
            ->with($this->station)
            ->once();
        $crewAssignment->shouldReceive('setSlot')
            ->with(null)
            ->once();

        $this->station->shouldReceive('getCrewlist->add')
            ->with($crewAssignment)
            ->once();

        $this->shipCrewRepository->shouldReceive('save')
            ->with($crewAssignment)
            ->once();

        $this->subject->addCrewAssignments($crewAssignments);
    }

    public function testGetStorage(): void
    {
        $storage = $this->mock(Collection::class);

        $this->wrapper->shouldReceive('get->getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);

        $this->assertSame($storage, $this->subject->getStorage());
    }

    public function testUpperStorage(): void
    {
        $commodity = $this->mock(CommodityInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->shipStorageManager->shouldReceive('upperStorage')
            ->with($this->station, $commodity, 5)
            ->once();

        $this->subject->upperStorage($commodity, 5);
    }

    public function testLowerStorage(): void
    {
        $commodity = $this->mock(CommodityInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->station);

        $this->shipStorageManager->shouldReceive('lowerStorage')
            ->with($this->station, $commodity, 5)
            ->once();

        $this->subject->lowerStorage($commodity, 5);
    }
}
