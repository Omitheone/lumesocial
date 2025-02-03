<?php

namespace LumeSocial\Console\Commands;

use Illuminate\Console\Command;
use LumeSocial\Services\AI\ContentGenerator;
use LumeSocial\Models\AiSettings;

class TestAiConnection extends Command
{
    protected $signature = 'ai:test';
    protected $description = 'Test AI service connection';

    public function handle(ContentGenerator $generator, AiSettings $settings): int
    {
        $this->info('Testing AI connection...');
        
        try {
            $result = $generator->generate('Test connection');
            if ($result) {
                $this->info('Connection successful!');
                return Command::SUCCESS;
            }
        } catch (\Exception $e) {
            $this->error("Connection failed: {$e->getMessage()}");
            return Command::FAILURE;
        }

        $this->error('Connection failed with no error message');
        return Command::FAILURE;
    }
} 