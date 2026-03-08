<?php

namespace Rylxes\Gdpr\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Models\ConsentLog;
use PHPUnit\Framework\Attributes\Test;

class ConsentLogTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ConsentLog::class);
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
    public function it_has_casts_property(): void
    {
        $this->assertTrue($this->reflection->hasProperty('casts'));

        $instance = $this->reflection->newInstanceWithoutConstructor();
        $casts = $this->reflection->getProperty('casts');
        $casts->setAccessible(true);

        $castValues = $casts->getValue($instance);
        $this->assertArrayHasKey('given_at', $castValues);
        $this->assertArrayHasKey('revoked_at', $castValues);
        $this->assertArrayHasKey('metadata', $castValues);
        $this->assertEquals('datetime', $castValues['given_at']);
        $this->assertEquals('datetime', $castValues['revoked_at']);
        $this->assertEquals('array', $castValues['metadata']);
    }
    #[Test]
    public function it_has_user_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('user'));
        $this->assertTrue($this->reflection->getMethod('user')->isPublic());
    }
    #[Test]
    public function it_has_scope_active(): void
    {
        $this->assertTrue($this->reflection->hasMethod('scopeActive'));
    }
    #[Test]
    public function it_has_scope_of_type(): void
    {
        $this->assertTrue($this->reflection->hasMethod('scopeOfType'));
    }
    #[Test]
    public function it_has_scope_for_user(): void
    {
        $this->assertTrue($this->reflection->hasMethod('scopeForUser'));
    }
    #[Test]
    public function it_has_scope_of_version(): void
    {
        $this->assertTrue($this->reflection->hasMethod('scopeOfVersion'));
    }
    #[Test]
    public function it_has_is_active_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isActive'));

        $method = $this->reflection->getMethod('isActive');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }
    #[Test]
    public function it_has_revoke_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('revoke'));

        $method = $this->reflection->getMethod('revoke');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }
    #[Test]
    public function it_has_configurable_table_name(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getTable'));
    }
    #[Test]
    public function it_has_configurable_connection(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getConnectionName'));
    }
}
