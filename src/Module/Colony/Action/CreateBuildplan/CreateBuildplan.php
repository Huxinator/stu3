<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\CreateBuildplan;

use request;

use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Exception\AccessViolation;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Colony\View\ShowModuleScreen\ShowModuleScreen;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class CreateBuildplan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILDPLAN_SAVE';

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ModuleRepositoryInterface $moduleRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    public function __construct(
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ModuleRepositoryInterface $moduleRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        EntityManagerInterface $entityManager,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->moduleRepository = $moduleRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
    }

    private function exitOnError(GameControllerInterface $game): void
    {
        $game->setView(ShowModuleScreen::VIEW_IDENTIFIER);
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $game->setView(ShowModuleScreen::VIEW_IDENTIFIER);

        //$this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $rump = $this->shipRumpRepository->find((int) request::indInt('rump'));
        if ($rump === null) {
            $this->loggerUtil->log('A');
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('B');

        if (!array_key_exists($rump->getId(), $this->shipRumpRepository->getBuildableByUser($userId))) {
            throw new AccessViolation(sprintf(
                'userId %d tried to create a buildplan with rump %s, but has not researched the rump',
                $userId,
                $rump->getName()
            ));
        }

        $moduleLevels = $this->shipRumpModuleLevelRepository->getByShipRump($rump->getId());

        $modules = [];
        $sigmod = [];
        $crew_usage = $rump->getBaseCrew();
        $error = false;
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {
            $this->loggerUtil->log(sprintf('%d', $i));
            $module = request::postArray('mod_' . $i);
            if (
                $i != ShipModuleTypeEnum::MODULE_TYPE_SPECIAL
                && $moduleLevels->{'getModuleMandatory' . $i}() == ShipModuleTypeEnum::MODULE_MANDATORY
                && count($module) == 0
            ) {
                $game->addInformationf(
                    _('Es wurde kein Modul des Typs %s ausgewählt'),
                    ModuleTypeDescriptionMapper::getDescription($i)
                );
                $this->loggerUtil->log('C');
                $this->exitOnError($game);
                $error = true;
            }
            if ($i === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                $specialCount = 0;
                foreach ($module as $id) {
                    $specialMod = $this->moduleRepository->find((int) $id);

                    if ($specialMod === null) {
                        continue;
                    }

                    $crew_usage += $specialMod->getCrew();
                    $modules[$id] = $specialMod;
                    $sigmod[$id] = $id;
                    $specialCount++;
                }

                if ($specialCount > $rump->getSpecialSlots()) {
                    $game->addInformation(_('Mehr Spezial-Module als der Rumpf gestattet'));
                    $this->loggerUtil->log('D');
                    $this->exitOnError($game);
                    $error = true;
                }
                continue;
            }
            if (count($module) == 0 || current($module) == 0) {
                $sigmod[$i] = 0;
                continue;
            }
            $mod = null;
            if (current($module) > 0) {
                /** @var ModuleInterface $mod */
                $mod = $this->moduleRepository->find((int) current($module));
                if ($mod->getLevel() > $rump->getModuleLevel()) {
                    $crew_usage += $mod->getCrew() + 1;
                } else {
                    $crew_usage += $mod->getCrew();
                }
            } else {
                if (!$moduleLevels->{'getModuleLevel' . $i}()) {
                    $this->exitOnError($game);
                    return;
                }
            }
            if ($mod !== null) {
                $modules[current($module)] = $mod;
                $sigmod[$i] = $mod->getId();
            }
        }

        if ($error) {
            return;
        }

        $this->loggerUtil->log('E');
        if ($crew_usage > $this->shipCrewCalculator->getMaxCrewCountByRump($rump)) {
            $game->addInformation(_('Crew-Maximum wurde überschritten'));
            $this->loggerUtil->log('F');
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('G');
        $signature = ShipBuildplan::createSignature($sigmod, $crew_usage);
        if (
            request::has('buildplanname')
            && request::indString('buildplanname') != ''
            && request::indString('buildplanname') != 'Bauplanname'
        ) {
            $planname = request::indString('buildplanname');

            $planname = CleanTextUtils::clearEmojis($planname);
            $nameWithoutUnicode = CleanTextUtils::clearUnicode($planname);
            if ($planname != $nameWithoutUnicode) {
                $game->addInformation(_('Der Name enthält ungültigen Unicode'));
                $this->exitOnError($game);
                return;
            }

            if (mb_strlen($planname) > 255) {
                $game->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
                $this->exitOnError($game);
                return;
            }
        } else {
            $planname = sprintf(
                _('Bauplan %s %s'),
                $rump->getName(),
                date('d.m.Y H:i')
            );
        }
        if ($this->shipBuildplanRepository->findByUserAndName($userId, $planname) !== null) {
            $game->addInformation(_('Ein Bauplan mit diesem Namen existiert bereits'));
            $this->exitOnError($game);
            return;
        }
        $this->loggerUtil->log('H');
        $game->addInformationf(
            _('Lege neuen Bauplan an: %s'),
            $planname
        );
        $plan = $this->shipBuildplanRepository->prototype();
        $plan->setUser($user);
        $plan->setRump($rump);
        $plan->setName($planname);
        $plan->setSignature($signature);
        $plan->setBuildtime($rump->getBuildtime());
        $plan->setCrew($crew_usage);

        $this->shipBuildplanRepository->save($plan);
        $this->entityManager->flush();

        $this->loggerUtil->log('I');

        /**
         * @var ModuleInterface[] $modules
         */
        foreach ($modules as $obj) {
            $mod = $this->buildplanModuleRepository->prototype();
            $mod->setModuleType((int) $obj->getType());
            $mod->setBuildplan($plan);
            $mod->setModule($obj);
            $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($obj->getSpecials()));

            $this->buildplanModuleRepository->save($mod);
        }

        $this->loggerUtil->log('J');

        $game->setView(ShowModuleScreenBuildplan::VIEW_IDENTIFIER);
        request::setVar('planid', $plan->getId());

        $this->loggerUtil->log('K');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
