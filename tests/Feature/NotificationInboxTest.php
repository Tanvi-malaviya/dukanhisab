<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Admin > Broadcast Center dispatches App\Notifications\AdminBroadcastNotification on the
 * `database` channel, but until now nothing ever read those rows back — a shop owner had no
 * way to see a broadcast the admin sent them. These lock in the read API: own-notifications-only
 * scoping, unread counts, mark-read/mark-all-read and delete.
 */
class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_user_sees_only_their_own_notifications(): void
    {
        $this->user->notify(new AdminBroadcastNotification('Hello', 'Mine', 'promo'));
        $this->otherUser->notify(new AdminBroadcastNotification('Hello', 'Not mine', 'promo'));

        $r = $this->getJson('/api/v1/shopowner/notifications')->assertStatus(200);
        $items = $r->json('notifications');

        $this->assertCount(1, $items);
        $this->assertEquals('Mine', $items[0]['message']);
    }

    public function test_notification_payload_matches_what_was_broadcast(): void
    {
        $this->user->notify(new AdminBroadcastNotification('New Feature', 'Check it out', 'feature'));

        $items = $this->getJson('/api/v1/shopowner/notifications')->json('notifications');

        $this->assertEquals('New Feature', $items[0]['title']);
        $this->assertEquals('Check it out', $items[0]['message']);
        $this->assertEquals('feature', $items[0]['type']);
        $this->assertFalse($items[0]['read']);
    }

    public function test_unread_count_reflects_only_unread_notifications(): void
    {
        $this->user->notify(new AdminBroadcastNotification('A', 'A', 'promo'));
        $this->user->notify(new AdminBroadcastNotification('B', 'B', 'promo'));

        $this->getJson('/api/v1/shopowner/notifications/unread-count')
            ->assertStatus(200)
            ->assertJson(['unread_count' => 2]);

        $id = $this->user->notifications()->first()->id;
        $this->postJson("/api/v1/shopowner/notifications/{$id}/read")->assertStatus(200);

        $this->getJson('/api/v1/shopowner/notifications/unread-count')
            ->assertJson(['unread_count' => 1]);
    }

    public function test_mark_read_only_affects_own_notification_not_someone_elses(): void
    {
        $this->otherUser->notify(new AdminBroadcastNotification('X', 'X', 'promo'));
        $otherId = $this->otherUser->notifications()->first()->id;

        $this->postJson("/api/v1/shopowner/notifications/{$otherId}/read")->assertStatus(404);
        $this->assertNull($this->otherUser->notifications()->first()->read_at);
    }

    public function test_mark_all_read_clears_unread_count(): void
    {
        $this->user->notify(new AdminBroadcastNotification('A', 'A', 'promo'));
        $this->user->notify(new AdminBroadcastNotification('B', 'B', 'promo'));

        $this->postJson('/api/v1/shopowner/notifications/mark-all-read')->assertStatus(200);

        $this->getJson('/api/v1/shopowner/notifications/unread-count')
            ->assertJson(['unread_count' => 0]);
    }

    public function test_user_can_delete_their_own_notification_but_not_someone_elses(): void
    {
        $this->user->notify(new AdminBroadcastNotification('A', 'A', 'promo'));
        $id = $this->user->notifications()->first()->id;

        $this->otherUser->notify(new AdminBroadcastNotification('B', 'B', 'promo'));
        $otherId = $this->otherUser->notifications()->first()->id;

        $this->deleteJson("/api/v1/shopowner/notifications/{$otherId}")->assertStatus(404);
        $this->deleteJson("/api/v1/shopowner/notifications/{$id}")->assertStatus(200);

        $this->assertCount(0, $this->user->notifications()->get());
        $this->assertCount(1, $this->otherUser->notifications()->get());
    }

    public function test_mobile_v1_alias_route_returns_the_same_notifications(): void
    {
        $this->user->notify(new AdminBroadcastNotification('Mobile', 'Via v1', 'promo'));

        $items = $this->getJson('/api/v1/notifications')->assertStatus(200)->json('notifications');
        $this->assertCount(1, $items);
        $this->assertEquals('Via v1', $items[0]['message']);
    }
}
