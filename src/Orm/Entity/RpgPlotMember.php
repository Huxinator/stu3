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
 * @Entity(repositoryClass="Stu\Orm\Repository\RpgPlotMemberRepository")
 * @Table(
 *     name="stu_plots_members",
 *     uniqueConstraints={@UniqueConstraint(name="plot_user_idx", columns={"plot_id", "user_id"})}
 * )
 */
class RpgPlotMember implements RpgPlotMemberInterface
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
    private int $plot_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id = 0;

    /**
     * @var RpgPlotInterface
     *
     * @ManyToOne(targetEntity="RpgPlot", inversedBy="members")
     * @JoinColumn(name="plot_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $rpgPlot;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlotId(): int
    {
        return $this->plot_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getRpgPlot(): RpgPlotInterface
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(RpgPlotInterface $rpgPlot): RpgPlotMemberInterface
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): RpgPlotMemberInterface
    {
        $this->user = $user;
        return $this;
    }
}
