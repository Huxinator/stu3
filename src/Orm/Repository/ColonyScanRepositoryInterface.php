<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyScanInterface;
use Stu\Orm\Entity\ColonyScan;

/**
 * @extends ObjectRepository<ColonyScan>
 */
interface ColonyScanRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyScanInterface;

    public function save(ColonyScanInterface $post): void;

    public function delete(ColonyScanInterface $post): void;

    /**
     * @return list<ColonyScanInterface>
     */
    public function getByUser(int $userId): array;

    public function truncateByUserId(ColonyScanInterface $userId): void;

    public function getSurface(int $colonyId): array;


    /**
     * @return array<ColonyScanInterface>
     */
    public function getEntryByUserAndSystem(int $user, int $systemId): array;

    public function getSurfaceArray(int $id): string;

    public function getSurfaceWidth(int $id): int;
}
