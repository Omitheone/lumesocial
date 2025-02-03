<?php

namespace Tests\Unit\Jobs;

use PHPUnit\Framework\TestCase;
use Mockery;
use App\Models\AiSettings;
use App\Services\AI\ContentReviewer;
use App\Jobs\ReviewAiContent;

class ReviewAiContentTest extends TestCase
{
    protected $reviewer;
    protected $settings;
    protected $job;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settings = Mockery::mock(AiSettings::class);
        $this->reviewer = Mockery::mock(ContentReviewer::class);
        
        $this->job = new ReviewAiContent($this->settings, 'Test content');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_reviews_content_successfully()
    {
        $expected = [
            'score' => 0.8,
            'feedback' => 'Good content'
        ];

        $this->reviewer->shouldReceive('review')
            ->once()
            ->with('Test content')
            ->andReturn($expected);

        $result = $this->job->handle($this->reviewer);
        
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_handles_review_failure()
    {
        $this->reviewer->shouldReceive('review')
            ->once()
            ->with('Test content')
            ->andReturn(null);

        $result = $this->job->handle($this->reviewer);
        
        $this->assertNull($result);
    }
} 