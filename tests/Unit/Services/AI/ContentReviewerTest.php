<?php

namespace Tests\Unit\Services\AI;

use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\MockInterface;
use App\Models\AiSettings;
use App\Services\AI\ContentReviewer;

class ContentReviewerTest extends TestCase
{
    protected $reviewer;
    protected $settings;
    protected $mock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settings = Mockery::mock(AiSettings::class);
        $this->reviewer = Mockery::mock(ContentReviewer::class);
        
        $this->mock = Mockery::mock('alias:' . ContentReviewer::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_reviews_content()
    {
        $content = 'Test content';
        $expected = [
            'score' => 0.8,
            'feedback' => 'Good content'
        ];

        $this->mock->shouldReceive('review')
            ->once()
            ->with($content)
            ->andReturn($expected);

        $result = $this->mock->review($content);
        
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_handles_invalid_content()
    {
        $content = '';
        
        $this->mock->shouldReceive('review')
            ->once()
            ->with($content)
            ->andReturn(null);

        $result = $this->mock->review($content);
        
        $this->assertNull($result);
    }

    public function test_can_review_content(): void
    {
        $expectedReview = "This content is well-structured and engaging.";
        $mockResponse = $this->createMockResponse($expectedReview);

        $this->reviewer
            ->expects($this->once())
            ->method('reviewContent')
            ->with("Test content")
            ->willReturn($mockResponse);

        $result = $this->reviewer->reviewContent("Test content");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('review', $result);
        $this->assertEquals($expectedReview, $result['review']);
    }

    public function test_can_check_tone(): void
    {
        $expectedAnalysis = "The tone is professional and friendly.";
        $mockResponse = [
            'analysis' => $expectedAnalysis,
            'metadata' => [
                'model' => 'gpt-3.5-turbo',
                'tokens_used' => 30
            ]
        ];

        $this->reviewer
            ->expects($this->once())
            ->method('checkTone')
            ->with("Test content")
            ->willReturn($mockResponse);

        $result = $this->reviewer->checkTone("Test content");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('analysis', $result);
        $this->assertEquals($expectedAnalysis, $result['analysis']);
    }

    public function test_can_suggest_improvements(): void
    {
        $expectedSuggestions = "Consider adding more engaging hashtags.";
        $mockResponse = [
            'suggestions' => $expectedSuggestions,
            'metadata' => [
                'model' => 'gpt-3.5-turbo',
                'tokens_used' => 30
            ]
        ];

        $this->reviewer
            ->expects($this->once())
            ->method('suggestImprovements')
            ->with("Test content")
            ->willReturn($mockResponse);

        $result = $this->reviewer->suggestImprovements("Test content");

        $this->assertIsArray($result);
        $this->assertArrayHasKey('suggestions', $result);
        $this->assertEquals($expectedSuggestions, $result['suggestions']);
    }

    public function test_includes_metadata(): void
    {
        $mockResponse = $this->createMockResponse("Test review");

        $this->reviewer
            ->expects($this->once())
            ->method('reviewContent')
            ->with("Test content")
            ->willReturn($mockResponse);

        $result = $this->reviewer->reviewContent("Test content");

        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('model', $result['metadata']);
        $this->assertArrayHasKey('tokens_used', $result['metadata']);
    }

    protected function createMockResponse(string $content): array
    {
        return [
            'review' => $content,
            'metadata' => [
                'model' => 'gpt-3.5-turbo',
                'tokens_used' => 30
            ]
        ];
    }
} 