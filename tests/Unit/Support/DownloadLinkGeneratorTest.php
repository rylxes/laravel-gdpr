<?php

namespace Rylxes\Gdpr\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Rylxes\Gdpr\Support\DownloadLinkGenerator;
use PHPUnit\Framework\Attributes\Test;

class DownloadLinkGeneratorTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(DownloadLinkGenerator::class);
    }
    #[Test]
    public function it_has_generate_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('generate'));

        $method = $this->reflection->getMethod('generate');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }
    #[Test]
    public function it_has_generate_token_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('generateToken'));

        $method = $this->reflection->getMethod('generateToken');
        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }
    #[Test]
    public function it_has_verify_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('verify'));

        $method = $this->reflection->getMethod('verify');
        $this->assertTrue($method->isPublic());
    }
    #[Test]
    public function generate_token_produces_64_char_hex_string(): void
    {
        $generator = new DownloadLinkGenerator();
        $token = $generator->generateToken();

        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }
    #[Test]
    public function generate_token_produces_unique_tokens(): void
    {
        $generator = new DownloadLinkGenerator();
        $tokens = [];

        for ($i = 0; $i < 10; $i++) {
            $tokens[] = $generator->generateToken();
        }

        $this->assertCount(10, array_unique($tokens));
    }
}
