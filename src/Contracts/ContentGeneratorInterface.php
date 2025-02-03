<?php

namespace LumeSocial\Contracts;

interface ContentGeneratorInterface
{
    public function createContent(string $prompt, array $options = []): array;
    
    public function createFromWebsite(string $url, array $options = []): array;
    
    public function createFromText(string $text, array $options = []): array;
    
    public function createVariations(string $content, int $count = 3): array;
    
    public function improveContent(string $content, array $options = []): array;
} 