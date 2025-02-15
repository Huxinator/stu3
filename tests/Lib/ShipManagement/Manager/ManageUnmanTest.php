<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ManageUnmanTest extends StuTestCase
{
    /** @var MockInterface&ShipSystemManagerInterface */
    private MockInterface $shipSystemManager;

    /** @var MockInterface&ShipRepositoryInterface */
    private MockInterface $shipRepository;

    /** @var MockInterface&ShipWrapperInterface */
    private MockInterface $wrapper;

    /** @var MockInterface&ShipInterface */
    private MockInterface $ship;

    /** @var MockInterface&ManagerProviderInterface */
    private MockInterface $managerProvider;

    private int $shipId = 555;
    private UserInterface $user;

    private ManageUnman $subject;

    protected function setUp(): void
    {
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->user = $this->mock(UserInterface::class);
        $this->managerProvider = $this->mock(ManagerProviderInterface::class);

        $this->subject = new ManageUnman(
            $this->shipSystemManager,
            $this->shipRepository
        );
    }

    public function testManageExpectErrorWhenValuesNotPresent(): void
    {
        static::expectExceptionMessage('value array not existent');
        static::expectException(RuntimeException::class);

        $values = ['foo' => '42'];

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNotInValues(): void
    {
        $values = ['unman' => ['5' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenForeignShip(): void
    {
        $shipOwner = $this->mock(UserInterface::class);
        $values = ['unman' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($shipOwner);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenShipEmpty(): void
    {
        $values = ['unman' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEmpty($msg);
    }

    public function testManageExpectNothingWhenNotEnoughSpaceOnProvider(): void
    {
        $values = ['unman' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('isAbleToStoreCrew')
            ->with(10)
            ->once()
            ->andReturn(false);
        $this->managerProvider->shouldReceive('getName')
            ->with()
            ->once()
            ->andReturn('providerName');

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(10);

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Nicht genügend Platz für die Crew auf der providerName'], $msg);
    }

    public function testManageExpectCrewSwapWhenEnoughSpaceOnProvider(): void
    {
        $shipCrewlist = $this->mock(Collection::class);
        $dockedShips = new ArrayCollection();
        $dockedShip = $this->mock(ShipInterface::class);
        $dockedShips->add($dockedShip);

        $values = ['unman' => ['555' => '42']];

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->managerProvider->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->managerProvider->shouldReceive('isAbleToStoreCrew')
            ->with(10)
            ->once()
            ->andReturn(true);
        $this->managerProvider->shouldReceive('addCrewAssignments')
            ->with($shipCrewlist)
            ->once();

        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->shipId);
        $this->ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('name');
        $this->ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->andReturn(10);
        $this->ship->shouldReceive('getCrewlist')
            ->withNoArgs()
            ->andReturn($shipCrewlist);
        $this->ship->shouldReceive('setAlertStateGreen')
            ->withNoArgs()
            ->once();
        $this->ship->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->twice()
            ->andReturn($dockedShips);

        $shipCrewlist->shouldReceive('clear')
            ->withNoArgs()
            ->once();

        $dockedShip->shouldReceive('setDockedTo')
            ->with(null)
            ->once();

        $this->shipRepository->shouldReceive('save')
            ->with($dockedShip)
            ->once();

        $this->shipSystemManager->shouldReceive('deactivateAll')
            ->with($this->wrapper)
            ->once();

        $msg = $this->subject->manage($this->wrapper, $values, $this->managerProvider);

        $this->assertEquals(['name: Die Crew wurde runtergebeamt'], $msg);
    }
}
