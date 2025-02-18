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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StationShipRepairRepository")
 * @Table(name="stu_station_shiprepair")
 **/
class StationShipRepair implements StationShipRepairInterface
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
    private int $station_id;

    /**
     * @Column(type="integer")
     *
     */
    private int $ship_id;

    /**
     * @var ShipInterface
     *
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="station_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $station;

    /**
     * @var ShipInterface
     *
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStationId(): int
    {
        return $this->station_id;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function getStation(): ShipInterface
    {
        return $this->station;
    }

    public function setStation(ShipInterface $station): StationShipRepairInterface
    {
        $this->station = $station;
        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): StationShipRepairInterface
    {
        $this->ship = $ship;
        return $this;
    }
}
