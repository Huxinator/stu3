<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Diplomatic;

use Mockery\MockInterface;
use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRendererInterface;
use Stu\Module\Alliance\View\AllianceList\AllianceList;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceRelationInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\StuTestCase;

class DiplomaticRelationsTest extends StuTestCase
{
    /** @var MockInterface&AllianceRelationRepositoryInterface */
    private MockInterface $allianceRelationRepository;

    /** @var MockInterface&AllianceRelationRendererInterface */
    private MockInterface $allianceRelationRenderer;

    private DiplomaticRelations $subject;

    protected function setUp(): void
    {
        $this->allianceRelationRepository = $this->mock(AllianceRelationRepositoryInterface::class);
        $this->allianceRelationRenderer = $this->mock(AllianceRelationRendererInterface::class);

        $this->subject = new DiplomaticRelations(
            $this->allianceRelationRepository,
            $this->allianceRelationRenderer
        );
    }

    public function testHandleRenders(): void
    {
        $game = $this->mock(GameControllerInterface::class);

        $renderResult = 'some-render-result';

        $relation = $this->mock(AllianceRelationInterface::class);

        $game->shouldReceive('setPageTitle')
            ->with('Diplomatische Beziehungen')
            ->once();
        $game->shouldReceive('setNavigation')
            ->with([
                [
                    'url' => 'alliance.php',
                    'title' => 'Allianz',
                ],
                [
                    'url' => sprintf('alliance.php?%s=1', AllianceList::VIEW_IDENTIFIER),
                    'title' => 'Allianzliste',
                ],
                [
                    'url' => sprintf('alliance.php?%s=1', DiplomaticRelations::VIEW_IDENTIFIER),
                    'title' => 'Diplomatische Beziehungen',
                ],
            ])
            ->once()
            ->andReturnSelf();

        $game->shouldReceive('setTemplateFile')
            ->with('html/alliance_diplomatic_relations.xhtml')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with(
                'RELATIONS_IMAGE',
                $renderResult
            )
            ->once();

        $this->allianceRelationRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([$relation]);

        $this->allianceRelationRenderer->shouldReceive('render')
            ->with([$relation], 800, 700)
            ->once()
            ->andReturn($renderResult);

        $this->subject->handle($game);
    }
}
