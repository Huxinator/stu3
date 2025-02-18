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
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyTerraformingRepository")
 * @Table(
 *     name="stu_colonies_terraforming",
 *     indexes={
 *          @Index(name="colony_idx",columns={"colonies_id"}),
 *          @Index(name="finished_idx",columns={"finished"})
 *     }
 * )
 */
class ColonyTerraforming implements ColonyTerraformingInterface
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
    private int $colonies_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $field_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $terraforming_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $finished = 0;

    /**
     * @var TerraformingInterface
     *
     * @ManyToOne(targetEntity="Terraforming")
     * @JoinColumn(name="terraforming_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $terraforming;

    /**
     * @var PlanetFieldInterface
     *
     * @ManyToOne(targetEntity="PlanetField")
     * @JoinColumn(name="field_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $field;

    /**
     * @var ColonyInterface
     *
     * @ManyToOne(targetEntity="Colony")
     * @JoinColumn(name="colonies_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyId(): int
    {
        return $this->colonies_id;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getTerraformingId(): int
    {
        return $this->terraforming_id;
    }

    public function setTerraformingId(int $terraformingId): ColonyTerraformingInterface
    {
        $this->terraforming_id = $terraformingId;

        return $this;
    }

    public function getFinishDate(): int
    {
        return $this->finished;
    }

    public function setFinishDate(int $finishDate): ColonyTerraformingInterface
    {
        $this->finished = $finishDate;

        return $this;
    }

    public function getTerraforming(): TerraformingInterface
    {
        return $this->terraforming;
    }

    public function setTerraforming(TerraformingInterface $terraforming): ColonyTerraformingInterface
    {
        $this->terraforming = $terraforming;

        return $this;
    }

    public function getField(): PlanetFieldInterface
    {
        return $this->field;
    }

    public function setField(PlanetFieldInterface $planetField): ColonyTerraformingInterface
    {
        $this->field = $planetField;

        return $this;
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): ColonyTerraformingInterface
    {
        $this->colony = $colony;
        return $this;
    }

    public function getProgress(): int
    {
        $start = $this->getFinishDate() - $this->getTerraforming()->getDuration();
        return time() - $start;
    }
}
