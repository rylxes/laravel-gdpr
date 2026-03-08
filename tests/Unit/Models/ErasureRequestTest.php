<?php

namespace Rylxes\Gdpr\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Models\ErasureRequest;
use PHPUnit\Framework\Attributes\Test;

class ErasureRequestTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ErasureRequest::class);
    }
    #[Test]
    public function it_extends_eloquent_model(): void
    {
        $this->assertTrue(
            $this->reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)
        );
    }
    #[Test]
    public function it_has_guarded_property_set_to_empty_array(): void
    {
        $instance = $this->reflection->newInstanceWithoutConstructor();
        $guarded = $this->reflection->getProperty('guarded');
        $guarded->setAccessible(true);

        $this->assertEquals([], $guarded->getValue($instance));
    }
    #[Test]
    public function it_has_correct_casts(): void
    {
        $instance = $this->reflection->newInstanceWithoutConstructor();
        $casts = $this->reflection->getProperty('casts');
        $casts->setAccessible(true);

        $castValues = $casts->getValue($instance);
        $this->assertArrayHasKey('scheduled_at', $castValues);
        $this->assertArrayHasKey('processed_at', $castValues);
        $this->assertArrayHasKey('cancelled_at', $castValues);
        $this->assertArrayHasKey('metadata', $castValues);
    }
    #[Test]
    public function it_defines_valid_statuses(): void
    {
        $this->assertIsArray(ErasureRequest::STATUSES);
        $this->assertContains('pending', ErasureRequest::STATUSES);
        $this->assertContains('cooling_off', ErasureRequest::STATUSES);
        $this->assertContains('processing', ErasureRequest::STATUSES);
        $this->assertContains('completed', ErasureRequest::STATUSES);
        $this->assertContains('cancelled', ErasureRequest::STATUSES);
        $this->assertContains('failed', ErasureRequest::STATUSES);
    }
    #[Test]
    public function it_defines_valid_strategies(): void
    {
        $this->assertIsArray(ErasureRequest::STRATEGIES);
        $this->assertContains('anonymize', ErasureRequest::STRATEGIES);
        $this->assertContains('delete', ErasureRequest::STRATEGIES);
    }
    #[Test]
    public function it_has_user_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('user'));
    }
    #[Test]
    public function it_has_required_scopes(): void
    {
        $this->assertTrue($this->reflection->hasMethod('scopePending'));
        $this->assertTrue($this->reflection->hasMethod('scopeCoolingOff'));
        $this->assertTrue($this->reflection->hasMethod('scopeReadyToProcess'));
        $this->assertTrue($this->reflection->hasMethod('scopeCompleted'));
    }
    #[Test]
    public function it_has_is_in_cooling_off_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isInCoolingOff'));

        $returnType = $this->reflection->getMethod('isInCoolingOff')->getReturnType();
        $this->assertEquals('bool', $returnType->getName());
    }
    #[Test]
    public function it_has_is_cancellable_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isCancellable'));

        $returnType = $this->reflection->getMethod('isCancellable')->getReturnType();
        $this->assertEquals('bool', $returnType->getName());
    }
    #[Test]
    public function it_has_mark_processing_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('markProcessing'));
    }
    #[Test]
    public function it_has_mark_completed_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('markCompleted'));
    }
    #[Test]
    public function it_has_cancel_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('cancel'));
    }
}
