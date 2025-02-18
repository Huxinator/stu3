<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipStateChangerInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class CreateTholianWeb implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_WEB';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private InteractionCheckerInterface $interactionChecker;

    private ActivatorDeactivatorHelperInterface $helper;

    private TholianWebRepositoryInterface $tholianWebRepository;

    private TholianWebUtilInterface $tholianWebUtil;

    private ShipCreatorInterface $shipCreator;

    private PrivateMessageSenderInterface $privateMessageSender;

    private StuTime $stuTime;

    private ShipStateChangerInterface $shipStateChanger;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        InteractionCheckerInterface $interactionChecker,
        ActivatorDeactivatorHelperInterface $helper,
        TholianWebRepositoryInterface $tholianWebRepository,
        TholianWebUtilInterface $tholianWebUtil,
        ShipCreatorInterface $shipCreator,
        PrivateMessageSenderInterface $privateMessageSender,
        StuTime $stuTime,
        ShipStateChangerInterface $shipStateChanger,
        EntityManagerInterface $entityManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->interactionChecker = $interactionChecker;
        $this->helper = $helper;
        $this->tholianWebRepository = $tholianWebRepository;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->shipCreator = $shipCreator;
        $this->privateMessageSender = $privateMessageSender;
        $this->stuTime = $stuTime;
        $this->shipStateChanger = $shipStateChanger;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        //TODO other web spinners in fleet should join

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();

        $emitter = $wrapper->getWebEmitterSystemData();

        if ($emitter === null || $emitter->getWebUnderConstruction() !== null || $emitter->getOwnedTholianWeb() !== null) {
            throw new SanityCheckException('emitter = null or already spinning', self::ACTION_IDENTIFIER);
        }

        if ($this->tholianWebRepository->getWebAtLocation($ship) !== null) {
            throw new SanityCheckException('already existing web on location', self::ACTION_IDENTIFIER);
        }

        if ($ship->getWarpState()) {
            $game->addInformation("Aktion nicht möglich, da der Warpantrieb aktiviert ist");
            return;
        }

        $chosenShipIds = request::postArray('chosen');

        /**
         * @var ShipInterface[]
         */
        $possibleCatches = [];
        foreach ($chosenShipIds as $targetId) {
            $target = $this->tryToCatch($ship, (int)$targetId, $game);
            if ($target !== null) {
                $possibleCatches[] = $target;
            }
        }

        if (empty($possibleCatches)) {
            $game->addInformation("Es konnten keine Ziele erfasst werden");
            return;
        }

        // activate system
        if (!$this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB, $game)) {
            return;
        }
        $this->shipStateChanger->changeShipState($wrapper, ShipStateEnum::SHIP_STATE_WEB_SPINNING);

        //create web ship
        $webShip = $this->shipCreator->createBy($userId, 9, 1840)->get();
        $webShip->updateLocation($ship->getMap(), $ship->getStarsystemMap());
        $webShip->setSpacecraftType(SpacecraftTypeEnum::SPACECRAFT_TYPE_OTHER);
        $this->shipRepository->save($webShip);

        //create web entity
        $web = $this->tholianWebRepository->prototype();
        $web->setWebShip($webShip);
        $this->tholianWebRepository->save($web);

        //link ships to web
        foreach ($possibleCatches as $target) {
            $target->setHoldingWeb($web);
            $this->shipRepository->save($target);
            $web->getCapturedShips()->add($target);

            //notify target owner
            $this->privateMessageSender->send(
                $userId,
                $target->getUser()->getId(),
                sprintf(
                    'In Sektor %s wird ein Energienetz um die %s errichtet',
                    $target->getSectorString(),
                    $target->getName()
                ),
                $target->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }

        $this->entityManager->flush();

        $emitter
            ->setWebUnderConstructionId($web->getId())
            ->setOwnedWebId($web->getId())->update();

        $this->tholianWebUtil->updateWebFinishTime($web);

        $game->addInformationf(
            "Es wird ein Energienetz um %d Ziele gespannt, Fertigstellung: %s",
            count($possibleCatches),
            $this->stuTime->transformToStuDate($web->getFinishedTime())
        );
    }

    private function tryToCatch(ShipInterface $ship, int $targetId, GameControllerInterface $game): ?ShipInterface
    {
        $target = $this->shipRepository->find($targetId);

        if ($target === null || $target->getCloakState()) {
            return null;
        }

        if (!$this->interactionChecker->checkPosition($ship, $target)) {
            $game->addInformationf(_('%s: Ziel nicht gefunden'), $target->getName());
            return null;
        }
        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformationf(_('%s: Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'), $target->getName());
            return null;
        }

        return $target;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
