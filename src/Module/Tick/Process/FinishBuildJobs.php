<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class FinishBuildJobs implements ProcessTickHandlerInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        BuildingManagerInterface $buildingManager
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->buildingManager = $buildingManager;
    }

    public function work(): void
    {
        $result = $this->planetFieldRepository->getByConstructionFinish(time());
        foreach ($result as $field) {
            $this->buildingManager->finish($field, $field->getActivateAfterBuild());
            $colony = $field->getColony();

            $txt = sprintf(
                "Kolonie %s: %s auf Feld %s fertiggestellt",
                $colony->getName(),
                $field->getBuilding()->getName(),
                $field->getFieldId()
            );

            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                (int) $colony->getUserId(),
                $txt,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
        }
    }
}
