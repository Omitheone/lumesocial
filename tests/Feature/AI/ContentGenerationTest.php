<?php

namespace Tests\Feature\AI;

use Tests\TestCase;
use OpenAI\Client;
use OpenAI\Factory;
use OpenAI\Resources\Chat;
use LumeSocial\Models\AiSettings;
use LumeSocial\Services\AI\ContentGenerator;
use Mockery;
use Mockery\MockInterface;

class ContentGenerationTest extends TestCase
{
    protected Client|MockInterface $openAiClient;
    protected Chat|MockInterface $chatResource;
    protected AiSettings $aiSettings;
    protected ContentGenerator $contentGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock chat resource
        $this->chatResource = Mockery::mock(Chat::class);

        // Create mock OpenAI client
        $client = app(Client::class);
        $this->instance(Client::class, $client);
        $this->openAiClient = $client;
        $this->openAiClient->allows('chat')->andReturn($this->chatResource);

        // Create AI settings
        $this->aiSettings = new AiSettings([
            'model' => 'gpt-3.5-turbo',
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        // Initialize content generator
        $this->contentGenerator = new ContentGenerator(
            $this->openAiClient,
            $this->aiSettings
        );
    }

    public function test_it_can_generate_content()
    {
        // Mock the chat create response
        $this->chatResource->allows('create')
            ->andReturn((object)[
                'choices' => [(object)[
                    'message' => (object)[
                        'content' => 'Generated test content',
                    ],
                ]],
                'usage' => (object)[
                    'total_tokens' => 50,
                ],
            ]);

        $result = $this->contentGenerator->generate('Test prompt');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals('Generated test content', $result['content']);
    }

    public function test_it_can_generate_variations()
    {
        // Mock the chat create response for variations
        $this->chatResource->allows('create')
            ->andReturn((object)[
                'choices' => [(object)[
                    'message' => (object)[
                        'content' => 'Variation content',
                    ],
                ]],
                'usage' => (object)[
                    'total_tokens' => 50,
                ],
            ]);

        $variations = $this->contentGenerator->generateVariations('Test prompt', 3);

        $this->assertIsArray($variations);
        $this->assertCount(3, $variations);
        $this->assertEquals('Variation content', $variations[0]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 