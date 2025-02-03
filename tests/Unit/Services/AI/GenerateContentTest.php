<?php

namespace Tests\Unit\Services\AI;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Organization;
use App\Models\AiSettings;

class GenerateContentTest extends TestCase
{
    protected $organization;
    protected $user;
    protected $settings;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->settings = AiSettings::factory()->create([
            'organization_id' => $this->organization->id
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
} 