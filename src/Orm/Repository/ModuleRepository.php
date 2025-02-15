<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleSpecial;

/**
 * @extends EntityRepository<Module>
 */
final class ModuleRepository extends EntityRepository implements ModuleRepositoryInterface
{
    // used for ModuleSelector
    public function getBySpecialTypeColonyAndRump(
        int $colonyId,
        int $moduleTypeId, // 1 bis 9: ShipModuleTypeEnum
        int $shipRumpId,
        int $shipRumpRoleId
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.downgrade_factor, m.crew,
                        m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id, m.ecost
                    FROM stu_modules m
                    WHERE m.type = :typeId
                    AND (SELECT CASE WHEN (SELECT count(id)
                                            FROM stu_modules
                                            WHERE type = :typeId
                                            AND rumps_role_id = :shipRumpRoleId) = 0
                                    THEN m.rumps_role_id IS NULL
                                    ELSE m.rumps_role_id = :shipRumpRoleId
                                END)
					AND (m.viewable = :state OR m.commodity_id IN (SELECT commodity_id
                                                                FROM stu_storage
                                                                WHERE colony_id = :colonyId))
                    AND m.id IN (SELECT module_id
                                FROM stu_modules_specials
                                WHERE special_id IN (SELECT module_special_id
                                                    FROM stu_rumps_module_special
                                                    WHERE rump_id = :shipRumpId))
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleTypeId,
                'colonyId' => $colonyId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'shipRumpId' => $shipRumpId,
                'state' => 1
            ])
            ->getResult();
    }

    // used for ModuleSelector
    public function getBySpecialTypeShipAndRump(
        int $shipId,
        int $moduleTypeId, // 1 bis 9: ShipModuleTypeEnum
        int $shipRumpId,
        int $shipRumpRoleId
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.downgrade_factor, m.crew,
                        m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id, m.ecost
                    FROM stu_modules m
                    WHERE m.type = :typeId
                    AND (SELECT CASE WHEN (SELECT count(id)
                                            FROM stu_modules
                                            WHERE type = :typeId
                                            AND rumps_role_id = :shipRumpRoleId) = 0
                                    THEN m.rumps_role_id IS NULL
                                    ELSE m.rumps_role_id = :shipRumpRoleId
                                END)
					AND (m.viewable = :state OR m.commodity_id IN (SELECT commodity_id
                                                                FROM stu_storage
                                                                WHERE ship_id = :shipId))
                    AND m.id IN (SELECT module_id
                                FROM stu_modules_specials
                                WHERE special_id IN (SELECT module_special_id
                                                    FROM stu_rumps_module_special
                                                    WHERE rump_id = :shipRumpId))
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleTypeId,
                'shipId' => $shipId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'shipRumpId' => $shipRumpId,
                'state' => 1
            ])
            ->getResult();
    }

    // used for ModuleSelector
    public function getByTypeColonyAndLevel(
        int $colonyId,
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array {

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.downgrade_factor, m.crew,
                        m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id, m.ecost
                    FROM stu_modules m
                    WHERE m.type = :typeId
                    AND (SELECT CASE WHEN (SELECT count(id)
                                            FROM stu_modules
                                            WHERE type = :typeId
                                            AND rumps_role_id = :shipRumpRoleId) = 0
                                    THEN m.rumps_role_id IS NULL
                                    ELSE m.rumps_role_id = :shipRumpRoleId
                                END)
					AND level IN (:levelList)
					AND (m.viewable = :state OR m.commodity_id IN (SELECT commodity_id
                                                                FROM stu_storage
                                                                WHERE colony_id = :colonyId))
                    ORDER BY m.level ASC
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleTypeId,
                'colonyId' => $colonyId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'levelList' => $moduleLevel,
                'state' => 1
            ])
            ->getResult();
    }

    // used for admin createBuildplan
    public function getByTypeAndLevel(
        int $moduleTypeId,
        int $shipRumpRoleId,
        array $moduleLevel
    ): array {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT
                        m.id, m.name, m.level, m.upgrade_factor, m.downgrade_factor, m.crew, m.type, m.research_id, m.commodity_id, m.viewable, m.rumps_role_id, m.ecost
                    FROM stu_modules m WHERE m.type = :typeId AND (SELECT CASE WHEN (SELECT count(id) FROM stu_modules where type = :typeId AND rumps_role_id = :shipRumpRoleId) = 0 THEN m.rumps_role_id IS NULL ELSE m.rumps_role_id = :shipRumpRoleId END)
					AND level IN (:levelList)
                ',
                $this->getResultSetMapping()
            )
            ->setParameters([
                'typeId' => $moduleTypeId,
                'shipRumpRoleId' => $shipRumpRoleId,
                'levelList' => $moduleLevel
            ])
            ->getResult();
    }

    // used for admin createBuildplan
    public function getBySpecialTypeIds(array $specialTypeIds): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m FROM %s m WHERE m.id IN (
                        SELECT ms.module_id FROM %s ms WHERE ms.special_id IN (:specialTypeIds)
                    )',
                    Module::class,
                    ModuleSpecial::class
                )
            )
            ->setParameters([
                'specialTypeIds' => $specialTypeIds
            ])
            ->getResult();
    }

    private function getResultSetMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Module::class, 'm');
        $rsm->addFieldResult('m', 'id', 'id');
        $rsm->addFieldResult('m', 'name', 'name');
        $rsm->addFieldResult('m', 'level', 'level');
        $rsm->addFieldResult('m', 'upgrade_factor', 'upgrade_factor');
        $rsm->addFieldResult('m', 'downgrade_factor', 'downgrade_factor');
        $rsm->addFieldResult('m', 'crew', 'crew');
        $rsm->addFieldResult('m', 'type', 'type');
        $rsm->addFieldResult('m', 'research_id', 'research_id');
        $rsm->addFieldResult('m', 'commodity_id', 'commodity_id');
        $rsm->addFieldResult('m', 'viewable', 'viewable');
        $rsm->addFieldResult('m', 'rumps_role_id', 'rumps_role_id');
        $rsm->addFieldResult('m', 'ecost', 'ecost');

        return $rsm;
    }
}
