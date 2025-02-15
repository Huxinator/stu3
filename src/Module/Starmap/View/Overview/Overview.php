<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\Overview;

use request;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private LayerRepositoryInterface $layerRepository;

    public function __construct(LayerRepositoryInterface $layerRepository)
    {
        $this->layerRepository = $layerRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Sternenkarte'));
        $game->setTemplateFile('html/starmap.xhtml');
        $game->appendNavigationPart('starmap.php', _('Sternenkarte'));

        //only layers, that are known by user
        $layers = $this->layerRepository->getKnownByUser($game->getUser()->getId());

        if (empty($layers)) {
            return;
        }

        $layerId = request::getInt('layerid');
        if (!$layerId) {
            $layer = current($layers);
        } else {
            $layer = $layers[$layerId];
        }
        $game->setTemplateVar('LAYERID', $layer->getId());

        //HEADROW
        $xHeadRow = [];
        for ($j = 1; $j <= (int)ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION); $j++) {
            $xHeadRow[] = $j;
        }

        //SECTIONS
        $sections = [];
        $k = 1;
        for ($i = 1; $i <= (int)ceil($layer->getHeight() / MapEnum::FIELDS_PER_SECTION); $i++) {
            for ($j = 1; $j <= (int)ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION); $j++) {
                $sections[$i][$j] = $k;
                $k++;
            }
        }

        $game->setTemplateVar('LAYERS', $layers);
        $game->setTemplateVar('X_HEAD_ROW', $xHeadRow);
        $game->setTemplateVar('SECTIONS', $sections);
        $game->setTemplateVar('FIELDS_PER_SECTION', MapEnum::FIELDS_PER_SECTION);
    }
}
