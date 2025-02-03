<?php

namespace LumeSocial\Services\AI;

use OpenAI\Client;
use LumeSocial\Models\AiSettings;
use LumeSocial\Models\Media;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageSelector
{
    public function __construct(
        protected Client $client,
        protected AiSettings $settings
    ) {}

    public function selectImages(string $content, int $count = 3): array
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
        $suggestions = $this->parseResponse($responseData['choices'][0]['message']['content']);

        return array_slice($suggestions, 0, $count);
    }

    protected function getSystemPrompt(): string
    {
        return 'You are an AI image selection assistant. Your task is to analyze the given content ' .
               'and suggest relevant image descriptions or keywords that would complement the content well. ' .
               'Please provide specific, detailed image suggestions that capture the essence of the content. ' .
               'Format your response as a list of image descriptions, one per line.';
    }

    protected function formatPrompt(string $content): string
    {
        $imageCount = $this->settings->image_count ?? 3;
        
        return "Please analyze the following content and suggest relevant images that would complement it well:\n\n" .
               $content . "\n\n" .
               "Provide {$imageCount} specific image descriptions, focusing on:\n" .
               "1. Relevance to the content\n" .
               "2. Visual appeal\n" .
               "3. Professional appearance\n" .
               "4. Emotional connection";
    }

    protected function parseResponse(string $response): array
    {
        $lines = explode("\n", trim($response));
        return array_values(array_filter(array_map('trim', $lines)));
    }

    protected function saveImage(string $url): Media
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'ai_image_');
        file_put_contents($tempPath, file_get_contents($url));

        $image = Image::make($tempPath);
        $filename = 'ai-generated/' . uniqid() . '.jpg';
        
        Storage::disk('public')->put(
            $filename,
            $image->encode('jpg', 90)
        );

        unlink($tempPath);

        return Media::create([
            'type' => 'image',
            'path' => $filename,
            'metadata' => [
                'width' => $image->width(),
                'height' => $image->height(),
                'mime_type' => 'image/jpeg',
            ],
        ]);
    }
}