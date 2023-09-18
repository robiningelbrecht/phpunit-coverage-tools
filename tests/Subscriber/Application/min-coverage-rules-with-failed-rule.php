<?php

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule;

return [
    new MinCoverageRule(
        pattern: MinCoverageRule::TOTAL,
        minCoverage: 20,
        exitOnLowCoverage: true
    ),
    new MinCoverageRule(
        pattern: 'RobinIngelbrecht\PHPUnitCoverageTools\CoverageMetrics',
        minCoverage: 100,
        exitOnLowCoverage: false
    ),
    new MinCoverageRule(
        pattern: 'RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application\ApplicationFinishedSubscriber',
        minCoverage: 100,
        exitOnLowCoverage: true
    ),
    new MinCoverageRule(
        pattern: 'RobinIngelbrecht\NonExistingNameSpace',
        minCoverage: 100,
        exitOnLowCoverage: true
    ),
];
