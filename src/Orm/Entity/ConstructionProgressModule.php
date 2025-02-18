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
 * @Entity(repositoryClass="Stu\Orm\Repository\ConstructionProgressModuleRepository")
 * @Table(
 *     name="stu_progress_module"
 * )
 **/
class ConstructionProgressModule implements ConstructionProgressModuleInterface
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
    private int $progress_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $module_id = 0;

    /**
     * @var ConstructionProgressInterface
     *
     * @ManyToOne(targetEntity="ConstructionProgress")
     * @JoinColumn(name="progress_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $progress;

    /**
     * @var ModuleInterface
     *
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getConstructionProgress(): ConstructionProgressInterface
    {
        return $this->progress;
    }

    public function setConstructionProgress(ConstructionProgressInterface $progress): ConstructionProgressModuleInterface
    {
        $this->progress = $progress;
        return $this;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function setModule(ModuleInterface $module): ConstructionProgressModuleInterface
    {
        $this->module = $module;

        return $this;
    }
}
