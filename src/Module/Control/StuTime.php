<?php

namespace Stu\Module\Control;

/**
 * This class adds the possibility to inject a timestamp generator
 */
class StuTime
{
    public function time(): int
    {
        return time();
    }

    public function transformToStuDate(int $unixTimestamp): string
    {
        return date("d.m.", $unixTimestamp) . (date("Y", $unixTimestamp) + 370) . " " . date("H:i", $unixTimestamp);
    }
}
