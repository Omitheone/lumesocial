<?php

namespace Inovector\Mixpost\Traits;

trait GeneratesContent
{
    public function generate(string $content, array $options = []): array
    {
        if (method_exists($this, 'createContent')) {
            return $this->createContent($content, $options);
        }
        
        throw new \BadMethodCallException('Method createContent() must be implemented.');
    }

    public function generateFromWebsite(string $url, array $options = []): array
    {
        if (method_exists($this, 'createFromWebsite')) {
            return $this->createFromWebsite($url, $options);
        }
        
        throw new \BadMethodCallException('Method createFromWebsite() must be implemented.');
    }
} 