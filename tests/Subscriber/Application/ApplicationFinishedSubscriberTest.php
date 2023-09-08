<?php

namespace Tests\Subscriber\Application;

use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Builder;
use RobinIngelbrecht\PHPUnitCoverageTools\ConsoleOutput;
use RobinIngelbrecht\PHPUnitCoverageTools\Exitter;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRules;
use RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application\ApplicationFinishedSubscriber;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\SpyOutput;

class ApplicationFinishedSubscriberTest extends TestCase
{
    use MatchesSnapshots;

    public function testNotifyWithAtLeastOneFailedRule(): void
    {
        $exitter = $this->createMock(Exitter::class);

        $exitter
            ->expects($this->once())
            ->method('exit')
            ->with(1);

        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-failed-rule.php'),
            exitOnLowCoverage: true,
            cleanUpCloverXml: false,
            exitter: $exitter,
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWithAWarning(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-warning.php'),
            exitOnLowCoverage: false,
            cleanUpCloverXml: false,
            exitter: new Exitter(),
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWhenCoverageIsOk(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            exitOnLowCoverage: false,
            cleanUpCloverXml: false,
            exitter: new Exitter(),
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWhitOnlyTotal(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-total-only.php'),
            exitOnLowCoverage: false,
            cleanUpCloverXml: false,
            exitter: new Exitter(),
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWhitInvalidRules(): void
    {
        $spyOutput = new SpyOutput();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MinCoverage has to be value between 0 and 100. 203 given');

        new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-invalid.php'),
            exitOnLowCoverage: false,
            cleanUpCloverXml: false,
            exitter: new Exitter(),
            consoleOutput: new ConsoleOutput($spyOutput),
        );
    }

    public function testDivideByZero(): void
    {
        $exitter = $this->createMock(Exitter::class);

        $exitter
            ->expects($this->never())
            ->method('exit');

        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-test-divide-by-zero.xml',
            minCoverageRules: MinCoverageRules::fromInt(100),
            exitOnLowCoverage: true,
            cleanUpCloverXml: false,
            exitter: $exitter,
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertMatchesTextSnapshot($spyOutput);
    }

    public function testNotifyWithNonExistingCloverFile(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-wrong.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            exitOnLowCoverage: false,
            cleanUpCloverXml: false,
            exitter: new Exitter(),
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertEmpty((string) $spyOutput);
    }

    public function testNotifyWithInvalidCloverFile(): void
    {
        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-invalid.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            exitOnLowCoverage: false,
            cleanUpCloverXml: false,
            exitter: new Exitter(),
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not determine coverage metrics');

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));
    }

    public function testNotifyWithCleanUpCloverFile(): void
    {
        copy(dirname(__DIR__, 2).'/clover.xml', dirname(__DIR__, 2).'/clover-to-delete.xml');
        $exitter = $this->createMock(Exitter::class);

        $exitter
            ->expects($this->once())
            ->method('exit')
            ->with(1);

        $spyOutput = new SpyOutput();
        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-to-delete.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-failed-rule.php'),
            exitOnLowCoverage: true,
            cleanUpCloverXml: true,
            exitter: $exitter,
            consoleOutput: new ConsoleOutput($spyOutput),
        );

        $subscriber->notify(event: new Finished(
            new Info(
                current: new Snapshot(
                    time: HRTime::fromSecondsAndNanoseconds(1, 0),
                    memoryUsage: MemoryUsage::fromBytes(100),
                    peakMemoryUsage: MemoryUsage::fromBytes(100),
                    garbageCollectorStatus: new GarbageCollectorStatus(0, 0, 0, 0, null, null, null, null, null, null, null, null)
                ),
                durationSinceStart: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySinceStart: MemoryUsage::fromBytes(100),
                durationSincePrevious: Duration::fromSecondsAndNanoseconds(1, 0),
                memorySincePrevious: MemoryUsage::fromBytes(100),
            ),
            0
        ));

        $this->assertFileDoesNotExist(dirname(__DIR__, 2).'/clover-to-delete.xml');
    }

    public function testFromConfigurationAndParameters(): void
    {
        $this->assertEquals(
            new ApplicationFinishedSubscriber(
                relativePathToCloverXml: 'tests/clover.xml',
                minCoverageRules: MinCoverageRules::fromInt(90),
                exitOnLowCoverage: false,
                cleanUpCloverXml: true,
                exitter: new Exitter(),
                consoleOutput: new ConsoleOutput(new \Symfony\Component\Console\Output\ConsoleOutput()),
            ),
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                ParameterCollection::fromArray([]),
                ['--min-coverage=90', '--clean-up-clover-xml']
            ),
        );
    }

    public function testFromConfigurationAndParametersFromFile(): void
    {
        $this->assertEquals(
            expected: new ApplicationFinishedSubscriber(
                relativePathToCloverXml: 'tests/clover.xml',
                minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
                exitOnLowCoverage: false,
                cleanUpCloverXml: false,
                exitter: new Exitter(),
                consoleOutput: new ConsoleOutput(new \Symfony\Component\Console\Output\ConsoleOutput()),
            ),
            actual: ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                configuration: (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                parameters: ParameterCollection::fromArray([]),
                args: ['--min-coverage="tests/Subscriber/Application/min-coverage-rules-success.php"']
            ),
        );
    }

    public function testFromConfigurationAndParametersWhenInvalidMinCoverage(): void
    {
        $this->assertNull(
            actual: ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                configuration: (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                parameters: ParameterCollection::fromArray([]),
                args: ['--min-coverage=a-word']
            ),
        );
    }

    public function testFromConfigurationAndParametersWhenCoverageTooHigh(): void
    {
        $this->assertNull(
            actual: ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                configuration: (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                parameters: ParameterCollection::fromArray([]),
                args: ['--min-coverage=101']
            ),
        );
    }

    public function testFromConfigurationAndParametersWhenRulesAreEmpty(): void
    {
        $this->assertNull(
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                configuration: (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                parameters: ParameterCollection::fromArray([]),
                args: ['--min-coverage="tests/Subscriber/Application/min-coverage-rules-empty.php"']
            ),
        );
    }
}
