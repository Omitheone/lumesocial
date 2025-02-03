<?php

namespace Inovector\Mixpost\Services\AI;

abstract class BaseContentGenerator
{
    public function generate(string $content, array $options = []): array
    {
        return $this->createContent($content, $options);
    }

    public function generateFromWebsite(string $url, array $options = []): array
    {
        return $this->createFromWebsite($url, $options);
    }

    abstract public function createContent(string $content, array $options = []): array;
    abstract public function createFromWebsite(string $url, array $options = []): array;
} 