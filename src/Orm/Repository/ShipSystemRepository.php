<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipSystem;
use Stu\Orm\Entity\ShipSystemInterface;

/**
 * @extends EntityRepository<ShipSystem>
 */
final class ShipSystemRepository extends EntityRepository implements ShipSystemRepositoryInterface
{

    public function prototype(): ShipSystemInterface
    {
        return new ShipSystem();
    }

    public function save(ShipSystemInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ShipSystemInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getByShip(int $shipId): array
    {
        return $this->findBy(
            ['ship_id' => $shipId],
            ['system_type' => 'asc']
        );
    }

    public function getTrackingShipSystems(int $targetId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ss FROM %s ss
                    WHERE ss.system_type = :systemType
                    AND ss.data LIKE :target',
                    ShipSystem::class
                )
            )
            ->setParameters([
                'systemType' => ShipSystemTypeEnum::SYSTEM_TRACKER,
                'target' => sprintf('%%"targetId":%d%%', $targetId)
            ])
            ->getResult();
    }

    public function getWebConstructingShipSystems(int $webId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ss FROM %s ss
                    WHERE ss.system_type = :systemType
                    AND ss.data LIKE :target',
                    ShipSystem::class
                )
            )
            ->setParameters([
                'systemType' => ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB,
                'target' => sprintf('%%"webUnderConstructionId":%d%%', $webId)
            ])
            ->getResult();
    }

    public function truncateByShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s s WHERE s.ship_id = :shipId',
                    ShipSystem::class
                )
            )
            ->setParameter('shipId', $shipId)
            ->execute();
    }
}
