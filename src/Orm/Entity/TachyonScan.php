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
 * @Entity(repositoryClass="Stu\Orm\Repository\TachyonScanRepository")
 * @Table(
 *     name="stu_tachyon_scan",
 *     indexes={
 *         @Index(name="tachyon_scan_user_idx", columns={"user_id"}),
 *         @Index(name="tachyon_scan_map_idx", columns={"map_id"}),
 *         @Index(name="tachyon_scan_sysmap_idx", columns={"starsystem_map_id"})
 *     }
 * )
 **/
class TachyonScan implements TachyonScanInterface
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
    private int $user_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $scan_time = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $map_id;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $starsystem_map_id;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var null|MapInterface
     *
     * @ManyToOne(targetEntity="Map")
     * @JoinColumn(name="map_id", referencedColumnName="id")
     */
    private $map;

    /**
     * @var null|StarSystemMapInterface
     *
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id", referencedColumnName="id")
     */
    private $starsystem_map;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TachyonScanInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getScanTime(): int
    {
        return $this->scan_time;
    }
    public function setScanTime(int $scanTime): TachyonScanInterface
    {
        $this->scan_time = $scanTime;
        return $this;
    }

    public function getMap(): ?MapInterface
    {
        return $this->map;
    }

    public function setMap(?MapInterface $map): TachyonScanInterface
    {
        $this->map = $map;
        return $this;
    }

    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        return $this->starsystem_map;
    }

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): TachyonScanInterface
    {
        $this->starsystem_map = $starsystem_map;
        return $this;
    }
}
