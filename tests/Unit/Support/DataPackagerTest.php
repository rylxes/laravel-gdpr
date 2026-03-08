<?php

namespace Rylxes\Gdpr\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Support\DataPackager;
use PHPUnit\Framework\Attributes\Test;

class DataPackagerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(DataPackager::class);
    }
    #[Test]
    public function it_has_package_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('package'));

        $method = $this->reflection->getMethod('package');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
    #[Test]
    public function it_has_store_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('store'));

        $method = $this->reflection->getMethod('store');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }
    #[Test]
    public function it_has_protected_format_methods(): void
    {
        $this->assertTrue($this->reflection->hasMethod('toJson'));
        $this->assertTrue($this->reflection->getMethod('toJson')->isProtected());

        $this->assertTrue($this->reflection->hasMethod('toCsv'));
        $this->assertTrue($this->reflection->getMethod('toCsv')->isProtected());

        $this->assertTrue($this->reflection->hasMethod('toXml'));
        $this->assertTrue($this->reflection->getMethod('toXml')->isProtected());
    }
    #[Test]
    public function it_has_protected_flatten_row_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('flattenRow'));
        $this->assertTrue($this->reflection->getMethod('flattenRow')->isProtected());
    }
    #[Test]
    public function package_method_accepts_sections_and_format(): void
    {
        $method = $this->reflection->getMethod('package');
        $params = $method->getParameters();

        $this->assertCount(2, $params);
        $this->assertEquals('sections', $params[0]->getName());
        $this->assertEquals('format', $params[1]->getName());
    }
    #[Test]
    public function to_json_produces_valid_json(): void
    {
        $packager = new DataPackager();

        $method = $this->reflection->getMethod('toJson');
        $method->setAccessible(true);

        $sections = [
            'Profile' => ['name' => 'Test User', 'email' => 'test@example.com'],
        ];

        $result = $method->invoke($packager, $sections);
        $decoded = json_decode($result, true);

        $this->assertNotNull($decoded);
        $this->assertArrayHasKey('exported_at', $decoded);
        $this->assertArrayHasKey('sections', $decoded);
        $this->assertArrayHasKey('Profile', $decoded['sections']);
    }
    #[Test]
    public function to_csv_produces_valid_csv(): void
    {
        $packager = new DataPackager();

        $method = $this->reflection->getMethod('toCsv');
        $method->setAccessible(true);

        $sections = [
            'Orders' => [
                ['id' => 1, 'total' => 100],
                ['id' => 2, 'total' => 200],
            ],
        ];

        $result = $method->invoke($packager, $sections);

        $this->assertStringContainsString('Orders', $result);
        $this->assertStringContainsString('id', $result);
        $this->assertStringContainsString('total', $result);
    }
    #[Test]
    public function to_xml_produces_valid_xml(): void
    {
        $packager = new DataPackager();

        $method = $this->reflection->getMethod('toXml');
        $method->setAccessible(true);

        $sections = [
            'Profile' => ['name' => 'Test User', 'email' => 'test@example.com'],
        ];

        $result = $method->invoke($packager, $sections);

        $this->assertStringContainsString('<?xml', $result);
        $this->assertStringContainsString('data_export', $result);
        $this->assertStringContainsString('Profile', $result);
    }
    #[Test]
    public function flatten_row_handles_nested_arrays(): void
    {
        $packager = new DataPackager();

        $method = $this->reflection->getMethod('flattenRow');
        $method->setAccessible(true);

        $row = [
            'name' => 'Test',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Test City',
            ],
        ];

        $result = $method->invoke($packager, $row);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('address.street', $result);
        $this->assertArrayHasKey('address.city', $result);
    }
}
