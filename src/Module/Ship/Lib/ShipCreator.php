<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperComputer;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEnergyWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperEps;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperHull;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperImpulseDrive;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperInterface;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperProjectileWeapon;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperReactor;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperShield;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperSpecial;
use Stu\Lib\ModuleRumpWrapper\ModuleRumpWrapperWarpcore;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleSpecialRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShipCreator implements ShipCreatorInterface
{
    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ModuleSpecialRepositoryInterface $moduleSpecialRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ModuleSpecialRepositoryInterface $moduleSpecialRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->moduleSpecialRepository = $moduleSpecialRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function createBy(
        int $userId,
        int $shipRumpId,
        int $shipBuildplanId,
        ?ColonyInterface $colony = null,
        ?ConstructionProgressInterface $progress = null
    ): ShipWrapperInterface {
        $ship = $progress !== null ? $progress->getShip() : $this->shipRepository->prototype();
        $ship->setUser($this->userRepository->find($userId));
        $ship->setBuildplan($this->shipBuildplanRepository->find($shipBuildplanId));
        $ship->setRump($this->shipRumpRepository->find($shipRumpId));
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        //create ship systems        
        $this->createShipSystemsByModuleList(
            $ship,
            $this->buildplanModuleRepository->getByBuildplan(
                $ship->getBuildplan()->getId()
            ),
            $progress
        );

        $moduleTypeList = [
            ShipModuleTypeEnum::MODULE_TYPE_HULL => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperHull($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_HULL));
            },
            ShipModuleTypeEnum::MODULE_TYPE_SHIELDS => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperShield($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_SHIELDS));
            },
            ShipModuleTypeEnum::MODULE_TYPE_EPS => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperEps($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_EPS));
            },
            ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperImpulseDrive($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE));
            },
            ShipModuleTypeEnum::MODULE_TYPE_WARPCORE => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperWarpcore($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_WARPCORE));
            },
            ShipModuleTypeEnum::MODULE_TYPE_REACTOR => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperReactor($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_REACTOR));
            },
            ShipModuleTypeEnum::MODULE_TYPE_COMPUTER => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperComputer($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_COMPUTER));
            },
            ShipModuleTypeEnum::MODULE_TYPE_PHASER => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperEnergyWeapon($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_PHASER));
            },
            ShipModuleTypeEnum::MODULE_TYPE_TORPEDO => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                return new ModuleRumpWrapperProjectileWeapon($wrapper, $ship->getRump(), $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_TORPEDO));
            },
            ShipModuleTypeEnum::MODULE_TYPE_SPECIAL => function (ShipInterface $ship, ShipWrapperInterface $wrapper): ModuleRumpWrapperInterface {
                $specialMods = $ship->getBuildplan()->getModulesByType(ShipModuleTypeEnum::MODULE_TYPE_SPECIAL);
                return new ModuleRumpWrapperSpecial($wrapper, $ship->getRump(), $specialMods);
            },
        ];

        $wrapper = $this->shipWrapperFactory->wrapShip($ship);

        foreach ($moduleTypeList as $moduleTypeId => $wrapperCallable) {
            if ($this->loggerUtil->doLog()) {
                $this->loggerUtil->log(sprintf("moduleTypeId: %d", $moduleTypeId));
            }
            $buildplanModules = $ship->getBuildplan()->getModulesByType($moduleTypeId);
            if (!empty($buildplanModules)) {
                if ($this->loggerUtil->doLog()) {
                    $this->loggerUtil->log(sprintf("wrapperCallable!"));
                }
                $moduleRumpWrapper = $wrapperCallable($ship, $wrapper);
                $moduleRumpWrapper->apply($ship);
            }
        }

        if ($ship->getName() == '' || $ship->getName() == sprintf('%s in Bau', $ship->getRump()->getName())) {
            $ship->setName($ship->getRump()->getName());
        }
        $ship->setSensorRange($ship->getRump()->getBaseSensorRange());

        $ship->setAlertStateGreen();

        $this->shipRepository->save($ship);
        if ($colony) {
            $starsystemMap = $this->starSystemMapRepository->getByCoordinates($colony->getSystem()->getId(), $colony->getSx(), $colony->getSy());

            $ship->setCx($colony->getSystem()->getCx());
            $ship->setCy($colony->getSystem()->getCy());
            $ship->setStarsystemMap($starsystemMap);
            $this->shipRepository->save($ship);
        }

        return $wrapper;
    }

    private function createShipSystemsByModuleList(
        ShipInterface $ship,
        array $modules,
        ?ConstructionProgressInterface $progress
    ): void {
        $systems = [];

        //default systems, that almost every ship should have
        if ($ship->getRump()->getCategoryId() !== ShipRumpEnum::SHIP_CATEGORY_SHUTTLE) {
            $systems[ShipSystemTypeEnum::SYSTEM_DEFLECTOR] = 0;
            $systems[ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM] = 0;
        }
        $systems[ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT] = 0;
        //TODO transporter

        if ($ship->getRump()->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_STATION) {
            $systems[ShipSystemTypeEnum::SYSTEM_BEAM_BLOCKER] = 0;
        }

        if ($ship->getRump()->isShipyard()) {
            $systems[ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB] = 0;
        }

        if ($ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR) {
            $systems[ShipSystemTypeEnum::SYSTEM_UPLINK] = 0;
        }

        foreach ($modules as $module) {
            switch ($module->getModule()->getType()) {
                case ShipModuleTypeEnum::MODULE_TYPE_SHIELDS:
                    $systems[ShipSystemTypeEnum::SYSTEM_SHIELDS] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_EPS:
                    $systems[ShipSystemTypeEnum::SYSTEM_EPS] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE:
                    $systems[ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_WARPCORE:
                    $systems[ShipSystemTypeEnum::SYSTEM_WARPCORE] = $module->getModule();
                    if ($ship->getRump()->getCategoryId() !== ShipRumpEnum::SHIP_CATEGORY_STATION) {
                        $systems[ShipSystemTypeEnum::SYSTEM_WARPDRIVE] = $module->getModule();
                    }
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_REACTOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_COMPUTER:
                    $systems[ShipSystemTypeEnum::SYSTEM_COMPUTER] = $module->getModule();
                    $systems[ShipSystemTypeEnum::SYSTEM_LSS] = 0;
                    $systems[ShipSystemTypeEnum::SYSTEM_NBS] = 0;
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_PHASER:
                    $systems[ShipSystemTypeEnum::SYSTEM_PHASER] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_TORPEDO:
                    $systems[ShipSystemTypeEnum::SYSTEM_TORPEDO] = $module->getModule();
                    break;
                case ShipModuleTypeEnum::MODULE_TYPE_SPECIAL:
                    $this->addSpecialSystems($module->getModule(), $systems);
                    break;
            }
        }
        if ($progress !== null) {
            foreach ($progress->getSpecialModules() as $mod) {
                $this->addSpecialSystems($mod->getModule(), $systems);
            }
        }
        foreach ($systems as $systemType => $module) {
            $obj = $this->shipSystemRepository->prototype();
            $obj->setShip($ship);
            $ship->getSystems()->set($systemType, $obj);
            $obj->setSystemType($systemType);
            if ($module !== 0) {
                $obj->setModule($module);
            }
            $obj->setStatus(100);
            $obj->setMode(ShipSystemModeEnum::MODE_OFF);

            $this->shipSystemRepository->save($obj);
        }
    }

    private function addSpecialSystems($module, &$systems): void
    {
        $moduleSpecials = $this->moduleSpecialRepository->getByModule($module->getId());

        foreach ($moduleSpecials as $special) {
            switch ($special->getSpecialId()) {
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_CLOAK:
                    $systems[ShipSystemTypeEnum::SYSTEM_CLOAK] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TACHYON_SCANNER:
                    $systems[ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TROOP_QUARTERS:
                    $systems[ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_ASTRO_LABORATORY:
                    $systems[ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_MATRIX_SENSOR:
                    $systems[ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TORPEDO_STORAGE:
                    $systems[ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_SHUTTLE_RAMP:
                    $systems[ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_TRANSWARP_COIL:
                    $systems[ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_HIROGEN_TRACKER:
                    $systems[ShipSystemTypeEnum::SYSTEM_TRACKER] = $module;
                    break;
                case ModuleSpecialAbilityEnum::MODULE_SPECIAL_THOLIAN_WEB:
                    $systems[ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB] = $module;
                    break;
            }
        }
    }
}
