<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\View\Overview;

use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Player\ColonyLimitCalculatorInterface;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\EmergencyWrapper;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\SpacecraftEmergencyRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private HistoryRepositoryInterface $historyRepository;

    private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository;

    private UserProfileVisitorRepositoryInterface $userProfileVisitorRepository;

    private KnPostRepositoryInterface $knPostRepository;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository;

    private UserRepositoryInterface $userRepository;

    private SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository;

    private KnFactoryInterface $knFactory;

    private ColonyLimitCalculatorInterface $colonyLimitCalculator;

    private LoggerUtilInterface $loggerUtil;

    private CrewCountRetrieverInterface $crewCountRetriever;

    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    private CrewLimitCalculatorInterface $crewLimitCalculator;

    public function __construct(
        HistoryRepositoryInterface $historyRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        UserProfileVisitorRepositoryInterface $userProfileVisitorRepository,
        KnPostRepositoryInterface $knPostRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        UserRepositoryInterface $userRepository,
        SpacecraftEmergencyRepositoryInterface $spacecraftEmergencyRepository,
        KnFactoryInterface $knFactory,
        ColonyLimitCalculatorInterface $colonyLimitCalculator,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        CrewLimitCalculatorInterface $crewLimitCalculator,
        CrewCountRetrieverInterface $crewCountRetriever
    ) {
        $this->historyRepository = $historyRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
        $this->userProfileVisitorRepository = $userProfileVisitorRepository;
        $this->knPostRepository = $knPostRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->shipyardShipQueueRepository = $shipyardShipQueueRepository;
        $this->userRepository = $userRepository;
        $this->spacecraftEmergencyRepository = $spacecraftEmergencyRepository;
        $this->knFactory = $knFactory;
        $this->colonyLimitCalculator = $colonyLimitCalculator;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->crewCountRetriever = $crewCountRetriever;
        $this->playerRelationDeterminator = $playerRelationDeterminator;
        $this->crewLimitCalculator = $crewLimitCalculator;
    }

    public function handle(GameControllerInterface $game): void
    {
        /**
         * @var UserInterface
         */
        $user = $game->getUser();
        $userId = $user->getId();

        $game->appendNavigationPart(
            'maindesk.php',
            _('Maindesk')
        );
        $game->setPageTitle(_('/ Maindesk'));
        $game->setTemplateFile('html/maindesk.xhtml');

        $game->setTemplateVar(
            'DISPLAY_FIRST_COLONY_DIALOGUE',
            $user->getState() === UserEnum::USER_STATE_UNCOLONIZED
        );

        $newAmount = $this->knPostRepository->getAmountSince($user->getKNMark());

        $game->setTemplateVar(
            'NEW_KN_POSTING_COUNT',
            $newAmount
        );
        $game->setTemplateVar(
            'NEW_KN_POSTINGS',
            array_map(
                function (KnPostInterface $knPost) use ($user, $newAmount): KnItemInterface {
                    $newAmount--;
                    $knItem = $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                    $knItem->setMark(((int)floor($newAmount / GameEnum::KN_PER_SITE)) * 6);
                    return $knItem;
                },
                $this->knPostRepository->getNewerThenMark($user->getKNMark())
            )
        );
        $game->setTemplateVar(
            'RECENT_PROFILE_VISITORS',
            $this->userProfileVisitorRepository->getRecent($userId)
        );
        $game->setTemplateVar(
            'RANDOM_ONLINE_USER',
            $this->userRepository->getOrderedByLastaction(15, $userId, time() - GameEnum::USER_ONLINE_PERIOD)
        );
        $game->setTemplateVar(
            'SHIP_BUILD_PROGRESS',
            array_merge(
                $this->colonyShipQueueRepository->getByUser($userId),
                $this->shipyardShipQueueRepository->getByUser($userId)
            )
        );
        $game->setTemplateVar(
            'RECENT_ALLIANCE_BOARD_TOPICS',
            $user->getAlliance() === null ? [] :
                $this->allianceBoardTopicRepository->getRecentByAlliance($user->getAlliance()->getId())
        );
        $game->setTemplateVar('USER', $user);
        $game->setTemplateVar('RECENT_HISTORY', $this->historyRepository->getRecent());

        //emergencies
        $this->setPotentialEmergencies($game);

        //planet
        $game->setTemplateVar('PLANET_LIMIT', $this->colonyLimitCalculator->getColonyLimitWithType($user, ColonyTypeEnum::COLONY_TYPE_PLANET));
        $game->setTemplateVar('PLANET_COUNT', $this->colonyLimitCalculator->getColonyCountWithType($user, ColonyTypeEnum::COLONY_TYPE_PLANET));

        //moon
        $game->setTemplateVar('MOON_LIMIT', $this->colonyLimitCalculator->getColonyLimitWithType($user, ColonyTypeEnum::COLONY_TYPE_MOON));
        $game->setTemplateVar('MOON_COUNT', $this->colonyLimitCalculator->getColonyCountWithType($user, ColonyTypeEnum::COLONY_TYPE_MOON));

        //asteroid
        $game->setTemplateVar('ASTEROID_LIMIT', $this->colonyLimitCalculator->getColonyLimitWithType($user, ColonyTypeEnum::COLONY_TYPE_ASTEROID));
        $game->setTemplateVar('ASTEROID_COUNT', $this->colonyLimitCalculator->getColonyCountWithType($user, ColonyTypeEnum::COLONY_TYPE_ASTEROID));

        $game->setTemplateVar(
            'CREW_LIMIT',
            $this->crewLimitCalculator->getGlobalCrewLimit($user)
        );

        // crew count
        $game->setTemplateVar(
            'CREW_COUNT_SHIPS',
            $this->crewCountRetriever->getAssignedToShipsCount($user)
        );
    }

    private function setPotentialEmergencies(GameControllerInterface $game): void
    {
        $emergencies = $this->spacecraftEmergencyRepository->getActive();

        if (empty($emergencies)) {
            return;
        }

        $emergencyWrappers = [];

        foreach ($emergencies as $emergency) {
            $emergencyWrappers[] = new EmergencyWrapper($this->playerRelationDeterminator, $emergency, $game->getUser());
        }

        $game->setTemplateVar('EMERGENCYWRAPPERS', $emergencyWrappers);
    }
}
