<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowColonization;

use request;

use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowColonization implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONIZATION';

    private ShipLoaderInterface $shipLoader;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonizationCheckerInterface $colonizationChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyRepositoryInterface $colonyRepository,
        ColonizationCheckerInterface $colonizationChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyRepository = $colonyRepository;
        $this->colonizationChecker = $colonizationChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colony = $this->colonyRepository->getByPosition(
            $ship->getStarsystemMap()
        );

        if ($colony === null) {
            return;
        }

        if ($ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
            if (!$this->colonizationChecker->canColonize($game->getUser(), $colony)) {
                return;
            }
        } else {
            return;
        }

        $game->setPageTitle(_('Kolonie gründen'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/colonization');

        $game->setTemplateVar('currentColony', $colony);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
    }
}
