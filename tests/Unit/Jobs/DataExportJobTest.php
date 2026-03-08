<?php

namespace Rylxes\Gdpr\Tests\Unit\Jobs;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Jobs\DataExportJob;
use PHPUnit\Framework\Attributes\Test;

class DataExportJobTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(DataExportJob::class);
    }
    #[Test]
    public function it_implements_should_queue(): void
    {
        $this->assertTrue(
            $this->reflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class)
        );
    }
    #[Test]
    public function it_uses_required_traits(): void
    {
        $traits = $this->reflection->getTraitNames();

        $this->assertContains('Illuminate\Foundation\Bus\Dispatchable', $traits);
        $this->assertContains('Illuminate\Queue\InteractsWithQueue', $traits);
        $this->assertContains('Illuminate\Bus\Queueable', $traits);
        $this->assertContains('Illuminate\Queue\SerializesModels', $traits);
    }
    #[Test]
    public function it_has_tries_property(): void
    {
        $this->assertTrue($this->reflection->hasProperty('tries'));

        $instance = $this->reflection->newInstanceWithoutConstructor();
        $tries = $this->reflection->getProperty('tries');
        $tries->setAccessible(true);

        $this->assertEquals(3, $tries->getValue($instance));
    }
    #[Test]
    public function it_has_backoff_property(): void
    {
        $this->assertTrue($this->reflection->hasProperty('backoff'));

        $instance = $this->reflection->newInstanceWithoutConstructor();
        $backoff = $this->reflection->getProperty('backoff');
        $backoff->setAccessible(true);

        $this->assertEquals(60, $backoff->getValue($instance));
    }
    #[Test]
    public function it_has_handle_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('handle'));

        $method = $this->reflection->getMethod('handle');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }
    #[Test]
    public function it_accepts_data_export_id_in_constructor(): void
    {
        $constructor = $this->reflection->getConstructor();
        $params = $constructor->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('dataExportId', $params[0]->getName());
    }
}
