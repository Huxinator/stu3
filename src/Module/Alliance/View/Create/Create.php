<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Create;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Create implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const VIEW_IDENTIFIER = 'CREATE_ALLIANCE';

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getAlliance() !== null) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Allianz gründen'));
        $game->appendNavigationPart('alliance.php?SHOW_LIST=1', _('Allianzliste'));
        $game->appendNavigationPart('alliance.php?CREATE_ALLIANCE=1', _('Allianz gründen'));
        $game->setTemplateFile('html/alliancecreate.xhtml');
    }
}
