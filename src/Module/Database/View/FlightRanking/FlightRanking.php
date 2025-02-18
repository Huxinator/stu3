<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\FlightRanking;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopListFlights;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class FlightRanking implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TOP_FLIGHTS';

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private DatabaseUiFactoryInterface $databaseUiFactory;

    public function __construct(
        DatabaseUiFactoryInterface $databaseUiFactory,
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->databaseUiFactory = $databaseUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setNavigation([
            [
                'url' => 'database.php',
                'title' => 'Datenbank'
            ],
            [
                'url' => sprintf('database.php?%s=1', static::VIEW_IDENTIFIER),
                'title' => 'Die Top 10 der Vielflieger'
            ]
        ]);
        $game->setPageTitle('/ Datenbank / Die Top 10 der Vielflieger');
        $game->showMacro('html/database.xhtml/top_flights_user');

        $game->setTemplateVar(
            'FLIGHTS_LIST',
            array_map(
                fn (array $data): DatabaseTopListFlights => $this->databaseUiFactory->createDatabaseTopListFlights($data),
                $this->flightSignatureRepository->getFlightsTop10()
            )
        );
    }
}
