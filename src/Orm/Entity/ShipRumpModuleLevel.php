<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpModuleLevelRepository")
 * @Table(
 *     name="stu_rumps_module_level",
 *     indexes={
 *         @Index(name="rump_module_level_ship_rump_idx", columns={"rump_id"})
 *     }
 * )
 **/
class ShipRumpModuleLevel implements ShipRumpModuleLevelInterface
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
     * @Column(type="smallint")
     *
     */
    private int $module_level_1 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_1 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_1_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_1_max = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_2 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_2 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_2_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_2_max = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_3 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_3 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_3_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_3_max = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_4 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_4 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_4_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_4_max = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_5 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_5 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_5_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_5_max = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_6 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_6 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_6_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_6_max = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_7 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_7 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_7_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_7_max = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_8 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_mandatory_8 = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_8_min = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $module_level_8_max = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function setRumpId(int $shipRumpId): ShipRumpModuleLevelInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getModuleLevel1(): int
    {
        return $this->module_level_1;
    }

    public function setModuleLevel1(int $moduleLevel1): ShipRumpModuleLevelInterface
    {
        $this->module_level_1 = $moduleLevel1;

        return $this;
    }

    public function getModuleMandatory1(): int
    {
        return $this->module_mandatory_1;
    }

    public function setModuleMandatory1(int $moduleMandatory1): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_1 = $moduleMandatory1;

        return $this;
    }

    public function getModuleLevel1Min(): int
    {
        return $this->module_level_1_min;
    }

    public function setModuleLevel1Min(int $moduleLevel1Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_1_min = $moduleLevel1Min;

        return $this;
    }

    public function getModuleLevel1Max(): int
    {
        return $this->module_level_1_max;
    }

    public function setModuleLevel1Max(int $moduleLevel1Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_1_max = $moduleLevel1Max;

        return $this;
    }

    public function getModuleLevel2(): int
    {
        return $this->module_level_2;
    }

    public function setModuleLevel2(int $moduleLevel2): ShipRumpModuleLevelInterface
    {
        $this->module_level_2 = $moduleLevel2;

        return $this;
    }

    public function getModuleMandatory2(): int
    {
        return $this->module_mandatory_2;
    }

    public function setModuleMandatory2(int $moduleMandatory2): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_2 = $moduleMandatory2;

        return $this;
    }

    public function getModuleLevel2Min(): int
    {
        return $this->module_level_2_min;
    }

    public function setModuleLevel2Min(int $moduleLevel2Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_2_min = $moduleLevel2Min;

        return $this;
    }

    public function getModuleLevel2Max(): int
    {
        return $this->module_level_2_max;
    }

    public function setModuleLevel2Max(int $moduleLevel2Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_2_max = $moduleLevel2Max;

        return $this;
    }

    public function getModuleLevel3(): int
    {
        return $this->module_level_3;
    }

    public function setModuleLevel3(int $moduleLevel3): ShipRumpModuleLevelInterface
    {
        $this->module_level_3 = $moduleLevel3;

        return $this;
    }

    public function getModuleMandatory3(): int
    {
        return $this->module_mandatory_3;
    }

    public function setModuleMandatory3(int $moduleMandatory3): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_3 = $moduleMandatory3;

        return $this;
    }

    public function getModuleLevel3Min(): int
    {
        return $this->module_level_3_min;
    }

    public function setModuleLevel3Min(int $moduleLevel3Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_3_min = $moduleLevel3Min;

        return $this;
    }

    public function getModuleLevel3Max(): int
    {
        return $this->module_level_3_max;
    }

    public function setModuleLevel3Max(int $moduleLevel3Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_3_max = $moduleLevel3Max;

        return $this;
    }

    public function getModuleLevel4(): int
    {
        return $this->module_level_4;
    }

    public function setModuleLevel4(int $moduleLevel4): ShipRumpModuleLevelInterface
    {
        $this->module_level_4 = $moduleLevel4;

        return $this;
    }

    public function getModuleMandatory4(): int
    {
        return $this->module_mandatory_4;
    }

    public function setModuleMandatory4(int $moduleMandatory4): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_4 = $moduleMandatory4;

        return $this;
    }

    public function getModuleLevel4Min(): int
    {
        return $this->module_level_4_min;
    }

    public function setModuleLevel4Min(int $moduleLevel4Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_4_min = $moduleLevel4Min;

        return $this;
    }

    public function getModuleLevel4Max(): int
    {
        return $this->module_level_4_max;
    }

    public function setModuleLevel4Max(int $moduleLevel4Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_4_max = $moduleLevel4Max;

        return $this;
    }

    public function getModuleLevel5(): int
    {
        return $this->module_level_5;
    }

    public function setModuleLevel5(int $moduleLevel5): ShipRumpModuleLevelInterface
    {
        $this->module_level_5 = $moduleLevel5;

        return $this;
    }

    public function getModuleMandatory5(): int
    {
        return $this->module_mandatory_5;
    }

    public function setModuleMandatory5(int $moduleMandatory5): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_5 = $moduleMandatory5;

        return $this;
    }

    public function getModuleLevel5Min(): int
    {
        return $this->module_level_5_min;
    }

    public function setModuleLevel5Min(int $moduleLevel5Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_5_min = $moduleLevel5Min;

        return $this;
    }

    public function getModuleLevel5Max(): int
    {
        return $this->module_level_5_max;
    }

    public function setModuleLevel5Max(int $moduleLevel5Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_5_max = $moduleLevel5Max;

        return $this;
    }

    public function getModuleLevel6(): int
    {
        return $this->module_level_6;
    }

    public function setModuleLevel6(int $moduleLevel6): ShipRumpModuleLevelInterface
    {
        $this->module_level_6 = $moduleLevel6;

        return $this;
    }

    public function getModuleMandatory6(): int
    {
        return $this->module_mandatory_6;
    }

    public function setModuleMandatory6(int $moduleMandatory6): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_6 = $moduleMandatory6;

        return $this;
    }

    public function getModuleLevel6Min(): int
    {
        return $this->module_level_6_min;
    }

    public function setModuleLevel6Min(int $moduleLevel6Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_6_min = $moduleLevel6Min;

        return $this;
    }

    public function getModuleLevel6Max(): int
    {
        return $this->module_level_6_max;
    }

    public function setModuleLevel6Max(int $moduleLevel6Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_6_max = $moduleLevel6Max;

        return $this;
    }

    public function getModuleLevel7(): int
    {
        return $this->module_level_7;
    }

    public function setModuleLevel7(int $moduleLevel7): ShipRumpModuleLevelInterface
    {
        $this->module_level_7 = $moduleLevel7;

        return $this;
    }

    public function getModuleMandatory7(): int
    {
        return $this->module_mandatory_7;
    }

    public function setModuleMandatory7(int $moduleMandatory7): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_7 = $moduleMandatory7;

        return $this;
    }

    public function getModuleLevel7Min(): int
    {
        return $this->module_level_7_min;
    }

    public function setModuleLevel7Min(int $moduleLevel7Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_7_min = $moduleLevel7Min;

        return $this;
    }

    public function getModuleLevel7Max(): int
    {
        return $this->module_level_7_max;
    }

    public function setModuleLevel7Max(int $moduleLevel7Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_7_max = $moduleLevel7Max;

        return $this;
    }

    public function getModuleLevel8(): int
    {
        return $this->module_level_8;
    }

    public function setModuleLevel8(int $moduleLevel8): ShipRumpModuleLevelInterface
    {
        $this->module_level_8 = $moduleLevel8;

        return $this;
    }

    public function getModuleMandatory8(): int
    {
        return $this->module_mandatory_8;
    }

    public function setModuleMandatory8(int $moduleMandatory8): ShipRumpModuleLevelInterface
    {
        $this->module_mandatory_8 = $moduleMandatory8;

        return $this;
    }

    public function getModuleLevel8Min(): int
    {
        return $this->module_level_8_min;
    }

    public function setModuleLevel8Min(int $moduleLevel8Min): ShipRumpModuleLevelInterface
    {
        $this->module_level_8_min = $moduleLevel8Min;

        return $this;
    }

    public function getModuleLevel8Max(): int
    {
        return $this->module_level_8_max;
    }

    public function setModuleLevel8Max(int $moduleLevel8Max): ShipRumpModuleLevelInterface
    {
        $this->module_level_8_max = $moduleLevel8Max;

        return $this;
    }

    public function getMandatoryModulesCount(): int
    {
        return $this->getModuleMandatory1() +
            $this->getModuleMandatory2() +
            $this->getModuleMandatory3() +
            $this->getModuleMandatory4() +
            $this->getModuleMandatory5() +
            $this->getModuleMandatory6() +
            $this->getModuleMandatory7() +
            $this->getModuleMandatory8();
    }
}
