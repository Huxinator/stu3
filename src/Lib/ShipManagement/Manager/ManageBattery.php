<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use RuntimeException;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class ManageBattery implements ManagerInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(PrivateMessageSenderInterface $privateMessageSender)
    {
        $this->privateMessageSender = $privateMessageSender;
    }

    public function manage(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];

        $batt = $values['batt'] ?? null;
        if ($batt === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();
        $shipId = $ship->getId();
        $epsSystem = $wrapper->getEpsSystemData();

        if (
            $epsSystem !== null
            && array_key_exists(
                $shipId,
                $batt
            )
            && $managerProvider->getEps() > 0
            && $epsSystem->getBattery() < $epsSystem->getMaxBattery()
        ) {
            $load = $this->determineLoad($batt[$shipId], $epsSystem, $managerProvider);

            if ($load > 0) {
                $epsSystem->setBattery($epsSystem->getBattery() + $load)->update();
                $managerProvider->lowerEps($load);
                $msg[] = sprintf(
                    _('%s: Batterie um %d Einheiten aufgeladen'),
                    $ship->getName(),
                    $load
                );

                $this->sendMessageToOwner($ship, $managerProvider, $load);
            }
        }

        return $msg;
    }

    private function determineLoad(
        string $value,
        EpsSystemData $epsSystem,
        ManagerProviderInterface $managerProvider
    ): int {
        if ($value == 'm') {
            $load = $epsSystem->getMaxBattery() - $epsSystem->getBattery();
        } else {
            $load = (int) $value;
            if ($epsSystem->getBattery() + $load > $epsSystem->getMaxBattery()) {
                $load = $epsSystem->getMaxBattery() - $epsSystem->getBattery();
            }
        }
        if ($load > $managerProvider->getEps()) {
            $load = $managerProvider->getEps();
        }

        return $load;
    }

    private function sendMessageToOwner(ShipInterface $ship, ManagerProviderInterface $managerProvider, int $load): void
    {
        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $ship->getId());

        $this->privateMessageSender->send(
            $managerProvider->getUser()->getId(),
            $ship->getUser()->getId(),
            sprintf(
                _('Die %s lädt in Sektor %s die Batterie der %s um %s Einheiten'),
                $managerProvider->getName(),
                $managerProvider->getSectorString(),
                $ship->getName(),
                $load
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            $href
        );
    }
}
