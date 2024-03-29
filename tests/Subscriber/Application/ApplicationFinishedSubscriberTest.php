<?php

namespace Tests\Subscriber\Application;

use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Builder;
use RobinIngelbrecht\PHPUnitCoverageTools\ConsoleOutput;
use RobinIngelbrecht\PHPUnitCoverageTools\Exitter;
use RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRules;
use RobinIngelbrecht\PHPUnitCoverageTools\Subscriber\Application\ApplicationFinishedSubscriber;
use RobinIngelbrecht\PHPUnitCoverageTools\Timer\ResourceUsageFormatter;
use RobinIngelbrecht\PHPUnitCoverageTools\Timer\SystemTimer;
use RobinIngelbrecht\PHPUnitCoverageTools\Timer\Timer;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\FixedResourceUsageFormatter;
use Tests\PausedTimer;
use Tests\SpyOutput;

class ApplicationFinishedSubscriberTest extends TestCase
{
    use MatchesSnapshots;

    private MockObject $exitter;
    private Timer $timer;
    private ResourceUsageFormatter $resourceUsageFormatter;
    private SpyOutput $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exitter = $this->createMock(Exitter::class);
        $this->output = new SpyOutput();
        $this->timer = PausedTimer::withDuration(\SebastianBergmann\Timer\Duration::fromMicroseconds(350123));
        $this->resourceUsageFormatter = FixedResourceUsageFormatter::withUsageInMb(12.00);
    }

    public function testNotifyWithAtLeastOneFailedRule(): void
    {
        $this->exitter
            ->expects($this->once())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-failed-rule.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testNotifyWithAWarning(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-warning.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testNotifyWhenCoverageIsOk(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testNotifyWithOnlyTotal(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-total-only.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testNotifyWithoutTotal(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-without-total.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testNotifyWithRulesThatDoNotExit(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-no-exit.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testDivideByZero(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-test-divide-by-zero.xml',
            minCoverageRules: MinCoverageRules::fromInt(100, true),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testNotifyWhenNoTrackedLines(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-with-no-tracked-lines.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-no-tracked-lines.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertMatchesTextSnapshot($this->output);
    }

    public function testNotifyWithNonExistingCloverFile(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-wrong.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->assertEmpty((string) $this->output);
    }

    public function testNotifyWithInvalidCloverFile(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-invalid.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-success.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

        $this->exitter
            ->expects($this->once())
            ->method('exit');

        $subscriber = new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover-to-delete.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-failed-rule.php'),
            cleanUpCloverXml: true,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
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

    public function testNotifyWithDuplicatePatterns(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Make sure all coverage rule patterns are unique');

        new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-with-duplicates.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
        );
    }

    public function testNotifyWithInvalidRules(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MinCoverage has to be value between 0 and 100. 203 given');

        new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-rules-invalid.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
        );
    }

    public function testNotifyWithInvalidRuleInstances(): void
    {
        $this->exitter
            ->expects($this->never())
            ->method('exit');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Make sure all coverage rules are of instance RobinIngelbrecht\PHPUnitCoverageTools\MinCoverage\MinCoverageRule');

        new ApplicationFinishedSubscriber(
            relativePathToCloverXml: 'tests/clover.xml',
            minCoverageRules: MinCoverageRules::fromConfigFile('tests/Subscriber/Application/min-coverage-invalid-rule-instances.php'),
            cleanUpCloverXml: false,
            exitter: $this->exitter,
            consoleOutput: new ConsoleOutput($this->output, $this->resourceUsageFormatter),
            timer: $this->timer,
        );
    }

    public function testFromConfigurationAndParameters(): void
    {
        $this->assertEquals(
            new ApplicationFinishedSubscriber(
                relativePathToCloverXml: 'tests/clover.xml',
                minCoverageRules: MinCoverageRules::fromInt(90, false),
                cleanUpCloverXml: true,
                exitter: new Exitter(),
                consoleOutput: ConsoleOutput::create(),
                timer: SystemTimer::create(),
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

    public function testFromConfigurationAndParameters2(): void
    {
        $this->assertEquals(
            new ApplicationFinishedSubscriber(
                relativePathToCloverXml: 'tests/clover.xml',
                minCoverageRules: MinCoverageRules::fromInt(90, true),
                cleanUpCloverXml: true,
                exitter: new Exitter(),
                consoleOutput: ConsoleOutput::create(),
                timer: SystemTimer::create(),
            ),
            ApplicationFinishedSubscriber::fromConfigurationAndParameters(
                (new Builder())->build([
                    '--coverage-clover=tests/clover.xml',
                ]),
                ParameterCollection::fromArray([
                    'exitOnLowCoverage' => '1',
                ]),
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
                cleanUpCloverXml: false,
                exitter: new Exitter(),
                consoleOutput: ConsoleOutput::create(),
                timer: SystemTimer::create(),
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
