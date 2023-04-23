<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Builder;
use RobinIngelbrecht\PHPUnitCoverageTools\PhpUnitExtension;

class PhpUnitExtensionTest extends TestCase
{
    private array $originalArgv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalArgv = $_SERVER['argv'];
        foreach ($this->originalArgv as $key => $arg) {
            if (!str_starts_with($arg, '--min-coverage=')) {
                continue;
            }

            unset($_SERVER['argv'][$key]);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER['argv'] = $this->originalArgv;
    }

    public function testItShouldRegisterSubscribers(): void
    {
        $configuration = (new Builder())->build([
            '--coverage-clover=clover.xml',
        ]);

        $facade = $this->createMock(Facade::class);
        $parameters = ParameterCollection::fromArray([]);

        $extension = new PhpUnitExtension();

        $facade
            ->expects($this->once())
            ->method('registerSubscribers');

        $_SERVER['argv'] = [...$this->originalArgv, '--min-coverage=90'];

        $extension->bootstrap(
            $configuration,
            $facade,
            $parameters
        );
    }

    public function testItShouldShortcutWhenNoClover(): void
    {
        $configuration = (new Builder())->build([]);
        $facade = $this->createMock(Facade::class);
        $parameters = ParameterCollection::fromArray([]);

        $extension = new PhpUnitExtension();

        $facade
            ->expects($this->never())
            ->method('registerSubscribers');

        $_SERVER['argv'] = [...$this->originalArgv, '--min-coverage=90'];

        $extension->bootstrap(
            $configuration,
            $facade,
            $parameters,
        );
    }

    public function testItShouldShortcutWhenNoMinCoverage(): void
    {
        $configuration = (new Builder())->build([
            '--coverage-clover=clover.xml',
        ]);
        $facade = $this->createMock(Facade::class);
        $parameters = ParameterCollection::fromArray([]);

        $extension = new PhpUnitExtension();

        $facade
            ->expects($this->never())
            ->method('registerSubscribers');

        $extension->bootstrap(
            $configuration,
            $facade,
            $parameters
        );
    }
}
