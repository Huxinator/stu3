<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowBeamFrom;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowBeamFrom implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BEAMFROM';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId()
        );
        $game->setPageTitle(_('Von Schiff beamen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/entity_not_available');

        $target = $this->shipLoader->find(request::getIntFatal('target'));
        if ($target === null) {
            return;
        }
        if ($ship->canInteractWith($target, false, true) === false) {
            return;
        }

        $game->setMacro('html/shipmacros.xhtml/show_ship_beamfrom');

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('OWNS_TARGET', $target->getUser() === $user);
    }
}
