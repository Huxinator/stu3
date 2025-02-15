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
 * @Entity(repositoryClass="Stu\Orm\Repository\FactionRepository")
 * @Table(
 *     name="stu_factions"
 * )
 */
class Faction implements FactionInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="string")
     *
     */
    private string $name = '';

    /**
     * @Column(type="text")
     *
     */
    private string $description = '';

    /**
     * @Column(type="string")
     *
     */
    private string $darker_color = '';

    /**
     * @Column(type="boolean")
     *
     */
    private bool $chooseable = false;

    /**
     * @Column(type="integer")
     *
     */
    private int $player_limit = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $start_building_id = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $start_research_id;

    //TODO survivor_rate to escape pods

    /**
     * @var null|ResearchInterface
     *
     * @ManyToOne(targetEntity="Research")
     * @JoinColumn(name="start_research_id", referencedColumnName="id")
     */
    private $start_research;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FactionInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): FactionInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDarkerColor(): string
    {
        return $this->darker_color;
    }

    public function setDarkerColor(string $darkerColor): FactionInterface
    {
        $this->darker_color = $darkerColor;

        return $this;
    }

    public function getChooseable(): bool
    {
        return $this->chooseable;
    }

    public function setChooseable(bool $chooseable): FactionInterface
    {
        $this->chooseable = $chooseable;

        return $this;
    }

    public function getPlayerLimit(): int
    {
        return $this->player_limit;
    }

    public function setPlayerLimit(int $playerLimit): FactionInterface
    {
        $this->player_limit = $playerLimit;

        return $this;
    }

    public function getStartBuildingId(): int
    {
        return $this->start_building_id;
    }

    public function setStartBuildingId(int $startBuildingId): FactionInterface
    {
        $this->start_building_id = $startBuildingId;

        return $this;
    }

    public function getStartResearch(): ?ResearchInterface
    {
        return $this->start_research;
    }

    public function setStartResearch(?ResearchInterface $start_research): FactionInterface
    {
        $this->start_research = $start_research;
        return $this;
    }
}
