<?php

namespace Stu\Orm\Entity;

interface DockingPrivilegeInterface
{
    public function getId(): int;

    public function getShipId(): int;

    public function setShipId(int $shipId): DockingPrivilegeInterface;

    public function getTargetId(): int;

    public function setTargetId(int $targetId): DockingPrivilegeInterface;

    public function getPrivilegeType(): int;

    public function setPrivilegeType(int $privilegeType): DockingPrivilegeInterface;

    public function getPrivilegeMode(): int;

    public function setPrivilegeMode(int $privilegeMode): DockingPrivilegeInterface;

    public function getPrivilegeModeString(): string;

    public function isDockingAllowed(): bool;

    public function getTargetName(): string;
}