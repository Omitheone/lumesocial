<?php

namespace Tests\Unit\Services\Subscription;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Organization;
use App\Services\Subscription\SubscriptionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class SubscriptionManagerTest extends TestCase
{
    use RefreshDatabase;

    protected $manager;
    protected $organization;
    protected $user;
    protected $basicPlan;
    protected $proPlan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->basicPlan = SubscriptionPlan::factory()->create(['name' => 'Basic']);
        $this->proPlan = SubscriptionPlan::factory()->create(['name' => 'Pro']);
        $this->organization = Organization::factory()->create();
        $this->manager = new SubscriptionManager($this->organization);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function test_can_subscribe_to_plan(): void
    {
        $result = $this->manager->subscribe($this->organization, $this->basicPlan);

        $this->assertTrue($result);
        $this->organization->shouldReceive('refresh');
        
        $this->assertEquals($this->basicPlan->id, $this->organization->subscription_plan_id);
        $this->assertEquals('active', $this->organization->subscription_status);
        $this->assertNotNull($this->organization->subscription_starts_at);
    }

    public function test_can_cancel_subscription(): void
    {
        $this->manager->subscribe($this->organization, $this->basicPlan);
        $result = $this->manager->cancel($this->organization);

        $this->assertTrue($result);
        $this->organization->shouldReceive('refresh');
        
        $this->assertEquals('cancelled', $this->organization->subscription_status);
        $this->assertNotNull($this->organization->subscription_ends_at);
    }

    public function test_can_upgrade_plan(): void
    {
        $this->manager->subscribe($this->organization, $this->basicPlan);
        $result = $this->manager->upgrade($this->organization, $this->proPlan);

        $this->assertTrue($result);
        $this->organization->shouldReceive('refresh');
        
        $this->assertEquals($this->proPlan->id, $this->organization->subscription_plan_id);
        $this->assertArrayHasKey('upgraded_from', $this->organization->subscription_data);
    }

    public function test_cannot_upgrade_to_lower_plan(): void
    {
        $this->manager->subscribe($this->organization, $this->proPlan);
        $result = $this->manager->upgrade($this->organization, $this->basicPlan);

        $this->assertFalse($result);
        $this->organization->shouldReceive('refresh');
        
        $this->assertEquals($this->proPlan->id, $this->organization->subscription_plan_id);
    }

    public function test_can_check_subscription_status(): void
    {
        $this->assertFalse($this->manager->isSubscribed($this->organization));

        $this->manager->subscribe($this->organization, $this->basicPlan);
        $this->assertTrue($this->manager->isSubscribed($this->organization));

        $this->manager->cancel($this->organization);
        $this->assertFalse($this->manager->isSubscribed($this->organization));
    }

    public function test_can_check_feature_access(): void
    {
        $this->manager->subscribe($this->organization, $this->basicPlan);

        $this->assertTrue($this->manager->hasFeature($this->organization, 'feature_1'));
        $this->assertFalse($this->manager->hasFeature($this->organization, 'feature_3'));
    }

    public function test_handles_expired_subscription(): void
    {
        $this->manager->subscribe($this->organization, $this->basicPlan, [
            'end_date' => Carbon::yesterday()
        ]);

        $this->assertFalse($this->manager->isSubscribed($this->organization));
    }
} 