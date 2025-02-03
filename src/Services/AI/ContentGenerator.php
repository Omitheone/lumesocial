<?php

namespace LumeSocial\Services\AI;

use OpenAI\Client;
use LumeSocial\Models\AiSettings;

class ContentGenerator
{
    public function __construct(
        protected Client $client,
        protected AiSettings $settings
    ) {}

    public function generate(string $prompt, array $options = []): array
    {
        $response = $this->client->chat()->create([
            'model' => $this->settings->model ?? 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $this->getSystemPrompt()],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 500,
        ]);

        $responseData = json_decode(json_encode($response), true);

        return [
            'content' => $responseData['choices'][0]['message']['content'],
            'tokens_used' => $responseData['usage']['total_tokens'],
            'model' => $responseData['model'],
            'finish_reason' => $responseData['choices'][0]['finish_reason'],
        ];
    }

    protected function getSystemPrompt(): string
    {
        return <<<EOT
You are a professional content writer. Your task is to generate engaging, 
well-written content that is appropriate for social media platforms. 
Keep the tone conversational but professional, and ensure the content is 
both informative and engaging.
EOT;
    }

    public function generateVariations(string $prompt, int $count = 3): array
    {
        $variations = [];
        
        for ($i = 0; $i < $count; $i++) {
            $response = $this->generate($prompt, [
                'temperature' => 0.8 + ($i * 0.1), // Increase variation with each iteration
            ]);
            
            $variations[] = $response['content'];
        }

        return $variations;
    }
} 