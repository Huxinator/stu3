<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyClassResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyClassResearchRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ColonizationChecker implements ColonizationCheckerInterface
{
    private ResearchedRepositoryInterface $researchedRepository;

    private ColonyClassResearchRepositoryInterface $colonyClassResearchRepository;

    private ColonyLimitCalculatorInterface $colonyLimitCalculator;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        ColonyClassResearchRepositoryInterface $colonyClassResearchRepository,
        ColonyLimitCalculatorInterface $colonyLimitCalculator
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->colonyClassResearchRepository = $colonyClassResearchRepository;
        $this->colonyLimitCalculator = $colonyLimitCalculator;
    }

    public function canColonize(UserInterface $user, ColonyInterface $colony): bool
    {
        if ($colony->isFree() === false) {
            return false;
        }

        $colonyClass = $colony->getColonyClass();

        if (!$this->colonyLimitCalculator->canColonizeFurtherColonyWithType($user, $colonyClass->getType())) {
            return false;
        }

        $researchIds = array_map(
            function (ColonyClassResearchInterface $colonyClassResearch): int {
                return $colonyClassResearch->getResearch()->getId();
            },
            $this->colonyClassResearchRepository->getByColonyClass($colonyClass)
        );
        if ($researchIds !== [] && $this->researchedRepository->hasUserFinishedResearch($user, $researchIds) === false) {
            return false;
        }
        return true;
    }
}
