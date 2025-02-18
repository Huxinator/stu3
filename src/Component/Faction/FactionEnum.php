<?php

declare(strict_types=1);

namespace Stu\Component\Faction;

final class FactionEnum
{
    /** @var array<string, int> */
    public const FACTION_NAME_TO_ID_MAP = [
        'federation' => self::FACTION_FEDERATION,
        'romulan' => self::FACTION_ROMULAN,
        'klingon' => self::FACTION_KLINGON,
        'cardassian' => self::FACTION_CARDASSIAN,
        'ferengi' => self::FACTION_FERENGI,
    ];

    /** @var array<int, string> */
    public const FACTION_ID_TO_COLOR_MAP = [
        self::FACTION_FEDERATION => '#0000ff',
        self::FACTION_ROMULAN => '#00ff00',
        self::FACTION_KLINGON => '#ff0000',
        self::FACTION_CARDASSIAN => '#ff7b42',
        self::FACTION_FERENGI => '#943100',
    ];

    /**
     * @var int
     */
    public const FACTION_FEDERATION = 1;

    /**
     * @var int
     */
    public const FACTION_ROMULAN = 2;

    /**
     * @var int
     */
    public const FACTION_KLINGON = 3;

    /**
     * @var int
     */
    public const FACTION_CARDASSIAN = 4;

    /**
     * @var int
     */
    public const FACTION_FERENGI = 5;

    /**
     * @var int
     */
    public const FACTION_PAKLED = 6;
}
