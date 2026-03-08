<?php

namespace Rylxes\Gdpr\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Support\DependencyResolver;
use PHPUnit\Framework\Attributes\Test;

class DependencyResolverTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(DependencyResolver::class);
    }
    #[Test]
    public function it_has_resolve_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('resolve'));

        $method = $this->reflection->getMethod('resolve');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
    #[Test]
    public function it_has_build_group_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('buildGroup'));

        $method = $this->reflection->getMethod('buildGroup');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
    #[Test]
    public function it_has_resolve_strategy_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('resolveStrategy'));

        $method = $this->reflection->getMethod('resolveStrategy');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }
    #[Test]
    public function it_has_protected_get_priority_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getPriority'));

        $method = $this->reflection->getMethod('getPriority');
        $this->assertTrue($method->isProtected());
    }
}
