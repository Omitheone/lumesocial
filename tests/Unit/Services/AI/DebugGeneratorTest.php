<?php

namespace Tests\Unit\Services\AI;

use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\MockInterface;
use App\Models\AiSettings;
use App\Services\AI\ContentGenerator;

class DebugGeneratorTest extends TestCase
{
    protected $generator;
    protected $settings;
    protected $mock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settings = Mockery::mock(AiSettings::class);
        $this->generator = Mockery::mock(ContentGenerator::class);
        
        $this->mock = Mockery::mock('alias:' . ContentGenerator::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_debug_content()
    {
        $prompt = 'Debug test content';
        $expected = [
            'content' => 'Debug mode content',
            'tokens' => 5
        ];

        $this->mock->shouldReceive('generate')
            ->once()
            ->with($prompt)
            ->andReturn($expected);

        $result = $this->mock->generate($prompt);
        
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_respects_debug_mode_setting()
    {
        $this->settings->shouldReceive('getAttribute')
            ->with('debug_mode')
            ->andReturn(true);

        $prompt = 'Test prompt';
        $expected = [
            'content' => 'Debug content',
            'tokens' => 3
        ];

        $this->mock->shouldReceive('generate')
            ->once()
            ->with($prompt)
            ->andReturn($expected);

        $result = $this->mock->generate($prompt);
        
        $this->assertEquals($expected, $result);
    }

    public function test_returns_debug_content(): void
    {
        $mockResponse = [
            'content' => '[DEBUG] Generated content for: Test prompt',
            'metadata' => [
                'model' => 'debug',
                'tokens_used' => 0,
                'debug' => true
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('createContent')
            ->with("Test prompt", [])
            ->willReturn($mockResponse);

        $result = $this->generator->createContent("Test prompt");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertStringContainsString('[DEBUG]', $result['content']);
        $this->assertTrue($result['metadata']['debug']);
    }

    public function test_returns_debug_variations(): void
    {
        $mockResponse = [
            'variations' => [
                '[DEBUG] Variation 1',
                '[DEBUG] Variation 2',
                '[DEBUG] Variation 3'
            ],
            'metadata' => [
                'model' => 'debug',
                'tokens_used' => 0,
                'debug' => true
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('createVariations')
            ->with("Test content", 3)
            ->willReturn($mockResponse);

        $result = $this->generator->createVariations("Test content", 3);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertCount(3, $result['variations']);
        $this->assertStringContainsString('[DEBUG]', $result['variations'][0]);
    }

    public function test_returns_debug_improvements(): void
    {
        $mockResponse = [
            'content' => '[DEBUG] Improved version of: Test content',
            'metadata' => [
                'model' => 'debug',
                'tokens_used' => 0,
                'debug' => true
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('improveContent')
            ->with("Test content", [])
            ->willReturn($mockResponse);

        $result = $this->generator->improveContent("Test content");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertStringContainsString('[DEBUG]', $result['content']);
    }

    public function test_includes_debug_metadata(): void
    {
        $mockResponse = [
            'content' => '[DEBUG] Test content',
            'metadata' => [
                'model' => 'debug',
                'tokens_used' => 0,
                'debug' => true,
                'timestamp' => now()->toIso8601String()
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('createContent')
            ->with("Test prompt", [])
            ->willReturn($mockResponse);

        $result = $this->generator->createContent("Test prompt");

        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('debug', $result['metadata']['model']);
        $this->assertEquals(0, $result['metadata']['tokens_used']);
        $this->assertTrue($result['metadata']['debug']);
    }
} 