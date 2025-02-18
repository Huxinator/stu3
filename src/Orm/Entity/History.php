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
 * @Entity(repositoryClass="Stu\Orm\Repository\HistoryRepository")
 * @Table(
 *     name="stu_history",
 *     indexes={
 *         @Index(name="type_idx",columns={"type"})
 *     }
 * )
 **/
class History implements HistoryInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="text")
     *
     */
    private string $text = '';

    /**
     * @Column(type="integer")
     *
     */
    private int $date = 0;

    /**
     * @Column(type="smallint")
     *
     */
    private int $type = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): HistoryInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): HistoryInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): HistoryInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): HistoryInterface
    {
        $this->user_id = $userId;

        return $this;
    }
}
