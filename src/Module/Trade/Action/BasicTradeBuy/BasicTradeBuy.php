<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\BasicTradeBuy;

use request;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowBasicTrade\ShowBasicTrade;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class BasicTradeBuy implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BASIC_BUY';

    private TradeLibFactoryInterface $tradeLibFactory;

    private BasicTradeRepositoryInterface $basicTradeRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    public function __construct(
        TradeLibFactoryInterface $tradeLibFactory,
        BasicTradeRepositoryInterface $basicTradeRepository,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->tradeLibFactory = $tradeLibFactory;
        $this->basicTradeRepository = $basicTradeRepository;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowBasicTrade::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $tradePostId = request::postIntFatal('tpid');
        $uniqId = request::postStringFatal('uid');
        $basicTrade = $this->basicTradeRepository->getByUniqId($uniqId);

        if ($basicTrade === null) {
            return;
        }

        $isNewest = $this->basicTradeRepository->isNewest($basicTrade);

        if (!$isNewest) {
            $game->addInformation("Kurs wurde zwischenzeitlich aktualisiert - es konnte nicht gekauft werden");
            return;
        }

        $tradePost = $this->tradePostRepository->find($tradePostId);

        if ($tradePost === null) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);

        if ($storageManager->getFreeStorage() <= 0) {
            $game->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - es konnte nicht gekauft werden");
            return;
        }

        $latinumStorage = $storageManager->getStorage()[CommodityTypeEnum::GOOD_LATINUM];

        if ($latinumStorage === null || $latinumStorage < 1) {
            $game->addInformation("Dein Warenkonto verfügt über kein Latinum - es konnte nicht gekauft werden");
            return;
        }

        $latestRates = $this->basicTradeRepository->getLatestRates($basicTrade);

        $newValue = null;
        $summand = 1;

        foreach ($latestRates as $basicTrade) {
            if ($newValue === null) {
                $newValue = $basicTrade->getValue();
            } else {
                $summand += $basicTrade->getBuySell();
            }
        }
        $newValue += (int)($summand / (count($latestRates) + 1));

        $newBasicTrade = $this->basicTradeRepository->prototype();
        $newBasicTrade->setFaction($basicTrade->getFaction());
        $newBasicTrade->setCommodity($basicTrade->getCommodity());
        $newBasicTrade->setBuySell(TradeEnum::BASIC_TRADE_BUY_MODIFIER);
        $newBasicTrade->setValue($newValue);
        $newBasicTrade->setDate(time());
        $newBasicTrade->setUniqId(uniqid());
        $newBasicTrade->setUserId($game->getUser()->getId());

        $this->basicTradeRepository->save($newBasicTrade);

        $storageManager->upperStorage($basicTrade->getCommodity()->getId(), (int) ($basicTrade->getValue() / 100));
        $storageManager->lowerStorage(CommodityTypeEnum::GOOD_LATINUM, 1);

        $game->addInformation('Die Waren wurden gekauft');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
