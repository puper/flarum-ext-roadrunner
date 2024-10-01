<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\Tests\Worker\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\RoadRunner\Environment;

final class EnvironmentTest extends TestCase
{
    public function testGetModeWithDefault(): void
    {
        $env = new Environment();
        $this->assertEquals('', $env->getMode());
    }

    public function testGetModeWithValue(): void
    {
        $env = new Environment(['RR_MODE' => 'mode_value']);
        $this->assertEquals('mode_value', $env->getMode());
    }

    public function testGetRelayAddressWithDefault(): void
    {
        $env = new Environment();
        $this->assertEquals('pipes', $env->getRelayAddress());
    }

    public function testGetRelayAddressWithValue(): void
    {
        $env = new Environment(['RR_RELAY' => 'relay_value']);
        $this->assertEquals('relay_value', $env->getRelayAddress());
    }

    public function testGetRPCAddressWithDefault(): void
    {
        $env = new Environment();
        $this->assertEquals('tcp://127.0.0.1:6001', $env->getRPCAddress());
    }

    public function testGetRPCAddressWithValue(): void
    {
        $env = new Environment(['RR_RPC' => 'rpc_value']);
        $this->assertEquals('rpc_value', $env->getRPCAddress());
    }

    public function testGetVersionWithValue(): void
    {
        $env = new Environment(['RR_VERSION' => '2024.1.3']);
        $this->assertEquals('2024.1.3', $env->getVersion());
    }

    public function testFromGlobals(): void
    {
        $_ENV['RR_MODE'] = 'global_mode';
        $_SERVER['RR_RELAY'] = 'global_relay';
        $_SERVER['RR_VERSION'] = 'global_version';

        $env = Environment::fromGlobals();

        $this->assertEquals('global_mode', $env->getMode());
        $this->assertEquals('global_relay', $env->getRelayAddress());
        $this->assertEquals('global_version', $env->getVersion());
        $this->assertEquals('tcp://127.0.0.1:6001', $env->getRPCAddress());
    }
}