<?php

namespace Rylxes\Gdpr\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\GdprManager;
use PHPUnit\Framework\Attributes\Test;

class GdprManagerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(GdprManager::class);
    }
    #[Test]
    public function it_has_export_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('export'));

        $method = $this->reflection->getMethod('export');
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
        $this->assertEquals('user', $method->getParameters()[0]->getName());
        $this->assertEquals('format', $method->getParameters()[1]->getName());
    }
    #[Test]
    public function it_has_erase_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('erase'));

        $method = $this->reflection->getMethod('erase');
        $this->assertTrue($method->isPublic());
        $this->assertEquals('user', $method->getParameters()[0]->getName());
    }
    #[Test]
    public function it_has_record_consent_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('recordConsent'));

        $method = $this->reflection->getMethod('recordConsent');
        $this->assertTrue($method->isPublic());
        $this->assertEquals('user', $method->getParameters()[0]->getName());
        $this->assertEquals('type', $method->getParameters()[1]->getName());
    }
    #[Test]
    public function it_has_revoke_consent_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('revokeConsent'));

        $method = $this->reflection->getMethod('revokeConsent');
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
    public function it_has_discover_exportables_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('discoverExportables'));

        $method = $this->reflection->getMethod('discoverExportables');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
    #[Test]
    public function it_has_discover_deletables_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('discoverDeletables'));

        $method = $this->reflection->getMethod('discoverDeletables');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
    #[Test]
    public function it_has_dependency_resolver_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('dependencyResolver'));
        $this->assertTrue($this->reflection->getMethod('dependencyResolver')->isPublic());
    }
    #[Test]
    public function it_has_packager_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('packager'));
        $this->assertTrue($this->reflection->getMethod('packager')->isPublic());
    }
    #[Test]
    public function it_has_download_links_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('downloadLinks'));
        $this->assertTrue($this->reflection->getMethod('downloadLinks')->isPublic());
    }
    #[Test]
    public function it_accepts_application_in_constructor(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);

        $params = $constructor->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('app', $params[0]->getName());
    }
}
