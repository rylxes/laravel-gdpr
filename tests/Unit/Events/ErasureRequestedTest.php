<?php

namespace Rylxes\Gdpr\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Events\ErasureRequested;
use PHPUnit\Framework\Attributes\Test;

class ErasureRequestedTest extends TestCase
{
    #[Test]
    public function it_has_correct_constructor_parameters(): void
    {
        $reflection = new ReflectionClass(ErasureRequested::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        $params = $constructor->getParameters();
        $this->assertCount(4, $params);
        $this->assertEquals('erasureRequestId', $params[0]->getName());
        $this->assertEquals('userId', $params[1]->getName());
        $this->assertEquals('strategy', $params[2]->getName());
        $this->assertEquals('scheduledAt', $params[3]->getName());
    }
    #[Test]
    public function it_has_readonly_properties(): void
    {
        $reflection = new ReflectionClass(ErasureRequested::class);

        $this->assertTrue($reflection->getProperty('erasureRequestId')->isReadOnly());
        $this->assertTrue($reflection->getProperty('userId')->isReadOnly());
        $this->assertTrue($reflection->getProperty('strategy')->isReadOnly());
        $this->assertTrue($reflection->getProperty('scheduledAt')->isReadOnly());
    }
    #[Test]
    public function it_uses_dispatchable_trait(): void
    {
        $reflection = new ReflectionClass(ErasureRequested::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }
}
