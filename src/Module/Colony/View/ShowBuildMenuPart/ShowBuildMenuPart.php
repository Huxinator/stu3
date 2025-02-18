<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildMenuPart;

use Stu\Module\Colony\Lib\BuildMenuWrapper;
use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ShowBuildMenuPart implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDMENU_PART';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowBuildMenuPartRequestInterface $showBuildMenuPartRequest;

    private BuildingRepositoryInterface $buildingRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowBuildMenuPartRequestInterface $showBuildMenuPartRequest,
        BuildingRepositoryInterface $buildingRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showBuildMenuPartRequest = $showBuildMenuPartRequest;
        $this->buildingRepository = $buildingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildMenuPartRequest->getColonyId(),
            $userId,
            false
        );

        $colonyId = (int) $colony->getId();

        $this->colonyGuiHelper->register($colony, $game);

        $menus = [];
        $menus[1]['buildings'] = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
            $colonyId,
            $userId,
            1,
            0
        );
        $menus[2]['buildings'] = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
            $colonyId,
            $userId,
            2,
            0
        );
        $menus[3]['buildings'] = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
            $colonyId,
            $userId,
            3,
            0
        );

        $game->showMacro('html/colonymacros.xhtml/buildmenu');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_BUILD));
        $game->setTemplateVar('BUILD_MENUS', $menus);
        $game->setTemplateVar('BUILD_MENU', new BuildMenuWrapper());
    }
}
