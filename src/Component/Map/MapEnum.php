<?php

declare(strict_types=1);

namespace Stu\Component\Map;

final class MapEnum
{
    //LAYERS
    public const LAYER_ID_WORMHOLES = 0;
    public const LAYER_ID_CRAGGANMORE = 1;

    //SECTIONS
    public const FIELDS_PER_SECTION = 20;

    //WORMHOLE ENTRY TYPES
    public const WORMHOLE_ENTRY_TYPE_IN = 0;
    public const WORMHOLE_ENTRY_TYPE_OUT = 1;
    public const WORMHOLE_ENTRY_TYPE_BOTH = 2;

    //OTHER
    public const MAPTYPE_INSERT = 1;
    public const MAPTYPE_LAYER_EXPLORED = 2;
}
