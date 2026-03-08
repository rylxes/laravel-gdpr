<?php

namespace Rylxes\Gdpr\Tests\Unit\Concerns;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Concerns\HandlesGdpr;
use PHPUnit\Framework\Attributes\Test;

class HandlesGdprTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(HandlesGdpr::class);
    }
    #[Test]
    public function it_is_a_trait(): void
    {
        $this->assertTrue($this->reflection->isTrait());
    }
    #[Test]
    public function it_has_export_label_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('exportLabel'));

        $method = $this->reflection->getMethod('exportLabel');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }
    #[Test]
    public function it_has_erasure_strategy_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('erasureStrategy'));

        $method = $this->reflection->getMethod('erasureStrategy');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }
    #[Test]
    public function it_has_erasure_priority_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('erasurePriority'));

        $method = $this->reflection->getMethod('erasurePriority');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }
    #[Test]
    public function it_has_anonymise_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('anonymise'));

        $method = $this->reflection->getMethod('anonymise');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }
    #[Test]
    public function it_has_record_consent_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('recordConsent'));

        $method = $this->reflection->getMethod('recordConsent');
        $this->assertTrue($method->isPublic());
    }
    #[Test]
    public function it_has_has_consent_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('hasConsent'));

        $method = $this->reflection->getMethod('hasConsent');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }
    #[Test]
    public function it_has_revoke_consent_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('revokeConsent'));

        $method = $this->reflection->getMethod('revokeConsent');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }
    #[Test]
    public function it_has_consent_logs_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('consentLogs'));

        $method = $this->reflection->getMethod('consentLogs');
        $this->assertTrue($method->isPublic());
    }
    #[Test]
    public function it_has_active_consent_types_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('activeConsentTypes'));

        $method = $this->reflection->getMethod('activeConsentTypes');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
}
