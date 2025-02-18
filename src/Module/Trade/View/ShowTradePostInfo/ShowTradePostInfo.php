<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowTradePostInfo;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradePostInfo implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEPOST_INFO';

    private ShowTradePostInfoRequestInterface $showTradePostInfoRequest;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeLibFactoryInterface $talFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    public function __construct(
        ShowTradePostInfoRequestInterface $showTradePostInfoRequest,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLibFactoryInterface $talFactory,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->showTradePostInfoRequest = $showTradePostInfoRequest;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->talFactory = $talFactory;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $tradePost = $this->tradePostRepository->find($this->showTradePostInfoRequest->getTradePostId());
        if ($tradePost === null) {
            return;
        }

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradePost->getId())) {
            throw new AccessViolation();
        }

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradepostinfo');
        $game->setPageTitle(_('Handelsposten Details'));

        $game->setTemplateVar('IS_NPC_TRADEPOST', $tradePost->isNpcTradepost());
        $game->setTemplateVar(
            'TRADE_POST_INFO',
            $this->talFactory->createTradeAccountTal($tradePost, $userId)
        );
    }
}
