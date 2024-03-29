<?php

use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule;

return [
    new MinCoverageRule(
        pattern: MinCoverageRule::TOTAL,
        minCoverage: 20,
        exitOnLowCoverage: true
    ),
    new MinCoverageRule(
        pattern: 'RobinIngelbrecht\PHPUnitCoverageTools\PhpUnitExtension',
        minCoverage: 100,
        exitOnLowCoverage: true
    ),
];
