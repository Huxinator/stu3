<?php

namespace Stu\Orm\Entity;

use ModulesData;

interface ModuleBuildingFunctionInterface
{
    public function getId(): int;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): ModuleBuildingFunctionInterface;

    public function getBuildingFunction(): int;

    public function setBuildingFunction(int $buildingFunction): ModuleBuildingFunctionInterface;

    public function getModule(): ModulesData;
}