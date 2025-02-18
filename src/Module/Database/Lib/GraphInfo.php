<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

final class GraphInfo
{
    public string $title;

    /** @var PlotInfo[] */
    private $plotInfos;

    public bool $yAxisStartAtZero;

    function __construct(
        string $title,
        array $plotInfos,
        bool $yAxisStartAtZero = false
    ) {
        $this->title = $title;
        $this->plotInfos = $plotInfos;
        $this->yAxisStartAtZero = $yAxisStartAtZero;
    }

    /**
     * @return PlotInfo[]
     */
    public function getPlotInfos(): array
    {
        return $this->plotInfos;
    }
}
