<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Mockery;
use Mockery\MockInterface;
use Stu\Exception\AccessViolation;
use Stu\Exception\EntityLockedException;
use Stu\Module\Tick\Lock\LockEnum;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\StuTestCase;

class ColonyLoaderTest extends StuTestCase
{
    /**
     * @var MockInterface|null|ColonyRepositoryInterface
     */
    private $colonyRepository;

    /**
     * @var MockInterface|null|LockManagerInterface
     */
    private $lockManager;

    /**
     * @var MockInterface|null|ColonyInterface
     */
    private $colony;

    private int $colonyId = 42;
    private int $userId = 5;

    /**
     * @var null|ColonyLoader
     */
    private $subject;


    public function setUp(): void
    {
        $this->colonyRepository = Mockery::mock(ColonyRepositoryInterface::class);
        $this->lockManager = Mockery::mock(LockManagerInterface::class);

        $this->colony = $this->mock(ColonyInterface::class);

        $this->subject = new ColonyLoader(
            $this->colonyRepository,
            $this->lockManager
        );
    }

    public function testByIdAndUserExpectErrorWhenEntityLocked(): void
    {
        static::expectExceptionMessage('Tick läuft gerade, Zugriff auf Kolonie ist daher blockiert');
        static::expectException(EntityLockedException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->colonyId, LockEnum::LOCK_TYPE_COLONY_GROUP)
            ->once()
            ->andReturn(true);

        $this->subject->byIdAndUser($this->colonyId, $this->userId);
    }

    public function testByIdAndUserExpectErrorWhenColonyNotExistent(): void
    {
        static::expectExceptionMessage("Colony not existent! Fool: 5");
        static::expectException(AccessViolation::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->colonyId, LockEnum::LOCK_TYPE_COLONY_GROUP)
            ->once()
            ->andReturn(false);

        $this->colonyRepository->shouldReceive('find')
            ->with($this->colonyId)
            ->once()
            ->andReturn(null);

        $this->subject->byIdAndUser($this->colonyId, $this->userId);
    }

    public function testByIdAndUserReturnsColonyIfNotLocked(): void
    {
        $this->lockManager->shouldReceive('isLocked')
            ->with($this->colonyId, LockEnum::LOCK_TYPE_COLONY_GROUP)
            ->once()
            ->andReturn(false);

        $this->colonyRepository->shouldReceive('find')
            ->with($this->colonyId)
            ->once()
            ->andReturn($this->colony);

        $this->colony->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn($this->userId);

        $result = $this->subject->byIdAndUser($this->colonyId, $this->userId);

        $this->assertSame($this->colony, $result);
    }

    public function testByIdAndUserExpectErrorWhenForeignColony(): void
    {
        static::expectExceptionMessage("Colony owned by another user (666)! Fool: 5");
        static::expectException(AccessViolation::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->colonyId, LockEnum::LOCK_TYPE_COLONY_GROUP)
            ->once()
            ->andReturn(false);

        $this->colonyRepository->shouldReceive('find')
            ->with($this->colonyId)
            ->once()
            ->andReturn($this->colony);

        $this->colony->shouldReceive('getUserId')
            ->withNoArgs()
            ->twice()
            ->andReturn(666);

        $this->subject->byIdAndUser($this->colonyId, $this->userId);
    }
}
