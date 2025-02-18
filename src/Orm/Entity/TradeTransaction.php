<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeTransactionRepository")
 * @Table(
 *     name="stu_trade_transaction",
 *     indexes={
 *         @Index(name="trade_transaction_date_tradepost_idx", columns={"date", "tradepost_id"})
 *     }
 * )
 **/
class TradeTransaction implements TradeTransactionInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $wg_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $wg_count = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $gg_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $gg_count = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $date = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $tradepost_id = 0;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="wg_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $wantedCommodity;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="gg_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $offeredCommodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getWantedCommodityId(): int
    {
        return $this->wg_id;
    }

    public function setWantedCommodityId(int $wantedCommodityId): TradeTransactionInterface
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    public function getWantedCommodityCount(): int
    {
        return $this->wg_count;
    }

    public function setWantedCommodityCount(int $wantedCommodityCount): TradeTransactionInterface
    {
        $this->wg_count = $wantedCommodityCount;

        return $this;
    }

    public function getOfferedCommodityId(): int
    {
        return $this->gg_id;
    }

    public function setOfferedCommodityId(int $offeredCommodityId): TradeTransactionInterface
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    public function getOfferedCommodityCount(): int
    {
        return $this->gg_count;
    }

    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeTransactionInterface
    {
        $this->gg_count = $offeredCommodityCount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeTransactionInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePostId(): int
    {
        return $this->tradepost_id;
    }

    public function setTradePostId(int $tradepost_id): TradeTransactionInterface
    {
        $this->tradepost_id = $tradepost_id;

        return $this;
    }

    public function getWantedCommodity(): CommodityInterface
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeTransactionInterface
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getOfferedCommodity(): CommodityInterface
    {
        return $this->offeredCommodity;
    }

    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeTransactionInterface
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }
}
