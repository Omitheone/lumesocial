<?php

namespace LumeSocial\Services\AI;

use OpenAI\Client;
use LumeSocial\Models\AiSettings;

class ContentReviewer
{
    public function __construct(
        protected Client $client,
        protected AiSettings $settings
    ) {}

    public function review(string $content): array
    {
        $response = $this->client->chat()->create([
            'model' => $this->settings->model ?? 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $this->getSystemPrompt()],
                ['role' => 'user', 'content' => $this->formatPrompt($content)],
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        $responseData = json_decode(json_encode($response), true);
        return $this->parseResponse($responseData['choices'][0]['message']['content']);
    }

    protected function getSystemPrompt(): string
    {
        return 'You are a content reviewer. Your task is to analyze the given content ' .
               'and provide feedback on its quality, tone, and potential improvements. ' .
               'Please be specific and constructive in your feedback.';
    }

    protected function formatPrompt(string $content): string
    {
        return "Please review the following content and provide feedback:\n\n" .
               $content . "\n\n" .
               "Please analyze:\n" .
               "1. Content quality\n" .
               "2. Tone and style\n" .
               "3. Engagement potential\n" .
               "4. Suggested improvements";
    }

    protected function parseResponse(string $response): array
    {
        // Simple parsing - you might want to make this more sophisticated
        return [
            'feedback' => $response,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function checkTone(string $content): array
    {
        $response = $this->client->chat()->create([
            'model' => $this->settings->model ?? 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Analyze the tone of this social media content. Consider factors like formality, emotion, and brand voice.'
                ],
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ]
        ]);

        return [
            'analysis' => $response->choices[0]->message->content ?? '',
            'metadata' => [
                'model' => $response->model,
                'tokens_used' => $response->usage->total_tokens ?? 0
            ]
        ];
    }

    public function suggestImprovements(string $content): array
    {
        $response = $this->client->chat()->create([
            'model' => $this->settings->model ?? 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Suggest specific improvements for this social media content to increase engagement and effectiveness.'
                ],
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ]
        ]);

        return [
            'suggestions' => $response->choices[0]->message->content ?? '',
            'metadata' => [
                'model' => $response->model,
                'tokens_used' => $response->usage->total_tokens ?? 0
            ]
        ];
    }
} 