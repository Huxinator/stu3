<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\AlreadyActiveException;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientCrewException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemCooldownException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotActivatableException;
use Stu\Component\Ship\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Tal\TalHelper;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ActivatorDeactivatorHelper implements ActivatorDeactivatorHelperInterface
{
    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function activate(
        int $shipId,
        int $systemId,
        GameControllerInterface $game,
        bool $allowUplink = false
    ): bool {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId,
            $allowUplink
        );

        if ($this->activateIntern($wrapper, $systemId, $game)) {
            $this->shipRepository->save($wrapper->get());
            return true;
        } else {
            return false;
        }
    }

    private function activateIntern(
        ShipWrapperInterface $wrapper,
        int $systemId,
        GameControllerInterface $game
    ): bool {
        $systemName = ShipSystemTypeEnum::getDescription($systemId);
        $ship = $wrapper->get();

        try {
            $this->shipSystemManager->activate($wrapper, $systemId);
            $game->addInformation(sprintf(_('%s: System %s aktiviert'), $ship->getName(), $systemName));
            return true;
        } catch (AlreadyActiveException $e) {
            $game->addInformation(sprintf(_('%s: System %s ist bereits aktiviert'), $ship->getName(), $systemName));
        } catch (SystemNotActivatableException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s besitzt keinen Aktivierungsmodus[/color][/b]'), $ship->getName(), $systemName));
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(sprintf(
                _('%s: [b][color=FF2626]System %s kann aufgrund Energiemangels (%d benötigt) nicht aktiviert werden[/color][/b]'),
                $ship->getName(),
                $systemName,
                $e->getNeededEnergy()
            ));
        } catch (SystemCooldownException $e) {
            $game->addInformation(sprintf(
                _('%s: [b][color=FF2626]System %s kann nicht aktiviert werden, Cooldown noch %s[/color][/b]'),
                $ship->getName(),
                $systemName,
                TalHelper::formatSeconds(strval($e->getRemainingSeconds()))
            ));
        } catch (SystemDamagedException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s ist beschädigt und kann daher nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName));
        } catch (ActivationConditionsNotMetException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s konnte nicht aktiviert werden, weil %s[/color][/b]'), $ship->getName(), $systemName, $e->getMessage()));
        } catch (SystemNotFoundException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s nicht vorhanden[/color][/b]'), $ship->getName(), $systemName));
        } catch (InsufficientCrewException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s konnte wegen Mangel an Crew nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName));
        } catch (ShipSystemException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s konnte nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName));
        }

        return false;
    }

    public function activateFleet(
        int $shipId,
        int $systemId,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $success = false;
        foreach ($wrapper->getFleetWrapper()->getShipWrappers() as $wrapper) {
            if ($this->activateIntern($wrapper, $systemId, $game)) {
                $success = true;
                $this->shipRepository->save($wrapper->get());
            }
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        $systemName = ShipSystemTypeEnum::getDescription($systemId);
        $game->addInformation(sprintf(_('Flottenbefehl ausgeführt: System %s aktiviert'), $systemName));
    }

    public function deactivate(
        int $shipId,
        int $systemId,
        GameControllerInterface $game,
        bool $allowUplink = false
    ): bool {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId,
            $allowUplink
        );

        if ($this->deactivateIntern($wrapper, $systemId, $game)) {
            $this->shipRepository->save($wrapper->get());
            return true;
        } else {
            return false;
        }
    }

    private function deactivateIntern(
        ShipWrapperInterface $wrapper,
        int $systemId,
        GameControllerInterface $game
    ): bool {
        $systemName = ShipSystemTypeEnum::getDescription($systemId);
        $ship = $wrapper->get();

        try {
            $this->shipSystemManager->deactivate($wrapper, $systemId);
            $game->addInformation(sprintf(_('%s: System %s deaktiviert'), $ship->getName(), $systemName));
            return true;
        } catch (AlreadyOffException $e) {
            $game->addInformation(sprintf(_('%s: System %s ist bereits deaktiviert'), $ship->getName(), $systemName));
        } catch (SystemNotDeactivatableException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s besitzt keinen Deaktivierungsmodus[/color][/b]'), $ship->getName(), $systemName));
        } catch (DeactivationConditionsNotMetException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s konnte nicht deaktiviert werden, weil %s[/color][/b]'), $ship->getName(), $systemName, $e->getMessage()));
        } catch (SystemNotFoundException $e) {
            $game->addInformation(sprintf(_('%s: System %s nicht vorhanden'), $ship->getName(), $systemName));
        }

        return false;
    }

    public function deactivateFleet(
        int $shipId,
        int $systemId,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $success = false;
        foreach ($wrapper->getFleetWrapper()->getShipWrappers() as $wrapper) {
            if ($this->deactivateIntern($wrapper, $systemId, $game)) {
                $success = true;
                $this->shipRepository->save($wrapper->get());
            }
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        $systemName = ShipSystemTypeEnum::getDescription($systemId);
        $game->addInformation(sprintf(_('Flottenbefehl ausgeführt: System %s deaktiviert'), $systemName));
    }

    public function setLSSMode(
        int $shipId,
        int $lssMode,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        $ship->setLSSMode($lssMode);
        $this->shipRepository->save($ship);

        if ($lssMode === ShipLSSModeEnum::LSS_NORMAL) {
            $game->addInformation("Territoriale Grenzanzeige deaktiviert");
        } elseif ($lssMode === ShipLSSModeEnum::LSS_BORDER) {
            $game->addInformation("Territoriale Grenzanzeige aktiviert");
        }
    }

    public function setAlertState(
        int $shipId,
        int $alertState,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        if (!$this->setAlertStateShip($wrapper, $alertState, $game)) {
            return;
        }

        if ($alertState === ShipAlertStateEnum::ALERT_RED) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=red]Rot[/color][/b] geändert");
        } elseif ($alertState === ShipAlertStateEnum::ALERT_YELLOW) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=yellow]Gelb[/color][/b] geändert");
        } elseif ($alertState === ShipAlertStateEnum::ALERT_GREEN) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=green]Grün[/color][/b] geändert");
        }
    }

    public function setAlertStateFleet(
        int $shipId,
        int $alertState,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $success = false;
        foreach ($wrapper->getFleetWrapper()->getShipWrappers() as $wrapper) {
            $success = $this->setAlertStateShip($wrapper, $alertState, $game) || $success;
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        if ($alertState === ShipAlertStateEnum::ALERT_RED) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=red]Rot[/color][/b]'));
        } elseif ($alertState === ShipAlertStateEnum::ALERT_YELLOW) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=yellow]Gelb[/color][/b]'));
        } elseif ($alertState === ShipAlertStateEnum::ALERT_GREEN) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=green]Grün[/color][/b]'));
        }
    }

    private function setAlertStateShip(ShipWrapperInterface $wrapper, int $alertState, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        // station constructions can't change alert state
        if ($ship->isConstruction()) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]Konstrukte können die Alarmstufe nicht ändern[/color][/b]'), $ship->getName()));
            return false;
        }

        // can only change when there is enough crew
        if (!$ship->hasEnoughCrew()) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]Mangel an Crew verhindert den Wechsel der Alarmstufe[/color][/b]'), $ship->getName()));
            return false;
        }

        if ($alertState === ShipAlertStateEnum::ALERT_RED && $ship->getCloakState()) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]Tarnung verhindert den Wechsel zu Alarm-Rot[/color][/b]'), $ship->getName()));
            return false;
        }

        try {
            $alertMsg = null;
            $wrapper->setAlertState($alertState, $alertMsg);
            $this->shipRepository->save($ship);

            if ($alertMsg !== null) {
                $game->addInformation(sprintf(_('%s: [b][color=FAFA03]%s[/color][/b]'), $ship->getName(), $alertMsg));
            }
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]Nicht genügend Energie um die Alarmstufe zu wechseln (%d benötigt)[/color][/b]'), $ship->getName(), $e->getNeededEnergy()));
            return false;
        }

        switch ($alertState) {
            case ShipAlertStateEnum::ALERT_RED:
                $this->setAlertRed($wrapper, $game);
                break;
            case ShipAlertStateEnum::ALERT_YELLOW:
                $this->setAlertYellow($wrapper, $game);
                break;
            case ShipAlertStateEnum::ALERT_GREEN:
                $this->setAlertGreen($wrapper, $game);
                break;
        }

        $this->shipRepository->save($ship);

        return true;
    }

    private function setAlertRed(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $alertSystems = [
            ShipSystemTypeEnum::SYSTEM_SHIELDS,
            ShipSystemTypeEnum::SYSTEM_NBS,
            ShipSystemTypeEnum::SYSTEM_PHASER,
            ShipSystemTypeEnum::SYSTEM_TORPEDO
        ];

        foreach ($alertSystems as $systemId) {
            $this->activateIntern($wrapper, $systemId, $game);
        }
    }

    private function setAlertYellow(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $alertSystems = [
            ShipSystemTypeEnum::SYSTEM_NBS
        ];

        foreach ($alertSystems as $systemId) {
            $this->activateIntern($wrapper, $systemId, $game);
        }
    }

    private function setAlertGreen(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $deactivateSystems = [
            ShipSystemTypeEnum::SYSTEM_PHASER,
            ShipSystemTypeEnum::SYSTEM_TORPEDO,
            ShipSystemTypeEnum::SYSTEM_SHIELDS
        ];

        foreach ($deactivateSystems as $systemId) {
            if ($wrapper->get()->hasShipSystem($systemId)) {
                $this->deactivateIntern($wrapper, $systemId, $game);
            }
        }
    }
}
