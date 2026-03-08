<?php

namespace Rylxes\Gdpr\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Models\DataExport;
use PHPUnit\Framework\Attributes\Test;

class DataExportTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(DataExport::class);
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
        $this->assertArrayHasKey('file_size_bytes', $castValues);
        $this->assertArrayHasKey('downloaded_at', $castValues);
        $this->assertArrayHasKey('expires_at', $castValues);
        $this->assertArrayHasKey('completed_at', $castValues);
        $this->assertArrayHasKey('metadata', $castValues);
    }
    #[Test]
    public function it_defines_valid_statuses(): void
    {
        $this->assertIsArray(DataExport::STATUSES);
        $this->assertContains('pending', DataExport::STATUSES);
        $this->assertContains('processing', DataExport::STATUSES);
        $this->assertContains('completed', DataExport::STATUSES);
        $this->assertContains('failed', DataExport::STATUSES);
    }
    #[Test]
    public function it_defines_valid_formats(): void
    {
        $this->assertIsArray(DataExport::FORMATS);
        $this->assertContains('json', DataExport::FORMATS);
        $this->assertContains('csv', DataExport::FORMATS);
        $this->assertContains('xml', DataExport::FORMATS);
    }
    #[Test]
    public function it_has_user_relationship(): void
    {
        $this->assertTrue($this->reflection->hasMethod('user'));
    }
    #[Test]
    public function it_has_required_scopes(): void
    {
        $this->assertTrue($this->reflection->hasMethod('scopeExpired'));
        $this->assertTrue($this->reflection->hasMethod('scopeCompleted'));
        $this->assertTrue($this->reflection->hasMethod('scopeFailed'));
        $this->assertTrue($this->reflection->hasMethod('scopeReadyForCleanup'));
    }
    #[Test]
    public function it_has_is_expired_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isExpired'));

        $returnType = $this->reflection->getMethod('isExpired')->getReturnType();
        $this->assertEquals('bool', $returnType->getName());
    }
    #[Test]
    public function it_has_is_downloaded_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isDownloaded'));

        $returnType = $this->reflection->getMethod('isDownloaded')->getReturnType();
        $this->assertEquals('bool', $returnType->getName());
    }
    #[Test]
    public function it_has_mark_completed_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('markCompleted'));
    }
    #[Test]
    public function it_has_mark_failed_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('markFailed'));
    }
    #[Test]
    public function it_has_download_url_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('downloadUrl'));
    }
    #[Test]
    public function it_has_file_size_for_humans_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('fileSizeForHumans'));
    }
}
