<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class BasicTradeAccountTal implements BasicTradeAccountTalInterface
{
    private StorageRepositoryInterface $storageRepository;

    private TradePostInterface $tradePost;

    private array $basicTrades;

    private int $userId;

    private $storage;

    private CommodityRepositoryInterface $commodityRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        StorageRepositoryInterface $storageRepository,
        TradePostInterface $tradePost,
        array $basicTrades,
        int $userId,
        CommodityRepositoryInterface $commodityRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->storageRepository = $storageRepository;
        $this->tradePost = $tradePost;
        $this->basicTrades = $basicTrades;
        $this->userId = $userId;
        $this->commodityRepository = $commodityRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    public function getShip(): ShipInterface
    {
        return $this->tradePost->getShip();
    }

    public function getTradePostDescription(): string
    {
        return $this->tradePost->getDescription();
    }

    public function getBasicTradeItems(): array
    {
        $result = [];

        $storage = $this->getStorage();

        foreach ($this->basicTrades as $basicTrade) {
            $commodityId = $basicTrade->getCommodity()->getId();
            $result[] = new BasicTradeItem($basicTrade, $storage[$commodityId] ?? null);
        }

        return $result;
    }

    public function getLatinumItem(): BasicTradeItem
    {
        $latinumStorage = $this->getStorage()[CommodityTypeEnum::COMMODITY_LATINUM] ?? null;
        $latinumCommodity = $this->commodityRepository->find(CommodityTypeEnum::COMMODITY_LATINUM);
        return new BasicTradeItem(null, $latinumStorage, $latinumCommodity);
    }

    private function getStorage(): array
    {
        if ($this->storage === null) {
            $this->storage = $this->storageRepository->getByTradePostAndUser(
                $this->tradePost->getId(),
                $this->userId
            );
        }
        return $this->storage;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
            function (int $value, StorageInterface $storage): int {
                return $value + $storage->getAmount();
            },
            0
        );
    }

    public function isOverStorage(): bool
    {
        return $this->getStorageSum() > $this->tradePost->getStorage();
    }

    public function getStorageCapacity(): int
    {
        return $this->tradePost->getStorage();
    }
}
