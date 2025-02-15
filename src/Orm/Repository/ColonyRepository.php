<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegionSettlement;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Colony>
 */
final class ColonyRepository extends EntityRepository implements ColonyRepositoryInterface
{
    public function prototype(): ColonyInterface
    {
        return new Colony();
    }

    public function save(ColonyInterface $colony): void
    {
        $em = $this->getEntityManager();

        $em->persist($colony);
    }

    public function delete(ColonyInterface $colony): void
    {
        $em = $this->getEntityManager();

        $em->remove($colony);
        $em->flush();
    }

    public function getAmountByUser(UserInterface $user, int $colonyType): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(c.id) from %s c WHERE c.user_id = :userId AND c.colonies_classes_id IN (
                        SELECT cc.id FROM %s cc WHERE cc.type = :type
                    )',
                    Colony::class,
                    ColonyClass::class
                )
            )
            ->setParameters([
                'userId' => $user,
                'type' => $colonyType
            ])
            ->getSingleScalarResult();
    }

    public function getStartingByFaction(int $factionId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c INDEX BY c.id
                     JOIN %s sm
                     WITH c.starsystem_map_id = sm.id
                     WHERE c.user_id = :userId AND c.colonies_classes_id IN (
                        SELECT pt.id FROM %s pt WHERE pt.allow_start = :allowStart
                    ) AND sm.systems_id IN (
                        SELECT m.systems_id FROM %s m WHERE m.systems_id > 0 AND m.admin_region_id IN (
                            SELECT mrs.region_id from %s mrs WHERE mrs.faction_id = :factionId
                        )
                    )',
                    Colony::class,
                    StarSystemMap::class,
                    ColonyClass::class,
                    Map::class,
                    MapRegionSettlement::class
                )
            )
            ->setParameters([
                'allowStart' => 1,
                'userId' => GameEnum::USER_NOONE,
                'factionId' => $factionId
            ])
            ->getResult();
    }

    public function getByPosition(StarSystemMapInterface $sysmap): ?ColonyInterface
    {
        return $this->findOneBy([
            'starsystem_map_id' => $sysmap->getId()
        ]);
    }

    public function getForeignColoniesInBroadcastRange(
        StarSystemMapInterface $systemMap,
        UserInterface $user
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                     JOIN %s sm
                     WITH c.starsystem_map_id = sm.id
                     WHERE c.user_id NOT IN (:ignoreIds)
                     AND sm.systems_id = :systemId
                     AND sm.sx BETWEEN (:sx - 1) AND (:sx + 1)
                     AND sm.sy BETWEEN (:sy - 1) AND (:sy + 1)',
                    Colony::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'ignoreIds' => [$user->getId(), GameEnum::USER_NOONE],
                'systemId' => $systemMap->getSystem()->getId(),
                'sx' => $systemMap->getSx(),
                'sy' => $systemMap->getSy()
            ])
            ->getResult();
    }

    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c
                    WHERE MOD(c.user_id, :groupCount) + 1 = :groupId
                    AND c.user_id != :userId',
                    Colony::class
                )
            )
            ->setParameters([
                'groupId' => $batchGroup,
                'groupCount' => $batchGroupCount,
                'userId' => GameEnum::USER_NOONE
            ])
            ->getResult();
    }

    public function getColonized(): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c WHERE c.user_id != :userId',
                    Colony::class
                )
            )
            ->setParameters([
                'userId' => GameEnum::USER_NOONE,
            ])
            ->getResult();
    }
}
