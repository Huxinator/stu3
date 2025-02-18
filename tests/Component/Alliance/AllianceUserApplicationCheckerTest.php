<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Mockery\MockInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\StuTestCase;

class AllianceUserApplicationCheckerTest extends StuTestCase
{
    private MockInterface $allianceJobRepository;

    private MockInterface $user;

    private MockInterface $alliance;

    private AllianceUserApplicationChecker $subject;

    protected function setUp(): void
    {
        $this->allianceJobRepository = $this->mock(AllianceJobRepositoryInterface::class);

        $this->user = $this->mock(UserInterface::class);
        $this->alliance = $this->mock(AllianceInterface::class);

        $this->subject = new AllianceUserApplicationChecker(
            $this->allianceJobRepository
        );
    }

    public function testMayApplyReturnsFalseIfUserHasPendingApplications(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceEnum::ALLIANCE_JOBS_PENDING
            )
            ->once()
            ->andReturn($this->mock(AllianceJobInterface::class));

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsFalseIfAllianceDoesNotAcceptApplications(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceEnum::ALLIANCE_JOBS_PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsFalseIfUserIsInAnAlliance(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceEnum::ALLIANCE_JOBS_PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(AllianceInterface::class));

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsFalseIfFactionsDontMatch(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceEnum::ALLIANCE_JOBS_PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFactionId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->user->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsTrueIfFactionsMatch(): void
    {
        $factionId = 666;

        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceEnum::ALLIANCE_JOBS_PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFactionId')
            ->withNoArgs()
            ->twice()
            ->andReturn($factionId);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->user->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn($factionId);

        $this->assertTrue(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsTrueIfFactionModeIsDisabled(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceEnum::ALLIANCE_JOBS_PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->assertTrue(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }
}
