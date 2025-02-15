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
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepository")
 * @Table(
 *     name="stu_rumps_cat_role_crew",
 *     indexes={
 *         @Index(name="ship_rump_category_role_idx", columns={"rump_category_id","rump_role_id"})
 *     }
 * )
 **/
class ShipRumpCategoryRoleCrew implements ShipRumpCategoryRoleCrewInterface
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
    private int $rump_category_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $rump_role_id = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $job_1_crew = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $job_2_crew = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $job_3_crew = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $job_4_crew = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $job_5_crew = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $job_6_crew = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $job_7_crew = 0;

    /**
     * @var ShipRumpRoleInterface
     *
     * @ManyToOne(targetEntity="ShipRumpRole")
     * @JoinColumn(name="rump_role_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $shiprumpRole;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipRumpCategoryId(): int
    {
        return $this->rump_category_id;
    }

    public function setShipRumpCategoryId(int $shipRumpCategoryId): ShipRumpCategoryRoleCrewInterface
    {
        $this->rump_category_id = $shipRumpCategoryId;

        return $this;
    }

    public function getShipRumpRoleId(): int
    {
        return $this->rump_role_id;
    }

    public function setShipRumpRoleId(int $shipRumpRoleId): ShipRumpCategoryRoleCrewInterface
    {
        $this->rump_role_id = $shipRumpRoleId;

        return $this;
    }

    public function getJob1Crew(): int
    {
        return $this->job_1_crew;
    }

    public function setJob1Crew(int $job1crew): ShipRumpCategoryRoleCrewInterface
    {
        $this->job_1_crew = $job1crew;

        return $this;
    }

    public function getJob2Crew(): int
    {
        return $this->job_2_crew;
    }

    public function setJob2Crew(int $job2crew): ShipRumpCategoryRoleCrewInterface
    {
        $this->job_2_crew = $job2crew;

        return $this;
    }

    public function getJob3Crew(): int
    {
        return $this->job_3_crew;
    }

    public function setJob3Crew(int $job3crew): ShipRumpCategoryRoleCrewInterface
    {
        $this->job_3_crew = $job3crew;

        return $this;
    }

    public function getJob4Crew(): int
    {
        return $this->job_4_crew;
    }

    public function setJob4Crew(int $job4crew): ShipRumpCategoryRoleCrewInterface
    {
        $this->job_4_crew = $job4crew;

        return $this;
    }

    public function getJob5Crew(): int
    {
        return $this->job_5_crew;
    }

    public function setJob5Crew(int $job5crew): ShipRumpCategoryRoleCrewInterface
    {
        $this->job_5_crew = $job5crew;

        return $this;
    }

    public function getJob6Crew(): int
    {
        return $this->job_6_crew;
    }

    public function setJob6Crew(int $job6crew): ShipRumpCategoryRoleCrewInterface
    {
        $this->job_6_crew = $job6crew;

        return $this;
    }

    public function getJob7Crew(): int
    {
        return $this->job_7_crew;
    }

    public function setJob7Crew(int $job7crew): ShipRumpCategoryRoleCrewInterface
    {
        $this->job_7_crew = $job7crew;

        return $this;
    }

    public function getShiprumpRole(): ShipRumpRoleInterface
    {
        return $this->shiprumpRole;
    }
}
