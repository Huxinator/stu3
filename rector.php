<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->importNames();

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_74,
    ]);

    $rectorConfig->skip([
        SimplifyBoolIdenticalTrueRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        PostIncDecToPreIncDecRector::class,
        __DIR__ . '/src/OrmProxy'
    ]);

    $rectorConfig->ruleWithConfiguration(ConsistentPregDelimiterRector::class, [
        ConsistentPregDelimiterRector::DELIMITER => '/',
    ]);
};
