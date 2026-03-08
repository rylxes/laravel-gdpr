<?php

namespace Rylxes\Gdpr\Tests\Unit\Events;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Events\ConsentRecorded;
use PHPUnit\Framework\Attributes\Test;

class ConsentRecordedTest extends TestCase
{
    #[Test]
    public function it_has_correct_constructor_parameters(): void
    {
        $reflection = new ReflectionClass(ConsentRecorded::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        $params = $constructor->getParameters();
        $this->assertCount(4, $params);
        $this->assertEquals('consentLogId', $params[0]->getName());
        $this->assertEquals('userId', $params[1]->getName());
        $this->assertEquals('consentType', $params[2]->getName());
        $this->assertEquals('consentVersion', $params[3]->getName());
    }
    #[Test]
    public function it_has_readonly_properties(): void
    {
        $reflection = new ReflectionClass(ConsentRecorded::class);

        $this->assertTrue($reflection->getProperty('consentLogId')->isReadOnly());
        $this->assertTrue($reflection->getProperty('userId')->isReadOnly());
        $this->assertTrue($reflection->getProperty('consentType')->isReadOnly());
        $this->assertTrue($reflection->getProperty('consentVersion')->isReadOnly());
    }
    #[Test]
    public function it_uses_dispatchable_trait(): void
    {
        $reflection = new ReflectionClass(ConsentRecorded::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains('Illuminate\Foundation\Events\Dispatchable', $traits);
    }
}
