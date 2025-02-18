<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class CreateRelation implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const ACTION_IDENTIFIER = 'B_NEW_RELATION';

    private CreateRelationRequestInterface $createRelationRequest;

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private AllianceRepositoryInterface $allianceRepository;

    public function __construct(
        CreateRelationRequestInterface $createRelationRequest,
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager,
        AllianceRepositoryInterface $allianceRepository
    ) {
        $this->createRelationRequest = $createRelationRequest;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = $alliance->getId();
        $user = $game->getUser();

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $user)) {
            throw new AccessViolation();
        }

        $counterpartId = $this->createRelationRequest->getCounterpartId();
        $typeId = $this->createRelationRequest->getRelationType();

        $counterpart = $this->allianceRepository->find($counterpartId);
        if (
            $counterpart === null
            || $alliance === $counterpart
            || !in_array($typeId, AllianceEnum::ALLOWED_RELATION_TYPES, true)
        ) {
            return;
        }

        $cnt = $this->allianceRelationRepository->getPendingCountByAlliances($allianceId, $counterpartId);
        if ($cnt >= 2) {
            $game->addInformation('Es gibt bereits ein Angebot für diese Allianz');
            return;
        }

        // check if a relation exists
        $existingRelation = $this->allianceRelationRepository->getByAlliancePair($allianceId, $counterpartId);
        if ($existingRelation !== null) {
            $existingRelationType = $existingRelation->getType();
            if ($existingRelationType === $typeId) {
                return;
            }

            if (
                $existingRelationType === AllianceEnum::ALLIANCE_RELATION_WAR
                && $typeId !== AllianceEnum::ALLIANCE_RELATION_PEACE
            ) {
                return;
            }
        }

        if ($typeId === AllianceEnum::ALLIANCE_RELATION_WAR) {
            $game->triggerEvent(new WarDeclaredEvent(
                $alliance,
                $counterpart,
                $user
            ));

            $game->addInformation(
                sprintf('Der Allianz %s wurde der Krieg erklärt', $counterpart->getName())
            );
        } else {
            $game->triggerEvent(new DiplomaticRelationProposedEvent(
                $alliance,
                $counterpart,
                $typeId
            ));

            $game->addInformation('Das Abkommen wurde angeboten');
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
