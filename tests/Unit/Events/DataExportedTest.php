<?php

namespace Rylxes\Gdpr\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Events\DataExported;
use PHPUnit\Framework\Attributes\Test;

class DataExportedTest extends TestCase
{
    #[Test]
    public function it_has_correct_constructor_parameters(): void
    {
        $reflection = new ReflectionClass(DataExported::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        $params = $constructor->getParameters();
        $this->assertCount(4, $params);
        $this->assertEquals('exportId', $params[0]->getName());
        $this->assertEquals('userId', $params[1]->getName());
        $this->assertEquals('format', $params[2]->getName());
        $this->assertEquals('filePath', $params[3]->getName());
    }
    #[Test]
    public function it_has_readonly_properties(): void
    {
        $reflection = new ReflectionClass(DataExported::class);

        $this->assertTrue($reflection->getProperty('exportId')->isReadOnly());
        $this->assertTrue($reflection->getProperty('userId')->isReadOnly());
        $this->assertTrue($reflection->getProperty('format')->isReadOnly());
        $this->assertTrue($reflection->getProperty('filePath')->isReadOnly());
    }
    #[Test]
    public function it_uses_dispatchable_trait(): void
    {
        $reflection = new ReflectionClass(DataExported::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }
}
