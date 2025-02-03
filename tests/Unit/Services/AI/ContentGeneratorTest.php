<?php

namespace Tests\Unit\Services\AI;

use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\MockInterface;
use App\Models\AiSettings;
use App\Services\AI\ContentGenerator;
use OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use OpenAI\Responses\Chat\CreateResponseChoice;
use OpenAI\Responses\Chat\CreateResponseUsage;
use OpenAI\Responses\Meta\MetaInformation;

class ContentGeneratorTest extends TestCase
{
    protected $generator;
    protected $settings;
    protected $mock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settings = Mockery::mock(AiSettings::class);
        $this->generator = Mockery::mock(ContentGenerator::class);
        
        $this->mock = \Mockery::mock(ContentGenerator::class)->makePartial();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createMockResponse(string $content): array
    {
        return [
            'content' => $content,
            'metadata' => [
                'model' => 'gpt-3.5-turbo',
                'tokens_used' => 30
            ]
        ];
    }

    public function test_can_create_content(): void
    {
        $expectedContent = "This is a test response";
        $mockResponse = $this->createMockResponse($expectedContent);

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->with("Test prompt", [])
            ->andReturn($mockResponse);

        $result = $this->generator->generate("Test prompt");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals($expectedContent, $result['content']);
    }

    public function test_can_create_variations(): void
    {
        $variations = ["Variation 1", "Variation 2", "Variation 3"];
        $mockResponse = [
            'variations' => $variations,
            'metadata' => [
                'model' => 'gpt-3.5-turbo',
                'tokens_used' => 30
            ]
        ];

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->with("Test content", 3)
            ->andReturn($mockResponse);

        $result = $this->generator->generate("Test content", 3);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('variations', $result);
        $this->assertCount(3, $result['variations']);
    }

    public function test_can_improve_content(): void
    {
        $improvedContent = "This is improved content";
        $mockResponse = $this->createMockResponse($improvedContent);

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->with("Test content", [])
            ->andReturn($mockResponse);

        $result = $this->generator->generate("Test content");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals($improvedContent, $result['content']);
    }

    public function test_handles_empty_response(): void
    {
        $mockResponse = $this->createMockResponse("");

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->with("Test prompt", [])
            ->andReturn($mockResponse);

        $result = $this->generator->generate("Test prompt");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEmpty($result['content']);
    }

    public function test_includes_metadata(): void
    {
        $mockResponse = $this->createMockResponse("Test content");

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->with("Test prompt", [])
            ->andReturn($mockResponse);

        $result = $this->generator->generate("Test prompt");

        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('model', $result['metadata']);
        $this->assertArrayHasKey('tokens_used', $result['metadata']);
    }

    /** @test */
    public function it_generates_content()
    {
        $prompt = 'Generate test content';
        $expected = [
            'content' => 'Generated content',
            'tokens' => 10
        ];

        $this->mock->shouldReceive('generate')
            ->once()
            ->with($prompt)
            ->andReturn($expected);

        $result = $this->mock->generate($prompt);
        
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_handles_empty_response()
    {
        $prompt = 'Generate test content';
        
        $this->mock->shouldReceive('generate')
            ->once()
            ->with($prompt)
            ->andReturn(null);

        $result = $this->mock->generate($prompt);
        
        $this->assertNull($result);
    }
} 