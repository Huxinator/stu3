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
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildplanHangarRepository")
 * @Table(
 *     name="stu_buildplans_hangar",
 *     uniqueConstraints={@UniqueConstraint(name="rump_idx", columns={"rump_id"})}
 * )
 **/
class BuildplanHangar implements BuildplanHangarInterface
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
    private int $rump_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $buildplan_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $default_torpedo_type_id;

    /**
     * @Column(type="integer")
     *
     */
    private int $start_energy_costs;

    /**
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="default_torpedo_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?TorpedoTypeInterface $defaultTorpedoType;

    /**
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="buildplan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ShipBuildplanInterface $buildplan;

    /**
     * @ManyToOne(targetEntity="ShipRump", inversedBy="startHangar")
     * @JoinColumn(name="rump_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ShipRumpInterface $shipRump;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildplanId(): int
    {
        return $this->buildplan_id;
    }

    public function setBuildplanId(int $buildplanId): BuildplanHangarInterface
    {
        $this->buildplan_id = $buildplanId;

        return $this;
    }

    public function getDefaultTorpedoTypeId(): int
    {
        return $this->default_torpedo_type_id;
    }

    public function setDefaultTorpedoTypeId(int $defaultTorpedoTypeId): BuildplanHangarInterface
    {
        $this->default_torpedo_type_id = $defaultTorpedoTypeId;

        return $this;
    }

    public function getDefaultTorpedoType(): ?TorpedoTypeInterface
    {
        return $this->defaultTorpedoType;
    }

    public function getBuildplan(): ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    public function setStartEnergyCosts(int $startEnergyCosts): BuildplanHangarInterface
    {
        $this->start_energy_costs = $startEnergyCosts;
        return $this;
    }

    public function getStartEnergyCosts(): int
    {
        return $this->start_energy_costs;
    }
}
